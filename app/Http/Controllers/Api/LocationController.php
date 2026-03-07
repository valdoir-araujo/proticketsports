<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use App\Models\Pais;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Retorna uma lista de estados (atualmente, apenas do Brasil).
     */
    public function getEstados(): JsonResponse
    {
        // No futuro, pode receber um $paisId
        $brasil = Pais::where('codigo_iso', 'BR')->first();
        $estados = $brasil ? $brasil->estados()->orderBy('nome')->get() : [];

        return response()->json($estados);
    }

    /**
     * Retorna uma lista de cidades para um estado específico.
     */
    public function getCidades(Estado $estado): JsonResponse
    {
        $cidades = $estado->cidades()->orderBy('nome')->get();
        return response()->json($cidades);
    }
}
