<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use Illuminate\View\View;

class ContatoController extends Controller
{
    /**
     * Exibe a página de contato com as áreas e responsáveis (cadastrados no admin).
     */
    public function index(): View
    {
        $contatos = Contato::ativos()->orderBy('ordem')->orderBy('area')->get();
        return view('contato.index', compact('contatos'));
    }
}
