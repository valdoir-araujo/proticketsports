<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Atleta;
use App\Models\Estado;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Exibe o formulário de registo unificado.
     */
    public function create(): View
    {
        $estados = Estado::orderBy('nome')->get();
        return view('auth.register', compact('estados'));
    }

    /**
     * Processa a requisição de registo unificado (Atleta).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Quando "Sou estrangeiro" está marcado, documento, estado/cidade e endereço não são exigidos
        if ($request->boolean('estrangeiro')) {
            $request->merge([
                'documento' => null,
                'estado_id' => null,
                'cidade_id' => null,
                'cep' => null,
                'logradouro' => null,
                'numero' => null,
                'complemento' => null,
                'bairro' => null,
            ]);
        } else {
            $request->merge([
                'documento' => $request->filled('documento') ? trim($request->input('documento')) : null,
                'estado_id' => $request->input('estado_id') ?: null,
                'cidade_id' => $request->input('cidade_id') ?: null,
                'cep' => $request->filled('cep') ? preg_replace('/\D/', '', $request->input('cep')) : null,
                'logradouro' => $request->filled('logradouro') ? trim($request->input('logradouro')) : null,
                'numero' => $request->filled('numero') ? trim($request->input('numero')) : null,
                'complemento' => $request->filled('complemento') ? trim($request->input('complemento')) : null,
                'bairro' => $request->filled('bairro') ? trim($request->input('bairro')) : null,
            ]);
        }

        // 1. Validação unificada: documento opcional para atletas estrangeiros (login será por e-mail)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'documento' => [
                'nullable',
                'string',
                'max:50',
                'unique:'.User::class.',documento',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === null || trim($value) === '') {
                        return;
                    }
                    $digits = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($digits) === 11 && ! $this->cpfValido($digits)) {
                        $fail('O CPF informado é inválido.');
                    }
                },
            ],
            'telefone' => ['required', 'string', 'max:20'],
            'data_nascimento' => ['required', 'date'],
            'sexo' => ['required', 'string', 'in:masculino,feminino'],
            // Estado e cidade obrigatórios só quando houver documento (brasileiro); estrangeiros podem deixar em branco
            'estado_id' => ['required_if:documento,filled', 'nullable', 'integer', 'exists:estados,id'],
            'cidade_id' => ['required_if:documento,filled', 'nullable', 'integer', 'exists:cidades,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            // Endereço (opcional; preenchido muitas vezes via CEP)
            'cep' => ['nullable', 'string', 'size:8'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:100'],
        ]);

        $user = null;

        // 2. Transação para criar User e Atleta
        try {
            DB::transaction(function () use ($request, $validated, &$user) {
                $doc = isset($validated['documento']) && trim($validated['documento']) !== ''
                    ? preg_replace('/[^0-9A-Za-z]/', '', $validated['documento'])
                    : null;
                if ($doc === '') {
                    $doc = null;
                }
                // users.documento suporta 20 chars; atletas.cpf suporta 14
                $docUser = $doc !== null ? substr($doc, 0, 20) : null;
                $docAtleta = $doc !== null ? substr($doc, 0, 14) : null;

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'profile' => 'atleta',
                    'documento' => $docUser,
                ]);

                $fotoUrl = null;
                if ($request->hasFile('foto')) {
                    $fotoUrl = $request->file('foto')->store('atletas/fotos', 'public');
                }

                $user->atleta()->create([
                    'foto_url' => $fotoUrl,
                    'cpf' => $docAtleta,
                    'data_nascimento' => $validated['data_nascimento'],
                    'sexo' => $validated['sexo'],
                    'telefone' => $validated['telefone'],
                    'cidade_id' => $validated['cidade_id'] ?? null,
                    'estado_id' => $validated['estado_id'] ?? null,
                    'cep' => $validated['cep'] ?? null,
                    'logradouro' => $validated['logradouro'] ?? null,
                    'numero' => $validated['numero'] ?? null,
                    'complemento' => $validated['complemento'] ?? null,
                    'bairro' => $validated['bairro'] ?? null,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Falha no registro: ' . $e->getMessage());
            return back()->withInput()->withErrors(['erro_geral' => 'Não foi possível concluir o seu registo. Por favor, tente novamente.']);
        }

        // 3. Dispara evento de email e faz login
        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Valida CPF (apenas dígitos, 11 caracteres) pelo algoritmo oficial.
     */
    private function cpfValido(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $s = 0;
            for ($c = 0; $c < $t; $c++) {
                $s += (int) $cpf[$c] * ($t + 1 - $c);
            }
            $resto = $s % 11;
            $digito = $resto < 2 ? 0 : 11 - $resto;
            if ((int) $cpf[$c] !== $digito) {
                return false;
            }
        }
        return true;
    }
}

