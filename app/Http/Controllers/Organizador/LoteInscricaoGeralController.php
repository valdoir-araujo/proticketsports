<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\LoteInscricaoGeral;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoteInscricaoGeralController extends Controller
{
    use AuthorizesRequests;

    /**
     * Valida os dados de um lote, verificando sobreposição de datas.
     */
    private function validateLoteRequest(Request $request, Evento $evento, ?LoteInscricaoGeral $loteAtual = null)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        $validator->after(function ($validator) use ($request, $evento, $loteAtual) {
            $dataInicio = $request->input('data_inicio');
            $dataFim = $request->input('data_fim');

            if (!$dataInicio || !$dataFim) {
                return;
            }

            $query = LoteInscricaoGeral::where('evento_id', $evento->id)
                ->where(function ($query) use ($dataInicio, $dataFim) {
                    $query->where('data_inicio', '<', $dataFim)
                          ->where('data_fim', '>', $dataInicio);
                });
            
            if ($loteAtual) {
                $query->where('id', '!=', $loteAtual->id);
            }

            if ($query->exists()) {
                $validator->errors()->add('data_inicio', 'As datas deste lote geral estão em conflito com um lote já existente.');
            }
        });

        return $validator;
    }

    /**
     * Salva um novo lote geral para o evento.
     */
    public function store(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);
        
        $validator = $this->validateLoteRequest($request, $evento);

        if ($validator->fails()) {
            return redirect()->route('organizador.eventos.show', $evento)
                             ->withFragment('lotes_gerais')
                             ->withErrors($validator)
                             ->withInput();
        }

        $evento->lotesInscricaoGeral()->create($validator->validated());

        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'lotes_gerais'])
                         ->with('sucesso', 'Lote geral criado com sucesso!');
    }

    /**
     * Atualiza um lote geral existente.
     */
    public function update(Request $request, Evento $evento, LoteInscricaoGeral $lotes_gerai): RedirectResponse
    {
        $this->authorize('update', $evento);

        $validator = $this->validateLoteRequest($request, $evento, $lotes_gerai);

        if ($validator->fails()) {
            // AJUSTE: Redireciona explicitamente para a página do evento em caso de erro,
            // o que é mais seguro do que usar back() com modais.
            return redirect()->route('organizador.eventos.show', $evento)
                             ->withFragment('lotes_gerais') // Foca na aba correta
                             ->withErrors($validator, 'updateLoteGeral') // Guarda os erros num "error bag"
                             ->withInput();
        }

        $lotes_gerai->update($validator->validated());

        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'lotes_gerais'])
                         ->with('sucesso', 'Lote geral atualizado com sucesso!');
    }


    /**
     * Remove um lote geral do evento.
     */
    public function destroy(Evento $evento, LoteInscricaoGeral $lotes_gerai): RedirectResponse
    {
        $this->authorize('update', $evento);

        $lotes_gerai->delete();

        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'lotes_gerais'])
                         ->with('sucesso', 'Lote geral removido com sucesso!');
    }
}

