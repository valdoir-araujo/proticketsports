<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\ProdutoOpcional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProdutoOpcionalController extends Controller
{
    /**
     * Armazena um novo produto opcional.
     */
    public function store(Request $request, Evento $evento)
    {
        if (!Auth::user()->organizacoes->contains($evento->organizacao)) {
            abort(403);
        }

        // 1. Validação
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            // Validação ajustada para 5MB e múltiplos formatos
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'valor' => 'required|numeric|min:0',
            'limite_estoque' => 'nullable|integer|min:1',
            'quantidade_gratuidade' => 'nullable|integer|min:0',
            'max_quantidade_por_inscricao' => 'nullable|integer|min:1',
            'requer_tamanho' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ]);

        // 2. Tratamento de Checkboxes
        $validated['requer_tamanho'] = $request->has('requer_tamanho');
        $validated['ativo'] = $request->has('ativo');

        // 3. Upload de Imagem
        if ($request->hasFile('imagem')) {
            $path = $request->file('imagem')->store('produtos/imagens', 'public');
            $validated['imagem_url'] = $path;
        }

        // 4. Criação
        $evento->produtosOpcionais()->create($validated);

        // CORREÇÃO: Redireciona explicitamente para a aba 'produtos'
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'produtos'])
                         ->with('sucesso', 'Produto adicionado com sucesso!');
    }

    /**
     * Mostra o formulário para editar um produto opcional.
     */
    public function edit(Evento $evento, ProdutoOpcional $produto): View
    {
        if (!Auth::user()->organizacoes->contains($evento->organizacao) || $produto->evento_id !== $evento->id) {
            abort(403);
        }

        return view('organizador.produtos.edit', compact('evento', 'produto'));
    }

    /**
     * Atualiza um produto opcional.
     */
    public function update(Request $request, Evento $evento, ProdutoOpcional $produto)
    {
        if (!Auth::user()->organizacoes->contains($evento->organizacao) || $produto->evento_id !== $evento->id) {
            abort(403);
        }

        // 1. Validação (Padronizada com o store: 5MB e formatos)
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', 
            'valor' => 'required|numeric|min:0',
            'limite_estoque' => 'nullable|integer|min:1',
            'quantidade_gratuidade' => 'nullable|integer|min:0',
            'max_quantidade_por_inscricao' => 'nullable|integer|min:1',
            'requer_tamanho' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ]);
        
        // 2. Tratamento de Checkboxes
        $validated['requer_tamanho'] = $request->has('requer_tamanho');
        $validated['ativo'] = $request->has('ativo');

        // 3. Upload de Imagem (com remoção da antiga)
        if ($request->hasFile('imagem')) {
            // Remove imagem antiga se existir
            if ($produto->imagem_url) {
                Storage::disk('public')->delete($produto->imagem_url);
            }
            $path = $request->file('imagem')->store('produtos/imagens', 'public');
            $validated['imagem_url'] = $path;
        }

        // 4. Atualização
        $produto->update($validated);

        // CORREÇÃO: Redireciona explicitamente para a aba 'produtos'
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'produtos'])
                         ->with('sucesso', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove um produto opcional.
     */
    public function destroy(Evento $evento, ProdutoOpcional $produto)
    {
        if (!Auth::user()->organizacoes->contains($evento->organizacao) || $produto->evento_id !== $evento->id) {
            abort(403);
        }

        // Apaga a imagem do storage antes de remover o registro do banco
        if ($produto->imagem_url) {
            Storage::disk('public')->delete($produto->imagem_url);
        }

        $produto->delete();

        // CORREÇÃO: Redireciona explicitamente para a aba 'produtos'
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'produtos'])
                         ->with('sucesso', 'Produto removido com sucesso!');
    }
}