<?php

namespace App\Http\Controllers;

use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class WebhookController extends Controller
{
    /**
     * Processa as notificações de webhook do Mercado Pago.
     */
    public function handle(Request $request): Response
    {
        // Log para depuração: guarda a notificação recebida
        Log::info('Webhook do Mercado Pago recebido:', $request->all());

        // A notificação pode ser sobre diferentes tópicos, estamos interessados em 'payment'
        if ($request->input('type') === 'payment') {
            $paymentId = $request->input('data.id');

            try {
                // Configura o SDK com o seu Access Token
                MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));
                
                // Busca os detalhes completos do pagamento na API do Mercado Pago
                $client = new PaymentClient();
                $payment = $client->get($paymentId);

                // Verifica se o pagamento foi aprovado
                if ($payment->status === 'approved') {
                    // Pega o ID da nossa inscrição que guardámos na 'external_reference'
                    $inscricaoId = $payment->external_reference;
                    
                    // Encontra a inscrição na nossa base de dados
                    $inscricao = Inscricao::find($inscricaoId);

                    // Atualiza o status e a data de pagamento
                    if ($inscricao && $inscricao->status === 'aguardando_pagamento') {
                        $inscricao->status = 'confirmada';
                        $inscricao->data_pagamento = now();
                        $inscricao->transacao_id_gateway = $paymentId;
                        $inscricao->save();

                        Log::info("Inscrição #{$inscricaoId} confirmada com sucesso.");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Erro ao processar webhook do Mercado Pago: " . $e->getMessage());
                // Retorna um erro 500 para que o Mercado Pago tente novamente mais tarde
                return response('Erro ao processar.', 500);
            }
        }

        // Retorna uma resposta 200 OK para o Mercado Pago saber que recebemos a notificação
        return response('Notificação recebida.', 200);
    }
}
