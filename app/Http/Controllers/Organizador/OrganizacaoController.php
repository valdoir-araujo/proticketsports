<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Organizacao;
use App\Models\Inscricao;
use App\Models\Estado; // Adicionado
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Adicionado para upload de logo
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrganizacaoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mostra o formulário para criar uma nova organização.
     */
    public function create(): View|RedirectResponse
    {
        $estados = Estado::orderBy('nome')->get();
        return view('organizador.organizacao.create', compact('estados'));
    }

    /**
     * Salva uma nova organização no banco de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'nome_fantasia' => 'required|string|max:191',
            'documento'     => 'required|string|max:20', // CNPJ ou CPF
            'email'         => 'required|email|max:255',
            'celular'       => 'nullable|string|max:20', // O form envia 'celular'
            'estado_id'     => 'required|exists:estados,id',
            'cidade_id'     => 'required|exists:cidades,id',
            'logo'          => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user, $validated, $request) {
                
                $data = [
                    'user_id'       => $user->id,
                    'nome'          => $validated['nome'],
                    'nome_fantasia' => $validated['nome_fantasia'],
                    'documento'     => $validated['documento'],
                    'email'         => $validated['email'],
                    'telefone'      => $request->celular ?? $request->telefone, // Mapeia celular do form para telefone do banco
                    'cidade_id'     => $validated['cidade_id'],
                ];

                // Upload da Logo
                if ($request->hasFile('logo')) {
                    $data['logo_url'] = $request->file('logo')->store('logos_organizacoes', 'public');
                }

                // 1. Cria a Organização
                $organizacao = Organizacao::create($data);
                
                // 2. Cria o Vínculo na Tabela Pivô
                $user->organizacoes()->attach($organizacao->id, ['role' => 'owner']);

                // 3. Atualiza o papel do usuário (se necessário)
                if (!$user->isOrganizador() && !$user->isAdmin()) {
                    $user->role = 'organizador';
                    if (isset($user->tipo_usuario)) {
                        $user->tipo_usuario = 'organizador';
                    }
                    $user->save();
                }
            });

            return redirect()->route('organizador.index') // Redireciona para a lista (index)
                             ->with('sucesso', 'Organização criada com sucesso! Você já pode gerenciar seus eventos.');

        } catch (\Exception $e) {
            return back()->withInput()
                         ->withErrors(['msg' => 'Erro ao criar organização: ' . $e->getMessage()]);
        }
    }

    /**
     * Exibe o formulário de edição da organização.
     * (Método que estava faltando)
     */
    public function edit(Organizacao $organizacao): View
    {
        // Verifica permissão (se o usuário pertence à organização)
        if (!$organizacao->users->contains(Auth::id())) {
            abort(403, 'Você não tem permissão para editar esta organização.');
        }

        $estados = Estado::orderBy('nome')->get();
        
        // Carrega relacionamento para preencher o select de cidade corretamente
        $organizacao->load('cidade.estado');

        return view('organizador.edit', compact('organizacao', 'estados'));
    }

    /**
     * Atualiza os dados da organização.
     * (Método que estava incompleto)
     */
    public function update(Request $request, Organizacao $organizacao): RedirectResponse
    {
        // Verifica permissão
        if (!$organizacao->users->contains(Auth::id())) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'nome'          => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'documento'     => 'required|string|max:20',
            'email'         => 'required|email|max:255',
            'celular'       => 'nullable|string|max:20',
            'estado_id'     => 'required|exists:estados,id',
            'cidade_id'     => 'required|exists:cidades,id',
            'logo'          => 'nullable|image|max:2048',
        ]);

        $data = [
            'nome'          => $validated['nome'],
            'nome_fantasia' => $validated['nome_fantasia'],
            'documento'     => $validated['documento'],
            'email'         => $validated['email'],
            'telefone'      => $request->celular, // Mapeia o input 'celular' para a coluna 'telefone'
            'cidade_id'     => $validated['cidade_id'],
        ];

        // Upload de Nova Logo
        if ($request->hasFile('logo')) {
            // Apaga a antiga se existir
            if ($organizacao->logo_url && Storage::disk('public')->exists($organizacao->logo_url)) {
                Storage::disk('public')->delete($organizacao->logo_url);
            }
            $data['logo_url'] = $request->file('logo')->store('logos_organizacoes', 'public');
        }

        $organizacao->update($data);

        return redirect()->route('organizador.index')
                         ->with('sucesso', 'Dados da organização atualizados com sucesso!');
    }

    /**
     * Exibe o dashboard financeiro.
     */
    public function financeiro(Request $request): View|RedirectResponse
    {
        $organizador = Auth::user();
        
        $orgId = $request->query('org_id');
        $organizacao = null;

        if ($orgId) {
            $organizacao = $organizador->organizacoes()->find($orgId);
        } else {
            $organizacao = $organizador->organizacoes()->first();
        }

        if (!$organizacao) {
            return redirect()->route('organizador.organizacao.create')
                             ->with('info', 'Crie sua primeira organização para acessar o financeiro.');
        }

        $eventosIds = $organizacao->eventos()->pluck('id');

        $totalArrecadado = Inscricao::whereIn('evento_id', $eventosIds)
            ->where('status', 'confirmada')
            ->sum('valor_pago');

        $taxaPlataforma = Inscricao::whereIn('evento_id', $eventosIds)
            ->where('status', 'confirmada')
            ->sum('taxa_plataforma');
            
        $totalRepassado = 0;
        $repasses = collect(); 

        if (method_exists($organizacao, 'repasses')) {
            $totalRepassado = $organizacao->repasses()
                ->where('status', 'efetuado')
                ->sum('valor_total_repassado');
            
            $repasses = $organizacao->repasses()->latest('data_repassado')->paginate(10);
        }
            
        $valorAReceber = ($totalArrecadado - $taxaPlataforma) - $totalRepassado;
        
        return view('organizador.financeiro.index', compact(
            'organizacao',
            'totalArrecadado',
            'taxaPlataforma',
            'totalRepassado',
            'valorAReceber',
            'repasses'
        ));
    }
}