<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->organizacoes()->exists();
    }

    public function rules(): array
    {
        return [
            'organizacao_id' => 'required|exists:organizacoes,id',
            'nome' => 'required|string|max:255',
            'modalidade_id' => 'required|integer|exists:modalidades,id',
            'campeonato_id' => 'nullable|integer|exists:campeonatos,id',
            'pontos_multiplicador' => 'required|integer|min:1',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'data_evento' => 'required|date',
            'data_inicio_inscricoes' => 'required|date',
            'data_fim_inscricoes' => 'required|date|after:data_inicio_inscricoes',
            'local' => 'required|string|max:255',
            'estado_id' => 'required|exists:estados,id',
            'cidade_id' => 'required|exists:cidades,id',
            'descricao_completa' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'organizacao_id.required' => 'Selecione a organização.',
            'organizacao_id.exists' => 'Organização inválida.',
            'data_fim_inscricoes.after' => 'A data de encerramento das inscrições deve ser após a data de abertura.',
        ];
    }
}
