<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use GuzzleHttp\Client as GuzzleClient;
use MercadoPago\Net\MPSRequestOptions; // Importante para os Headers

class MercadoPagoService implements PaymentGatewayInterface
{
    private ?string $token = null;

    public function __construct()
    {
        $token = config('services.mercadopago.access_token') ?: config('services.mercadopago.token');

        if (empty($token)) {
            $token = env('MERCADOPAGO_ACCESS_TOKEN') ?? env('MERCADOPAGO_TOKEN');
        }

        if (!empty($token) && is_string($token)) {
            $this->token = $token;
            MercadoPagoConfig::setAccessToken($token);
            if (app()->environment('local')) {
                MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
            }
        } else {
            Log::warning('Token do Mercado Pago não configurado. Configure MERCADOPAGO_ACCESS_TOKEN ou MERCADOPAGO_TOKEN no .env do servidor para habilitar pagamentos.');
        }
    }

    /**
     * Garante que o token está configurado antes de chamar a API. Evita quebrar o site quando o token não está no .env (ex.: hospedagem).
     */
    private function ensureToken(): void
    {
        if (empty($this->token)) {
            Log::critical('Tentativa de uso do Mercado Pago sem token configurado.');
            throw new \Exception('Pagamento não está configurado. Entre em contato com o organizador do evento.');
        }
    }

    /**
     * Cria uma Preferência de Pagamento e retorna seu ID.
     */
    public function createPreference(Inscricao $inscricao): string
    {
        $this->ensureToken();
        try {
            $client = new PreferenceClient();
            $baseUrl = rtrim(config('app.url'), '/');

            if (empty($baseUrl) || str_contains($baseUrl, 'localhost')) {
                $baseUrl = 'http://127.0.0.1:8000';
            }
            
            $urlSucesso = "{$baseUrl}/pagamento/sucesso/{$inscricao->id}";
            $urlFalha   = "{$baseUrl}/pagamento/falha/{$inscricao->id}";

            $preference_data = [
                "items" => [
                    [
                        "id" => (string) $inscricao->id,
                        "title" => "Inscrição: " . substr($inscricao->evento->nome, 0, 200),
                        "quantity" => 1,
                        "unit_price" => (float) number_format($inscricao->valor_pago, 2, '.', ''),
                        "currency_id" => "BRL",
                    ]
                ],
                "payer" => [
                    "name" => substr($inscricao->atleta->user->name, 0, 100),
                    "email" => $inscricao->atleta->user->email,
                ],
                "external_reference" => (string) $inscricao->id, 
                "statement_descriptor" => "PROTICKET", 
                "back_urls" => [
                    "success" => $urlSucesso,
                    "failure" => $urlFalha,
                    "pending" => $urlSucesso,
                ],
                "auto_return" => app()->environment('local') ? null : "approved",
            ];

            $preference = $client->create($preference_data);
            return $preference->id;

        } catch (\Exception $e) {
            Log::error("MercadoPagoService Preference Error: " . $e->getMessage());
            throw new \Exception("Erro ao gerar link de pagamento.");
        }
    }

