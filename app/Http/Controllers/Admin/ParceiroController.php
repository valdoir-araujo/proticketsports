<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parceiro;
use App\Http\Requests\StoreParceiroRequest;
use App\Http\Requests\UpdateParceiroRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class ParceiroController extends Controller
{
    public function index(): View
    {
        $parceiros = Parceiro::orderBy('ordem')->orderBy('nome')->get();
        return view('admin.parceiros.index', compact('parceiros'));
    }

    public function create(): View
    {
        return view('admin.parceiros.create');
    }

    public function store(StoreParceiroRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo']);
        $data['ordem'] = (int) ($data['ordem'] ?? 0);
        $data['ativo'] = (bool) $request->boolean('ativo');
        if (!empty($data['descricao'])) {
            $data['descricao'] = Purifier::clean($data['descricao']);
        }

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $request->file('logo')->store('parceiros', 'public');
        }

        Parceiro::create($data);
        return redirect()->route('admin.parceiros.index')->with('sucesso', 'Parceiro cadastrado com sucesso.');
    }

    public function edit(Parceiro $parceiro): View
    {
        return view('admin.parceiros.edit', compact('parceiro'));
    }

    public function update(UpdateParceiroRequest $request, Parceiro $parceiro): RedirectResponse
    {
        $data = $request->safe()->except(['logo']);
        $data['ordem'] = (int) ($data['ordem'] ?? 0);
        $data['ativo'] = (bool) $request->boolean('ativo');
        if (array_key_exists('descricao', $data) && $data['descricao'] !== null) {
            $data['descricao'] = Purifier::clean($data['descricao']);
        }

        if ($request->hasFile('logo')) {
            if ($parceiro->logo_url) {
                Storage::disk('public')->delete($parceiro->logo_url);
            }
            $data['logo_url'] = $request->file('logo')->store('parceiros', 'public');
        }

        $parceiro->update($data);
        return redirect()->route('admin.parceiros.index')->with('sucesso', 'Parceiro atualizado com sucesso.');
    }

    public function destroy(Parceiro $parceiro): RedirectResponse
    {
        if ($parceiro->logo_url) {
            Storage::disk('public')->delete($parceiro->logo_url);
        }
        $parceiro->delete();
        return redirect()->route('admin.parceiros.index')->with('sucesso', 'Parceiro excluído com sucesso.');
    }
}
