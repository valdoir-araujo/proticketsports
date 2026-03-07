<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $evento = $this->route('evento');
        return $evento && $this->user()?->can('update', $evento);
    }

    public function rules(): array
    {
        $evento = $this->route('evento');

        return [
            'nome' => ['required', 'string', 'max:255', Rule::unique('eventos')->ignore($evento->id)],
            'modalidade_id' => 'required|integer|exists:modalidades,id',
            'campeonato_id' => 'nullable|integer|exists:campeonatos,id',
            'pontos_multiplicador' => 'required|integer|min:1',
            'data_evento' => 'required|date',
            'data_inicio_inscricoes' => 'required|date',
            'data_fim_inscricoes' => 'required|date|after:data_inicio_inscricoes',
            'local' => 'required|string|max:255',
            'estado_id' => 'required|exists:estados,id',
            'cidade_id' => 'required|exists:cidades,id',
            'descricao_completa' => 'nullable|string',
            'lista_inscritos_publica' => 'required|boolean',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.unique' => 'Já existe um evento com este nome.',
            'data_fim_inscricoes.after' => 'A data de encerramento das inscrições deve ser após a data de abertura.',
        ];
    }
}
