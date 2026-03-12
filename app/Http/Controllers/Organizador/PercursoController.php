<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Percurso;
use App\Models\PercursoModelo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PercursoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Cria os percursos modelo Corrida (5K, 10K, 21K) para evento marcado como Corrida.
     * Só disponível quando o evento não tem percursos e a modalidade é Corrida.
     */
    public function storePercursosModeloCorrida(Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);

        if (!$evento->isCorrida()) {
            return redirect(route('organizador.eventos.show', [$evento, 'tab' => 'percursos']))
                ->with('erro', 'Esta ação é válida apenas para eventos do tipo Corrida.');
        }

        if ($evento->percursos()->exists()) {
            return redirect(route('organizador.eventos.show', [$evento, 'tab' => 'percursos']))
                ->with('erro', 'O evento já possui percursos. Adicione manualmente ou remova os existentes.');
        }

        $modelo = [
            ['descricao' => '5K',  'distancia_km' => 5,   'horario_largada' => '07:00', 'horario_alinhamento' => '06:45'],
            ['descricao' => '10K', 'distancia_km' => 10,  'horario_largada' => '07:15', 'horario_alinhamento' => '07:00'],
            ['descricao' => '21K', 'distancia_km' => 21.1, 'horario_largada' => '07:30', 'horario_alinhamento' => '07:15'],
        ];

        foreach ($modelo as $dados) {
            $evento->percursos()->create([
                'percurso_modelo_id' => null,
                'descricao' => $dados['descricao'],
                'distancia_km' => $dados['distancia_km'],
                'altimetria_metros' => 0,
                'horario_alinhamento' => $dados['horario_alinhamento'],
                'horario_largada' => $dados['horario_largada'],
            ]);
        }

        return redirect(route('organizador.eventos.show', [$evento, 'tab' => 'percursos']))
            ->with('sucesso', 'Percursos modelo Corrida (5K, 10K, 21K) criados. Configure as categorias e lotes em cada percurso.');
    }

    /**
     * Salva um novo percurso para um evento, baseado num modelo da biblioteca.
     */
    public function store(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);

        $validated = $request->validate([
            'percurso_modelo_id' => 'required|exists:percurso_modelos,id',
            'distancia_km' => 'required|numeric|min:0|decimal:0,3',
            'altimetria_metros' => 'required|integer|min:0',
            'horario_alinhamento' => ['required', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'horario_largada' => ['required', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'strava_route_url' => 'nullable|url',
        ], [
            'percurso_modelo_id.required' => 'É obrigatório selecionar um modelo de percurso da biblioteca.',
            'distancia_km.decimal' => 'O campo distância deve ter no máximo 3 casas decimais (ex: 50.55).',
        ]);
        
        $percursoModelo = PercursoModelo::find($validated['percurso_modelo_id']);

        $evento->percursos()->create([
            'percurso_modelo_id' => $percursoModelo->id,
            'descricao' => $percursoModelo->descricao,
            'distancia_km' => $validated['distancia_km'],
            'altimetria_metros' => $validated['altimetria_metros'],
            'horario_alinhamento' => $validated['horario_alinhamento'],
            'horario_largada' => $validated['horario_largada'],
            'strava_route_url' => $validated['strava_route_url'],
        ]);

        return redirect(route('organizador.eventos.show', $evento) . '#percursos')->with('sucesso', 'Percurso adicionado com sucesso!');
    }

    /**
     * Vincula um percurso antigo a um modelo da biblioteca.
     */
    public function link(Request $request, Evento $evento, Percurso $percurso): RedirectResponse
    {
        $this->authorize('update', $evento);

        if ($percurso->percurso_modelo_id) {
            return back()->withErrors(['msg' => 'Este percurso já está vinculado a um modelo.']);
        }

        $validated = $request->validate([
            'percurso_modelo_id' => 'required|exists:percurso_modelos,id',
        ]);

        $percursoModelo = PercursoModelo::find($validated['percurso_modelo_id']);

        $percurso->update([
            'percurso_modelo_id' => $percursoModelo->id,
            'descricao' => $percursoModelo->descricao,
        ]);

        return redirect(route('organizador.eventos.show', $evento) . '#percursos')->with('sucesso', "Percurso '{$percurso->descricao}' vinculado à biblioteca com sucesso!");
    }

    /**
     * Atualiza um percurso existente.
     */
    public function update(Request $request, Evento $evento, Percurso $percurso): RedirectResponse
    {
        $this->authorize('update', $evento);

        // A validação para o update não inclui a descrição, pois ela é herdada do modelo
        // e não deve ser editada aqui. Apenas os detalhes específicos do evento são alterados.
        $validated = $request->validate([
            'distancia_km' => 'required|numeric|min:0|decimal:0,3',
            'altimetria_metros' => 'required|integer|min:0',
            'horario_alinhamento' => ['required', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'horario_largada' => ['required', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'strava_route_url' => 'nullable|url',
        ]);

        $percurso->update($validated);

        return redirect(route('organizador.eventos.show', $evento) . '#percursos')->with('sucesso', 'Percurso atualizado com sucesso!');
    }

    /**
     * Remove um percurso.
     */
    public function destroy(Evento $evento, Percurso $percurso): RedirectResponse
    {
        $this->authorize('update', $evento);

        if ($percurso->categorias()->has('inscricoes')->exists()) {
            return redirect(route('organizador.eventos.show', $evento) . '#percursos')->withErrors(['msg' => 'Não é possível remover este percurso pois existem atletas inscritos em suas categorias.']);
        }
        
        $percurso->delete();

        return redirect(route('organizador.eventos.show', $evento) . '#percursos')->with('sucesso', 'Percurso removido com sucesso!');
    }
}

