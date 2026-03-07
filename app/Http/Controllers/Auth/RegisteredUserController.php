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
use Illuminate\Support\Facades\Log; // Adicionado para Log
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
        // 1. Validação unificada para os campos do formulário
        $validated = $request->validate([
            // Campos do User
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            // Campos do Atleta
            'documento' => ['required', 'string', 'max:20', 'unique:'.User::class.',documento'], // Valida unicidade na tabela users
            'telefone' => ['required', 'string', 'max:20'],
            'data_nascimento' => ['required', 'date'],
            'sexo' => ['required', 'string', 'in:masculino,feminino'],
            'estado_id' => ['required', 'integer', 'exists:estados,id'],
            'cidade_id' => ['required', 'integer', 'exists:cidades,id'],
        ]);

        $user = null;

        // 2. Transação para criar User e Atleta
        try {
            DB::transaction(function () use ($validated, &$user, $request) {
                
                $cpfLimpo = preg_replace('/[^0-9]/', '', $validated['documento']);

                // Cria o registro na tabela 'users'
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'profile' => 'atleta', // Perfil padrão
                    'documento' => $cpfLimpo, // Salva o documento na tabela users
                ]);

                // Cria o perfil de Atleta na tabela 'atletas'
                // Esta linha assume que o seu Model User tem a relação: 
                // public function atleta() { return $this->hasOne(Atleta::class); }
                $user->atleta()->create([
                    'cpf' => $cpfLimpo, // Salva também na tabela atletas
                    'data_nascimento' => $validated['data_nascimento'],
                    'sexo' => $validated['sexo'],
                    'telefone' => $validated['telefone'],
                    'cidade_id' => $validated['cidade_id'],
                    'estado_id' => $validated['estado_id'],
                ]);
                
            });
        } catch (\Exception $e) {
            // Em caso de falha, retorna com o erro
            Log::error('Falha no registro: ' . $e->getMessage()); 
            return back()->withInput()->withErrors(['erro_geral' => 'Não foi possível concluir o seu registo. Por favor, tente novamente.']);
        }

        // 3. Dispara evento de email e faz login
        event(new Registered($user));
        Auth::login($user);

        // Redireciona para o dashboard
        return redirect(route('dashboard', absolute: false));
    }
}

