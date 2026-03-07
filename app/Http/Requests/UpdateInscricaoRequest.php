<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInscricaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inscricao = $this->route('inscricao');
        return $inscricao && $this->user() && $inscricao->atleta->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'equipe_id' => 'nullable|exists:equipes,id',
            'produtos' => 'nullable|array',
            'produtos.*.id' => 'required|integer|exists:produtos_opcionais,id',
            'produtos.*.quantidade' => 'required|integer|min:0',
            'produtos.*.tamanho' => 'nullable|string',
        ];
    }
}
