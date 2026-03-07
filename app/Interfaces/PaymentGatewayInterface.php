<?php

namespace App\Interfaces;

use App\Models\Inscricao;
use Illuminate\Http\Request; // 🟢 Importação necessária

interface PaymentGatewayInterface
{
    /**
     * Cria uma Preferência de Pagamento e retorna seu ID.
     * Usado para inicializar os Bricks na página de checkout.
     */
    public function createPreference(Inscricao $inscricao): string;

    /**
     * Processa os dados de pagamento recebidos do Brick.
     * Usado para finalizar a transação via AJAX.
     */
    public function processPayment(array $data, Inscricao $inscricao): array;

    /**
     * Verifica se o webhook foi enviado pelo gateway (ex.: assinatura x-signature do Mercado Pago).
     * Retorna true se válido ou se verificação não se aplicar; false se inválido.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Processa os webhooks recebidos do gateway.
     */
    public function handleWebhook(Request $request);
}