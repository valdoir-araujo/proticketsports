<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInscricaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inscricao = $this->route('inscricao');
        if (! $inscricao || ! $this->user()) {
            return false;
        }
        if ($inscricao->atleta->user_id === $this->user()->id) {
            return true;
        }
        $organizacao = $inscricao->evento->organizacao ?? null;
        return $organizacao && $this->user()->organizacoes->contains($organizacao);
    }

    /**
     * Normaliza o array de produtos: mantém apenas itens com id válido (ou chave como id)
     * e quantidade >= 0, para evitar erro "valor obrigatório para produtos.X.id".
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
            if ($qty < 0) {
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
            'equipe_id' => 'nullable|exists:equipes,id',
            'produtos' => 'nullable|array',
            'produtos.*.id' => 'required|integer|exists:produtos_opcionais,id',
            'produtos.*.quantidade' => 'required|integer|min:0',
            'produtos.*.tamanho' => 'nullable|string',
        ];
    }
}
