<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContatoController extends Controller
{
    public function index(): View
    {
        $contatos = Contato::orderBy('ordem')->orderBy('area')->get();
        return view('admin.contatos.index', compact('contatos'));
    }

    public function create(): View
    {
        return view('admin.contatos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'area' => 'required|string|max:120',
            'nome' => 'required|string|max:120',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'telefone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:120',
            'icone' => 'nullable|string|max:80',
            'cor' => 'required|in:orange,blue,emerald,violet',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'boolean',
        ]);

        $validated['icone'] = $validated['icone'] ?? 'fa-solid fa-user';
        $validated['ordem'] = (int) ($validated['ordem'] ?? 0);
        $validated['ativo'] = $request->boolean('ativo');
        unset($validated['foto']);

        if ($request->hasFile('foto')) {
            $validated['foto_url'] = $request->file('foto')->store('contatos', 'public');
        }

        Contato::create($validated);
        return redirect()->route('admin.contatos.index')->with('sucesso', 'Contato cadastrado com sucesso.');
    }

    public function edit(Contato $contato): View
    {
        return view('admin.contatos.edit', compact('contato'));
    }

    public function update(Request $request, Contato $contato): RedirectResponse
    {
        $validated = $request->validate([
            'area' => 'required|string|max:120',
            'nome' => 'required|string|max:120',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'telefone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:120',
            'icone' => 'nullable|string|max:80',
            'cor' => 'required|in:orange,blue,emerald,violet',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'boolean',
            'remover_foto' => 'boolean',
        ]);

        $validated['icone'] = $validated['icone'] ?? 'fa-solid fa-user';
        $validated['ordem'] = (int) ($validated['ordem'] ?? 0);
        $validated['ativo'] = $request->boolean('ativo');
        unset($validated['foto'], $validated['remover_foto']);

        if ($request->boolean('remover_foto') && $contato->foto_url) {
            Storage::disk('public')->delete($contato->foto_url);
            $validated['foto_url'] = null;
        } elseif ($request->hasFile('foto')) {
            if ($contato->foto_url) {
                Storage::disk('public')->delete($contato->foto_url);
            }
            $validated['foto_url'] = $request->file('foto')->store('contatos', 'public');
        }

        $contato->update($validated);
        return redirect()->route('admin.contatos.index')->with('sucesso', 'Contato atualizado com sucesso.');
    }

    public function destroy(Contato $contato): RedirectResponse
    {
        if ($contato->foto_url) {
            Storage::disk('public')->delete($contato->foto_url);
        }
        $contato->delete();
        return redirect()->route('admin.contatos.index')->with('sucesso', 'Contato excluído com sucesso.');
    }
}
