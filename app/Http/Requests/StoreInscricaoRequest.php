<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscricaoRequest extends FormRequest
{
    /**
     * Autorização fica a cargo do InscricaoController (getInscricaoUser + redirect
     * para identificação ou profile.edit), para evitar 403 e tratar usuário por sessão.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza o array de produtos: só considera itens que tenham [id] enviado (checkbox marcado).
     * Quando o checkbox está desmarcado, produtos[ID][id] não vem no POST, mas produtos[ID][quantidade]
     * pode vir. Não usar a chave do array como id — senão todos os produtos seriam incluídos.
     */
    protected function prepareForValidation(): void
    {
        $produtos = $this->input('produtos', []);
        if (! is_array($produtos)) {
            return;
        }

        $normalized = [];
        foreach ($produtos as $key => $item) {
            if (! is_array($item)) {
                continue;
            }
            // Só incluir se o checkbox foi enviado (id presente). Sem isso = produto não selecionado.
            if (! isset($item['id']) || $item['id'] === '' || $item['id'] === null) {
                continue;
            }
            $qty = (int) ($item['quantidade'] ?? 0);
            if ($qty < 1) {
                continue;
            }
            $normalized[] = [
                'id' => (int) $item['id'],
                'quantidade' => $qty,
                'tamanho' => $item['tamanho'] ?? null,
            ];
        }

        $this->merge(['produtos' => $normalized]);
    }

    public function rules(): array
    {
        return [
            'evento_id' => ['required', 'exists:eventos,id'],
            'categoria_id' => 'required|exists:categorias,id',
            'equipe_id' => 'nullable|integer|exists:equipes,id',
            'produtos' => 'nullable|array',
            'produtos.*.id' => 'required|integer|exists:produtos_opcionais,id',
            'produtos.*.quantidade' => 'required|integer|min:1',
            'produtos.*.tamanho' => 'nullable|string',
            'parceiro_id' => 'nullable|integer|exists:atletas,id',
            'tipo_pagamento_dupla' => 'nullable|in:unico,individual',
            // Corrida: opcionais
            'ritmo_previsto' => 'nullable|string|max:50',
            'pelotao_largada' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'evento_id.exists' => 'Evento não encontrado.',
            'categoria_id.required' => 'Selecione uma categoria.',
            'categoria_id.exists' => 'Categoria inválida.',
        ];
    }
}
