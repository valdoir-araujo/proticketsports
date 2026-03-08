<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParceiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Rota protegida pelo middleware is_admin
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', Rule::in(array_keys(\App\Models\Parceiro::TIPOS))],
            'descricao' => ['nullable', 'string', 'max:15000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'site_url' => ['nullable', 'url', 'max:500'],
            'instagram' => ['nullable', 'url', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome do parceiro',
            'tipo' => 'tipo de parceiro',
            'descricao' => 'descrição',
            'logo' => 'logo',
            'site_url' => 'site',
            'instagram' => 'Instagram',
            'email' => 'e-mail',
            'telefone' => 'telefone',
            'ordem' => 'ordem de exibição',
            'ativo' => 'ativo',
        ];
    }
}
