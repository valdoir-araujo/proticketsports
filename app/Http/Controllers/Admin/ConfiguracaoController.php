<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        // Busca o valor da taxa no banco, se não existir, usa 10 como padrão.
        $taxaPlataforma = Setting::where('key', 'taxa_plataforma')->first()->value ?? '10.0';

        return view('admin.configuracoes.index', compact('taxaPlataforma'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'taxa_plataforma' => 'required|numeric|min:0|max:100',
        ]);

        // Usa updateOrCreate para criar a configuração se ela não existir,
        // ou para atualizá-la se já existir.
        Setting::updateOrCreate(
            ['key' => 'taxa_plataforma'],
            ['value' => $request->taxa_plataforma]
        );

        return back()->with('sucesso', 'Configurações salvas com sucesso!');
    }
}