<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Cupom;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CupomController extends Controller
{
    /**
     * Função de autorização centralizada e à prova de falhas.
     * Esta é a forma mais direta e fiável de verificar a propriedade.
     */
    private function authorizeOwner(Evento $evento)
    {
        // Verifica diretamente na base de dados se existe uma ligação entre
        // o utilizador autenticado e a organização do evento.
        $isOwner = auth()->user()->organizacoes()->where('organizacao_id', $evento->organizacao_id)->exists();

        if (!$isOwner) {
            abort(403, 'Ação não autorizada. Não tem permissão para gerir os recursos desta organização.');
        }
    }

    public function index(Evento $evento)
    {
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'cupons']);
    }

    public function create(Evento $evento): View
    {
        $this->authorizeOwner($evento);
        return view('organizador.cupons.create', compact('evento'));
    }

    public function store(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorizeOwner($evento);

        $validated = $request->validate([
            'codigo' => ['required', 'string', 'uppercase', 'max:50', Rule::unique('cupons')->where('evento_id', $evento->id)],
            'tipo_desconto' => ['required', Rule::in(['percentual', 'fixo'])],
            'valor' => ['required', 'numeric', 'min:0'],
            'limite_usos' => ['nullable', 'integer', 'min:1'],
            'data_validade' => ['nullable', 'date', 'after_or_equal:today'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['evento_id'] = $evento->id;

        Cupom::create($validated);

        return back()->with('sucesso', 'Cupom criado com sucesso!')->with('tab', 'cupons');
    }

    public function update(Request $request, Evento $evento, Cupom $cupom): RedirectResponse
    {
        $this->authorizeOwner($evento);

        $validated = $request->validate([
            // CORREÇÃO DEFINITIVA: Passar o ID explícito ($cupom->id) para o método ignore()
            // é a forma mais robusta e à prova de falhas de dizer ao Laravel para ignorar o registo
            // atual na verificação de unicidade, eliminando a causa do erro.
            'codigo' => [
                'required', 
                'string', 
                'uppercase', 
                'max:50', 
                Rule::unique('cupons')->where('evento_id', $evento->id)->ignore($cupom->id)
            ],
            'tipo_desconto' => ['required', Rule::in(['percentual', 'fixo'])],
            'valor' => ['required', 'numeric', 'min:0'],
            'limite_usos' => ['nullable', 'integer', 'min:1'],
            'data_validade' => ['nullable', 'date', 'after_or_equal:today'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $validated['ativo'] = $request->has('ativo');

        $cupom->update($validated);

        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'cupons'])
                         ->with('sucesso', 'Cupom atualizado com sucesso!');
    }

    public function destroy(Evento $evento, Cupom $cupom): RedirectResponse
    {
        $this->authorizeOwner($evento);
        
        $cupom->delete();

        return back()->with('sucesso', 'Cupom removido com sucesso!')->with('tab', 'cupons');
    }
}

