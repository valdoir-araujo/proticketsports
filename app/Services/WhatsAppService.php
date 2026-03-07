<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $instanceId;
    protected $token;
    protected $clientToken;

    public function __construct()
    {
        // Configuração Z-API
        // Adicione estas linhas ao seu arquivo .env:
        // ZAPI_INSTANCE_ID=SUA_INSTANCIA_AQUI
        // ZAPI_TOKEN=SEU_TOKEN_AQUI
        // ZAPI_CLIENT_TOKEN=SEU_CLIENT_TOKEN_AQUI (Opcional, se ativado na segurança da Z-API)
        
        $this->instanceId = env('ZAPI_INSTANCE_ID'); 
        $this->token = env('ZAPI_TOKEN');
        $this->clientToken = env('ZAPI_CLIENT_TOKEN');
    }

    /**
     * Envia mensagem de texto simples via Z-API
     */
    public function sendText($number, $message)
    {
        if (!$this->instanceId || !$this->token) {
            Log::warning("WhatsAppService: Credenciais Z-API não configuradas no .env");
            return false;
        }

        try {
            // Endpoint padrão da Z-API para envio de texto
            // Documentação: https://developer.z-api.io/message/send-message-text
            $endpoint = "https://api.z-api.io/instances/{$this->instanceId}/token/{$this->token}/send-text";

            $headers = [
                'Content-Type' => 'application/json',
            ];

            // Adiciona o Client-Token se estiver configurado (Segurança extra da Z-API)
            if ($this->clientToken) {
                $headers['Client-Token'] = $this->clientToken;
            }

            $response = Http::withHeaders($headers)->post($endpoint, [
                'phone' => $this->formatNumber($number),
                'message' => $message
            ]);

            if ($response->failed()) {
                Log::error("Erro Z-API: " . $response->body());
                return false;
            }

            return $response->successful();

        } catch (\Exception $e) {
            Log::error("Erro ao enviar WhatsApp (Z-API): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Formata o número para o padrão internacional exigido pela Z-API (55 + DDD + Numero)
     * Exemplo: 5546999999999
     */
    private function formatNumber($number)
    {
        // Remove tudo que não é número (espaços, traços, parênteses)
        $nums = preg_replace('/[^0-9]/', '', $number);
        
        // Verifica se é um número válido (mínimo 10 dígitos: DDD + Número)
        if (strlen($nums) < 10) {
            return $nums; // Retorna como está se for muito curto (provavelmente inválido)
        }

        // Se não começar com 55 (Brasil), adiciona o DDI
        if (substr($nums, 0, 2) !== '55') {
            return '55' . $nums;
        }
        
        return $nums;
    }
}