    /**
     * Cria um pagamento PIX para a inscrição (mesma regra da loja: backend gera e retorna QR).
     */
    public function createPixPayment(Inscricao $inscricao): array
    {
        $this->ensureToken();
        try {
            $client = new PaymentClient();
            $user = $inscricao->atleta->user;
            $payerData = [
                'email' => $user->email,
                'first_name' => substr($user->name, 0, 100),
            ];
            $cpf = preg_replace('/\D/', '', (string) ($inscricao->atleta->cpf ?? ''));
            if ($cpf !== '') {
                $payerData['identification'] = ['type' => 'CPF', 'number' => $cpf];
            }
            $paymentRequest = [
                'transaction_amount' => (float) number_format($inscricao->valor_pago, 2, '.', ''),
                'description' => 'Inscrição #' . $inscricao->id . ' - ' . substr($inscricao->evento->nome, 0, 200),
                'payment_method_id' => 'pix',
                'payer' => $payerData,
                'external_reference' => (string) $inscricao->id,
            ];
            $requestOptions = new MPSRequestOptions();
            $requestOptions->setCustomHeaders(['X-Idempotency-Key: inscricao_pix_' . $inscricao->id . '_' . uniqid('', true)]);
            $payment = $client->create($paymentRequest, $requestOptions);
            if ($payment->status === 'approved') {
                return [
                    'status' => 'approved',
                    'payment_id' => (string) $payment->id,
                ];
            }
            if ($payment->status === 'pending' || $payment->status === 'in_process') {
                $poi = $payment->point_of_interaction ?? null;
                $txData = $poi->transaction_data ?? null;
                return [
                    'status' => 'pending',
                    'payment_id' => (string) $payment->id,
                    'qr_code' => $txData->qr_code ?? '',
                    'qr_code_base64' => $txData->qr_code_base64 ?? '',
                ];
            }
            throw new \Exception($payment->status_detail ?? 'Pagamento não aprovado.');
        } catch (MPApiException $e) {
            $content = $e->getApiResponse()->getContent();
            Log::error('MercadoPagoService PIX Error: ' . json_encode($content));
            throw new \Exception($content['message'] ?? 'Erro ao gerar PIX.');
        } catch (\Exception $e) {
            Log::error('MercadoPagoService PIX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Processa os dados de pagamento recebidos do Brick.
     */
    public function processPayment(array $data, Inscricao $inscricao): array
    {
        $this->ensureToken();
        try {
            $payerDocument = preg_replace('/[^0-9]/', '', $data['payer']['identification']['number'] ?? '');
            $client = new PaymentClient();

            // 1. Montagem dos dados base (valor SEMPRE do servidor - nunca do cliente)
            $payment_request = [
                "transaction_amount" => (float) $inscricao->valor_pago,
                "token" => $data['token'],
                "description" => "Inscrição #{$inscricao->id} - ProTicket",
                "installments" => (int) $data['installments'],
                "payment_method_id" => $data['payment_method_id'],
                "issuer_id" => (int) $data['issuer_id'],
                "external_reference" => (string) $inscricao->id,
                "payer" => [
                    "email" => $data['payer']['email'],
                    "identification" => [
                        "type" => $data['payer']['identification']['type'],
                        "number" => $payerDocument
                    ]
                ],
                // 🛡️ ADICIONADO: Informações adicionais para antifraude
                "additional_info" => [
                    "ip_address" => $data['payer_ip'] ?? request()->ip()
                ]
            ];

            // 🛡️ ADICIONADO: Configuração de Headers (Device ID)
            // Isso resolve a pendência "Identificador do dispositivo" no painel
            $request_options = new MPSRequestOptions();
            $request_options->setCustomHeaders([
                "X-Meli-Session-Id" => $data['device_id'] ?? null
            ]);
            
            // 2. Cria o pagamento enviando os headers customizados
            $payment = $client->create($payment_request, $request_options);

            if ($payment->status === 'approved') {
                $inscricao->update([
                    'status' => 'confirmada',
                    'data_pagamento' => now(),
                    'metodo_pagamento' => $payment->payment_method_id,
                    'transacao_id_gateway' => (string) $payment->id,
                ]);
                return ['status' => 'success', 'mp_status' => $payment->status, 'payment_id' => $payment->id];
            } elseif ($payment->status === 'in_process' || $payment->status === 'pending') {
                return ['status' => 'success', 'mp_status' => $payment->status, 'message' => 'Pagamento em análise.'];
            } else {
                $errorMsg = $payment->status_detail ?? 'Pagamento não aprovado.';
                return ['status' => 'error', 'message' => $this->translateStatusDetail($errorMsg)];
            }

        } catch (MPApiException $e) {
            $content = $e->getApiResponse()->getContent();
            Log::error("Erro MP API: " . json_encode($content));
            return ['status' => 'error', 'message' => 'Erro MP: ' . ($content['message'] ?? 'Cartão recusado.')];
        } catch (\Exception $e) {
            Log::error("Erro Interno Service: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Erro interno ao processar.'];
        }
    }

    /**
     * Verifica se a notificação webhook foi enviada pelo Mercado Pago (header x-signature).
     * Documentação: https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = config('services.mercadopago.webhook_secret');
        if (empty($secret)) {
            if (app()->environment('production')) {
                Log::warning('Webhook MP: MERCADOPAGO_WEBHOOK_SECRET não configurado. Configure em Suas integrações > Webhooks.');
                return false;
            }
            Log::debug('Webhook MP: assinatura não verificada (webhook_secret ausente em ambiente não-produção).');
            return true; // Em local/test permite sem secret para desenvolvimento
        }

        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');
        if (empty($xSignature)) {
            Log::warning('Webhook MP: header x-signature ausente.');
            return false;
        }

        $parts = explode(',', $xSignature);
        $ts = null;
        $hash = null;
        foreach ($parts as $part) {
            $keyValue = explode('=', trim($part), 2);
            if (count($keyValue) === 2) {
                $key = trim($keyValue[0]);
                $value = trim($keyValue[1]);
                if ($key === 'ts') {
                    $ts = $value;
                } elseif ($key === 'v1') {
                    $hash = $value;
                }
            }
        }
        if ($ts === null || $hash === null) {
            Log::warning('Webhook MP: x-signature inválido (ts ou v1 ausente).');
            return false;
        }

        $dataId = $request->query('data.id') ?? $request->input('data.id');
        if ($dataId !== null && is_string($dataId) && preg_match('/^[a-zA-Z0-9]+$/', $dataId)) {
            $dataId = strtolower($dataId);
        }
        $dataId = $dataId ?? '';

        $manifestParts = ["id:$dataId"];
        if ($xRequestId !== null && $xRequestId !== '') {
            $manifestParts[] = "request-id:$xRequestId";
        }
        $manifestParts[] = "ts:$ts";
        $manifest = implode(';', $manifestParts) . ';';

        $calculated = hash_hmac('sha256', $manifest, $secret);
        $valid = hash_equals($calculated, $hash);
        if (!$valid) {
            Log::warning('Webhook MP: assinatura inválida.');
        }
        return $valid;
    }

    public function handleWebhook(Request $request)
    {
        if (empty($this->token)) {
            Log::warning('Webhook MP ignorado: token não configurado.');
            return null;
        }
        $paymentId = $request->query('id') ?? $request->input('data.id');
        if (!$paymentId) return null;

        try {
            $client = new PaymentClient();
            $payment = $client->get($paymentId);
            if (!$payment) return null;

            $pedidoLojaId = null;
            if (!empty($payment->metadata) && is_object($payment->metadata) && isset($payment->metadata->pedido_loja_id)) {
                $pedidoLojaId = $payment->metadata->pedido_loja_id;
            } elseif (!empty($payment->metadata) && is_array($payment->metadata) && isset($payment->metadata['pedido_loja_id'])) {
                $pedidoLojaId = $payment->metadata['pedido_loja_id'];
            }

            return [
                'payment_id' => $payment->id,
                'status'     => $payment->status,
                'amount'     => $payment->transaction_amount,
                'inscricao_id' => $payment->external_reference ?? null,
                'pedido_loja_id' => $pedidoLojaId,
                'payment_method' => $payment->payment_method_id ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("Erro Webhook Service: " . $e->getMessage());
            return null;
        }
    }

    private function translateStatusDetail($statusDetail)
    {
        $messages = [
            'cc_rejected_bad_filled_card_number' => 'Verifique o número do cartão.',
            'cc_rejected_bad_filled_date' => 'Verifique a data de validade.',
            'cc_rejected_bad_filled_security_code' => 'Verifique o código de segurança (CVV).',
            'cc_rejected_other_reason' => 'Cartão recusado pelo banco emissor.',
            'cc_rejected_insufficient_amount' => 'Saldo insuficiente.',
            'cc_rejected_high_risk' => 'Pagamento recusado pelo antifraude do Mercado Pago.',
            'cc_rejected_call_for_authorize' => 'Autorize o pagamento junto ao seu banco.',
        ];
        return $messages[$statusDetail] ?? 'Pagamento recusado. Verifique os dados.';
    }
}