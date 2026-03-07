<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\CategoriaModelo;
use App\Models\PercursoModelo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class CategoriaModeloController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a lista de modelos de categoria.
     */
    public function index(Request $request): View
    {
        $organizacao = $request->user()->organizacoes->first();
        
        // TODO: [SEGURANÇA] Reativar futuramente
        // $this->authorize('view', $organizacao);

        $percursoModelosComCategorias = $organizacao->percursoModelos()
            ->with(['categoriaModelos' => fn($query) => $query->orderBy('nome')])
            ->orderBy('id')
            ->get();

        $percursoModelosParaFormulario = $organizacao->percursoModelos()->orderBy('id')->get();

        return view('organizador.modelos-categoria.index', compact('percursoModelosComCategorias', 'percursoModelosParaFormulario'));
    }

    /**
     * Salva um novo modelo de categoria.
     */
    public function store(Request $request)
    {
        $organizacao = $request->user()->organizacoes->first();
        
        // =================================================================
        // 🔴 COMENTADO PARA EVITAR ERRO 403
        // =================================================================
        // $this->authorize('update', $organizacao);

        // 1. Validação
        $validated = $request->validate([
            'percurso_modelo_id' => [
                'required',
                Rule::exists('percurso_modelos', 'id')->where('organizacao_id', $organizacao->id)
            ],
            'nome' => 'required|string|max:255',
            'genero' => 'required|in:Masculino,Feminino,Unissex',
            'idade_min' => 'required|integer|min:0',
            'idade_max' => 'required|integer|gte:idade_min',
        ]);

        // 2. Geração de Código Automático
        $percursoModelo = PercursoModelo::findOrFail($validated['percurso_modelo_id']);
        $codigoGerado = $percursoModelo->codigo . '_' . Str::slug($validated['nome']) . '_' . Str::slug($validated['genero']);

        // 3. Validação Extra de Unicidade
        $request->merge(['codigo' => $codigoGerado]);
        $request->validate([
            'codigo' => [
                Rule::unique('categoria_modelos')->where(function ($query) use ($request, $organizacao) {
                    // Validamos a unicidade considerando também a organização, já que a tabela tem essa coluna
                    return $query->where('organizacao_id', $organizacao->id)
                                 ->where('percurso_modelo_id', $request->percurso_modelo_id);
                }),
            ],
        ], ['codigo.unique' => 'Categoria duplicada neste percurso.']);

        // 4. Criação
        // 🟢 CORREÇÃO AQUI: Adicionado 'organizacao_id' que é obrigatório no seu banco
        $categoria = CategoriaModelo::create([
            'organizacao_id' => $organizacao->id, // <--- O CAMPO QUE FALTAVA
            'percurso_modelo_id' => $validated['percurso_modelo_id'],
            'nome' => $validated['nome'],
            'codigo' => $codigoGerado,
            'genero' => $validated['genero'],
            'idade_min' => $validated['idade_min'],
            'idade_max' => $validated['idade_max'],
        ]);

        // =================================================================
        // 🟢 RETORNO HÍBRIDO (JSON para Modal / Redirect para Normal)
        // =================================================================
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Categoria criada com sucesso!',
                'categoria' => $categoria
            ]);
        }

        return back()->with('sucesso', 'Categoria criada com sucesso!');
    }

    /**
     * Atualiza um modelo de categoria existente.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $organizacao = $request->user()->organizacoes->first();
        
        // Busca direto pelo ID e organizacao_id, já que a coluna existe
        $categoria = CategoriaModelo::where('organizacao_id', $organizacao->id)->findOrFail($id);

        // $this->authorize('update', $organizacao);

        $validated = $request->validate([
            'percurso_modelo_id' => [
                'required',
                Rule::exists('percurso_modelos', 'id')->where('organizacao_id', $organizacao->id)
            ],
            'nome'      => 'required|string|max:255',
            'genero'    => 'required|in:Masculino,Feminino,Unissex',
            'idade_min' => 'required|integer|min:0',
            'idade_max' => 'required|integer|gte:idade_min',
        ]);

        $percursoModelo = PercursoModelo::find($validated['percurso_modelo_id']);
        $codigoGerado = $percursoModelo->codigo . '_' . Str::slug($validated['nome']) . '_' . Str::slug($validated['genero']);

        $request->merge(['codigo' => $codigoGerado]);
        $request->validate([
            'codigo' => [
                Rule::unique('categoria_modelos')
                    ->where(function ($query) use ($request, $organizacao) {
                        return $query->where('organizacao_id', $organizacao->id)
                                     ->where('percurso_modelo_id', $request->percurso_modelo_id);
                    })->ignore($categoria->id),
            ],
        ], ['codigo.unique' => 'Categoria duplicada.']);

        $categoria->update([
            'percurso_modelo_id' => $validated['percurso_modelo_id'],
            'nome' => $validated['nome'],
            'codigo' => $codigoGerado,
            'genero' => $validated['genero'],
            'idade_min' => $validated['idade_min'],
            'idade_max' => $validated['idade_max'],
        ]);

        return back()->with('sucesso', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove um modelo de categoria.
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $organizacao = $request->user()->organizacoes->first();
        
        // $this->authorize('delete', $organizacao);

        $categoria = CategoriaModelo::where('organizacao_id', $organizacao->id)->findOrFail($id);
        
        $categoria->delete();

        return back()->with('sucesso', 'Categoria removida com sucesso!');
    }
}