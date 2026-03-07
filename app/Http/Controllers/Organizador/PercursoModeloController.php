<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\PercursoModelo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PercursoModeloController extends Controller
{
    use AuthorizesRequests;

    /**
     * Helper para obter a organização correta baseada no contexto ou fallback.
     */
    private function getOrganizacaoContexto(Request $request)
    {
        // 1. Tenta pegar o ID da organização passado na URL (aceita org_id ou organizacao_id)
        $orgId = $request->input('org_id') ?? $request->input('organizacao_id');

        // 2. Se tiver ID, verifica se o usuário é dono/membro dessa organização
        if ($orgId) {
            $org = $request->user()->organizacoes()->find($orgId);
            if ($org) return $org;
        }

        // 3. Segurança para AJAX/JSON (Selects e APIs)
        // Se a requisição for JSON e não tiver ID explícito, retorna null.
        // Isso impede que dados da "Organização A" vazem para a "Organização B".
        if ($request->wantsJson()) {
            return null;
        }

        // 4. Fallback apenas para telas HTML (View): Pega a primeira organização do usuário
        return $request->user()->organizacoes->first();
    }

    /**
     * Exibe a lista de modelos de percurso da organização.
     * Suporta retorno JSON para preenchimento de selects via AJAX.
     */
    public function index(Request $request)
    {
        $organizacao = $this->getOrganizacaoContexto($request);

        // Se nenhuma organização for identificada (especialmente no caso de JSON sem ID), retorna vazio.
        // Isso garante que não misturamos dados de organizações diferentes.
        if (!$organizacao) {
            $empty = collect([]);
            return $request->wantsJson() 
                ? response()->json($empty) 
                : view('organizador.modelos-percurso.index', ['percursoModelos' => $empty]);
        }

        // Busca APENAS os modelos desta organização específica
        $percursoModelos = $organizacao->percursoModelos()->orderBy('descricao')->get();

        // Retorna JSON se a requisição for AJAX
        if ($request->wantsJson()) {
            return response()->json($percursoModelos);
        }

        // Passamos a organização para a view para dar contexto visual (opcional)
        return view('organizador.modelos-percurso.index', compact('percursoModelos', 'organizacao'));
    }

    /**
     * Armazena um novo modelo de percurso no banco de dados.
     */
    public function store(Request $request)
    {
        $organizacao = $this->getOrganizacaoContexto($request);

        // Se não identificou organização, NÃO tenta adivinhar. Retorna erro.
        // Isso previne criar modelos na organização errada acidentalmente.
        if (!$organizacao) {
            $msg = 'Nenhuma organização selecionada. Verifique se você selecionou o contexto correto.';
            return $request->wantsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->withErrors(['erro' => $msg]);
        }
        
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:20',
                'alpha_dash', // Permite letras, números, traços e underscores
                // Garante que o código seja único APENAS dentro desta organização
                Rule::unique('percurso_modelos')->where('organizacao_id', $organizacao->id),
            ],
        ]);

        // Cria o modelo vinculado à organização correta
        $modelo = $organizacao->percursoModelos()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Modelo criado com sucesso!',
                'modelo' => $modelo
            ]);
        }

        // Mantém o org_id na URL ao redirecionar para não perder o contexto
        return redirect()->route('organizador.modelos-percurso.index', ['org_id' => $organizacao->id])
                         ->with('sucesso', 'Modelo de percurso criado com sucesso!');
    }

    /**
     * Remove o modelo especificado do banco de dados.
     */
    public function destroy(Request $request, $id)
    {
        // Busca o modelo globalmente primeiro para descobrir de qual organização ele é
        $modelo = PercursoModelo::findOrFail($id);

        // Verifica se o usuário atual tem acesso à organização dona deste modelo
        if (!$request->user()->organizacoes->contains($modelo->organizacao_id)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Acesso não autorizado.'], 403);
            }
            abort(403, 'Acesso não autorizado a este modelo.');
        }

        // Verifica se existem percursos (eventos) usando este modelo antes de excluir
        if (method_exists($modelo, 'percursos') && $modelo->percursos()->exists()) {
            $msg = 'Não é possível excluir este modelo pois ele está sendo usado em eventos.';
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->withErrors(['erro' => $msg]);
        }

        $modelo->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Modelo removido com sucesso!']);
        }

        return back()->with('sucesso', 'Modelo de percurso removido com sucesso!');
    }
}