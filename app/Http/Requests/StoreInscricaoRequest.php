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
     * Normaliza o array de produtos antes da validação: o formulário pode enviar
     * produtos[ID][quantidade] e produtos[ID][tamanho] mesmo quando o checkbox
     * produtos[ID][id] não foi enviado (ex.: checkbox desmarcado). Mantemos apenas
     * itens com id válido (ou chave numérica) e quantidade >= 1.
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
            $id = $item['id'] ?? $key;
            if ($id === '' || $id === null) {
                continue;
            }
            $qty = (int) ($item['quantidade'] ?? 0);
            if ($qty < 1) {
                continue;
            }
            $normalized[] = [
                'id' => (int) $id,
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
