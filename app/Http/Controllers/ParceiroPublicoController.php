<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use Illuminate\View\View;

class ParceiroPublicoController extends Controller
{
    /**
     * Lista parceiros ativos para a página pública (cards).
     */
    public function index(): View
    {
        $parceiros = Parceiro::ativos()->orderBy('ordem')->orderBy('nome')->get();
        return view('parceiros.index', compact('parceiros'));
    }
}
