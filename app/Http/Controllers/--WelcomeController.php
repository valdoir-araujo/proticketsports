<?php

namespace App\Http\Controllers;

use App\Models\Banner; // Presumo que você tenha um modelo Banner
use App\Models\Evento;
use App\Models\Modalidade; // 👈 =========== ADICIONE ESTA LINHA ===========
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    /**
     * Exibe a página inicial do site.
     */
    public function index(): View
    {
        // Você já deve ter algo assim para os banners
        $banners = Banner::where('ativo', true)->orderBy('ordem', 'asc')->get();

        // Você já deve ter algo assim para os eventos
        $eventosDestaque = Evento::where('status', 'publicado')
                            ->where('data_evento', '>=', now())
                            ->orderBy('data_evento', 'asc')
                            ->take(4) // Pega apenas 4 para a home
                            ->get();

        // 👇 ==========================================================
        // 👇 ADICIONE ESTA LINHA PARA BUSCAR AS MODALIDADES
        // 👇 ==========================================================
        $modalidades = Modalidade::orderBy('nome')->get();


        // 👇 ==========================================================
        // 👇 ADICIONE 'modalidades' AO COMPACT
        // 👇 ==========================================================
        return view('welcome', compact(
            'banners', 
            'eventosDestaque', 
            'modalidades' // 👈 Adicione a variável aqui
        ));
    }
}