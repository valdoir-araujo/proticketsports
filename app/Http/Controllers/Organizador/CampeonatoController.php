<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Campeonato;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampeonatoController extends Controller
{
    /**
     * Exibe a lista de campeonatos da organização do usuário.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $organizacao = $request->user()->organizacoes->first();

        if (!$organizacao) {
            return redirect()->route('organizador.organizacao.create')
                ->with('info', 'Para começar, você precisa criar sua primeira organização.');
        }

        // ==========================================================
        // ⬇️ CORREÇÃO APLICADA AQUI ⬇️
        // ==========================================================
        $campeonatos = $organizacao->campeonatos()
            ->withCount('eventos') // Adiciona a contagem de eventos (etapas)
            ->latest()
            ->paginate(10);

        return view('organizador.campeonatos.index', compact('campeonatos'));
    }

    /**
     * Mostra o formulário para criar um novo campeonato.
     */
    public function create(): View
    {
        return view('organizador.campeonatos.create');
    }

    /**
     * Salva um novo campeonato no banco de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ano' => 'required|integer|min:2024',
            'descricao' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);
        
        $organizacao = $request->user()->organizacoes->first();
        if (!$organizacao) {
            abort(403, 'Você não pertence a uma organização.');
        }

        $validated['organizacao_id'] = $organizacao->id;
        $validated['slug'] = Str::slug($validated['nome'] . '-' . $validated['ano']);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('campeonatos/logos', 'public');
            $validated['logo_url'] = $path;
        }

        Campeonato::create($validated);

        return redirect()->route('organizador.campeonatos.index')
                         ->with('sucesso', 'Campeonato criado com sucesso!');
    }

    /**
     * Exibe os detalhes de um campeonato específico.
     */
    public function show(Campeonato $campeonato): View
    {
        if (!Auth::user()->organizacoes->contains($campeonato->organizacao)) {
            abort(403);
        }

        $campeonato->load('eventos');
        return view('organizador.campeonatos.show', compact('campeonato'));
    }

    /**
     * Mostra o formulário para editar um campeonato.
     */
    public function edit(Campeonato $campeonato): View
    {
        if (!Auth::user()->organizacoes->contains($campeonato->organizacao)) {
            abort(403);
        }
        
        return view('organizador.campeonatos.edit', compact('campeonato'));
    }

    /**
     * Atualiza um campeonato no banco de dados.
     */
    public function update(Request $request, Campeonato $campeonato): RedirectResponse
    {
        if (!Auth::user()->organizacoes->contains($campeonato->organizacao)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ano' => 'required|integer|min:2024',
            'descricao' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);
        
        $validated['slug'] = Str::slug($validated['nome'] . '-' . $validated['ano']);

        if ($request->hasFile('logo')) {
            if ($campeonato->logo_url) {
                Storage::disk('public')->delete($campeonato->logo_url);
            }
            $path = $request->file('logo')->store('campeonatos/logos', 'public');
            $validated['logo_url'] = $path;
        }

        $campeonato->update($validated);

        return redirect()->route('organizador.campeonatos.index')
                         ->with('sucesso', 'Campeonato atualizado com sucesso!');
    }

    /**
     * Exclui um campeonato.
     */
    public function destroy(Campeonato $campeonato): RedirectResponse
    {
        if (!Auth::user()->organizacoes->contains($campeonato->organizacao)) {
            abort(403);
        }
        
        if ($campeonato->logo_url) {
            Storage::disk('public')->delete($campeonato->logo_url);
        }

        $campeonato->delete();
        
        return redirect()->route('organizador.campeonatos.index')
                         ->with('sucesso', 'Campeonato excluído com sucesso.');
    }
}