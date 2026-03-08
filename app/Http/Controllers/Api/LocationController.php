<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Pais;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    private const CACHE_ESTADOS_TTL = 3600;   // 1 hora – lista raramente muda
    private const CACHE_CIDADES_TTL = 3600;   // 1 hora

    /**
     * Retorna uma lista de estados (atualmente, apenas do Brasil).
     * Cache para reduzir consultas em formulários (inscrição, perfil, filtros).
     */
    public function getEstados(): JsonResponse
    {
        $estados = Cache::remember('api_estados_br', self::CACHE_ESTADOS_TTL, function () {
            $brasil = Pais::where('codigo_iso', 'BR')->first();
            return $brasil ? $brasil->estados()->orderBy('nome')->get() : [];
        });

        return response()->json($estados);
    }

    /**
     * Retorna uma lista de cidades para um estado específico.
     */
    public function getCidades(Estado $estado): JsonResponse
    {
        $cidades = Cache::remember('api_cidades_estado_' . $estado->id, self::CACHE_CIDADES_TTL, function () use ($estado) {
            return $estado->cidades()->orderBy('nome')->get();
        });

        return response()->json($cidades);
    }

    /**
     * Busca endereço pelo CEP (ViaCEP) e retorna com estado_id e cidade_id do sistema.
     * Uso: formulários de cadastro/edição com preenchimento automático.
     */
    public function getCep(Request $request): JsonResponse
    {
        $cep = preg_replace('/\D/', '', $request->input('cep', ''));
        if (strlen($cep) !== 8) {
            return response()->json(['erro' => true, 'mensagem' => 'CEP deve ter 8 dígitos.'], 422);
        }

        $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/");
        if (!$response->successful()) {
            return response()->json(['erro' => true, 'mensagem' => 'Não foi possível consultar o CEP.'], 502);
        }

        $data = $response->json();
        if (isset($data['erro']) && $data['erro']) {
            return response()->json(['erro' => true, 'mensagem' => 'CEP não encontrado.'], 404);
        }

        $uf = $data['uf'] ?? '';
        $localidade = $data['localidade'] ?? '';

        $estado = Estado::where('uf', $uf)->first();
        $cidade = null;
        $cidades = [];
        if ($estado) {
            $cidade = Cidade::where('estado_id', $estado->id)
                ->whereRaw('LOWER(TRIM(nome)) = ?', [mb_strtolower(trim($localidade))])
                ->first();
            if (!$cidade) {
                $cidade = Cidade::where('estado_id', $estado->id)
                    ->where('nome', 'like', '%' . $localidade . '%')
                    ->first();
            }
            $cidades = $estado->cidades()->orderBy('nome')->get();
        }

        return response()->json([
            'cep' => $data['cep'] ?? '',
            'logradouro' => $data['logradouro'] ?? '',
            'complemento' => $data['complemento'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'localidade' => $localidade,
            'uf' => $uf,
            'estado_id' => $estado?->id,
            'cidade_id' => $cidade?->id,
            'cidades' => $cidades,
        ]);
    }
}
