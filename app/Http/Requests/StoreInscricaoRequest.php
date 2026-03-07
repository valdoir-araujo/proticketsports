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
