<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventoController extends Controller
{
    /**
     * Exibe uma lista de todos os eventos da plataforma.
     */
    public function index(): View
    {
        // Buscamos todos os eventos, com seus relacionamentos para evitar
        // múltiplas queries (N+1 problem).
        // Ordenamos pelos mais recentes e paginamos.
        $eventos = Evento::with(['organizacao', 'cidade.estado'])
                         ->latest()
                         ->paginate(15);

        return view('admin.eventos.index', compact('eventos'));
    }

    /**
     * Mostra o formulário de edição para o Administrador.
     * (NOVO MÉTODO)
     */
    public function edit(Evento $evento): View
    {
        return view('admin.eventos.edit', compact('evento'));
    }

    /**
     * Atualiza o evento (Status ou Taxa de Serviço).
     */
    public function update(Request $request, Evento $evento): RedirectResponse
    {
        // Validação expandida para aceitar Status E Taxa de Serviço
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:publicado,rascunho,cancelado,concluido'],
            'taxaservico' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $evento->update($validated);

        // Redireciona para a lista (index) para facilitar o fluxo de trabalho
        return redirect()->route('admin.eventos.index')->with('sucesso', 'Evento atualizado com sucesso!');
    }
}