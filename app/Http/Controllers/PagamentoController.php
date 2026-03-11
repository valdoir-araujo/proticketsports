<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Cupom;
use App\Models\Inscricao;
use App\Models\PedidoLoja;
use App\Models\ProdutoOpcional;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PagamentoController extends Controller
{
    /**
     * Retorna o usuário que pode pagar a inscrição: logado OU identificado pela sessão da inscrição.
     */
    private function getPagamentoUser(): ?User
    {
        if (Auth::check()) {
            return Auth::user();
        }
        if (session()->has('inscricao_user_id')) {
            return User::find(session('inscricao_user_id'));
        }
        return null;
    }

    /**
     * Mostra a página de checkout com os componentes de pagamento (Bricks).
     */
    public function show(Inscricao $inscricao, PaymentGatewayInterface $paymentGateway): View|RedirectResponse
    {
        // 1. Validação de Segurança e Status
        $user = $this->getPagamentoUser();
        if (!$user) {
            abort(403, 'Acesso não autorizado.');
        }
        $donoAtleta = $inscricao->atleta->user_id === $user->id;
        $grupoDoUsuario = $inscricao->codigo_grupo && Inscricao::where('codigo_grupo', $inscricao->codigo_grupo)
            ->whereHas('atleta', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
        $podePagarGrupo = $inscricao->codigo_grupo && $grupoDoUsuario;
        if (!$donoAtleta && !$grupoDoUsuario && !$podePagarGrupo) {
            abort(403, 'Acesso não autorizado.');
        }

        if ($inscricao->status === 'confirmada') {
            return redirect()->route('pagamento.sucesso', $inscricao);
        }

        // Inscrições em grupo: nunca auto-confirmar por valor zerado (só a "cabeça" do grupo tem valor; as outras têm 0)
        if ($inscricao->codigo_grupo) {
            // Sempre exibir checkout; o valor a pagar está nesta inscrição ou no grupo
            if ((float) $inscricao->valor_pago <= 0) {
                return redirect()->route('inscricao.show', $inscricao)
                    ->withErrors(['msg' => 'Link de pagamento inválido. Use o link da inscrição que contém o valor a pagar.']);
            }
        }
        
        // 2. Lógica para Inscrição Gratuita (Valor zerado ou Cupom 100%) — apenas inscrições individuais
        if (!$inscricao->codigo_grupo && $inscricao->valor_pago <= 0) {
            try {
                DB::transaction(function () use ($inscricao) {
                    // A. Baixa no Estoque
                    foreach ($inscricao->produtosOpcionais as $produto) {
                        $quantidadeComprada = $produto->pivot->quantidade;
                        if ($quantidadeComprada > 0) {
                            $produtoOpcional = ProdutoOpcional::lockForUpdate()->find($produto->id);
                            
                            // Verifica se tem limite configurado
                            if (!is_null($produtoOpcional->limite_estoque)) {
                                if ($produtoOpcional->limite_estoque < $quantidadeComprada) {
                                    throw new \Exception("Estoque esgotado para o produto: {$produtoOpcional->nome}");
                                }
                                $produtoOpcional->decrement('limite_estoque', $quantidadeComprada);
                            }
                        }
                    }

                    // B. Confirma Inscrição
                    $inscricao->update([
                        'status' => 'confirmada',
                        'data_pagamento' => now(),
                        'metodo_pagamento' => 'cortesia_ou_100_off',
                        'transacao_id_gateway' => 'FREE-' . $inscricao->id . '-' . time()
                    ]);
                    if ($inscricao->cupom_id) {
                        Cupom::where('id', $inscricao->cupom_id)->increment('usos');
                    }
                });

                return redirect()->route('pagamento.sucesso', $inscricao)
                    ->with('sucesso', 'Inscrição gratuita confirmada com sucesso!');

            } catch (\Exception $e) {
                Log::error("Erro ao processar gratuidade: " . $e->getMessage());
                return redirect()->route('inscricao.show', $inscricao)
                    ->withErrors(['msg' => 'Erro ao processar gratuidade: ' . $e->getMessage()]);
            }
        }

        $evento = $inscricao->evento;
        $pagamentoManual = $evento->pagamento_manual ?? false;

        // 3. Se pagamento manual: exibe chave PIX, QR Code e opção de anexar comprovante (não usa Mercado Pago)
        if ($pagamentoManual) {
            return view('pagamento.show', [
                'inscricao' => $inscricao,
                'evento' => $evento,
                'pagamentoManual' => true,
                'publicKey' => null,
            ]);
        }

        // 4. Pagamento via Mercado Pago: verifica se o gateway está configurado
        $publicKey = config('services.mercadopago.public_key') ?: env('MERCADOPAGO_PUBLIC_KEY');
        $accessToken = config('services.mercadopago.access_token') ?: config('services.mercadopago.token')
            ?: env('MERCADOPAGO_ACCESS_TOKEN') ?: env('MERCADOPAGO_TOKEN');
        if (empty($publicKey) || empty($accessToken)) {
            Log::warning("Checkout inscrição #{$inscricao->id}: Mercado Pago não configurado (public_key ou access_token ausente).");
            return redirect()->route('inscricao.show', $inscricao)
                ->withErrors(['msg' => 'Pagamento temporariamente indisponível. Entre em contato com o organizador do evento.']);
        }

        return view('pagamento.show', [
            'inscricao' => $inscricao,
            'evento' => $evento,
            'pagamentoManual' => false,
            'publicKey' => $publicKey,
        ]);
    }

    /**
     * Processa o pagamento recebido do front-end via AJAX (Brick).
     */
    public function process(Request $request, Inscricao $inscricao, PaymentGatewayInterface $paymentGateway): JsonResponse
    {
        // 1. Validações Iniciais (dono da inscrição ou quem paga o grupo)
        $user = $this->getPagamentoUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado.'], 403);
        }
        $donoAtleta = $inscricao->atleta->user_id === $user->id;
        $podePagarGrupo = $inscricao->codigo_grupo && Inscricao::where('codigo_grupo', $inscricao->codigo_grupo)
            ->whereHas('atleta', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
        if (!$donoAtleta && !$podePagarGrupo) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado.'], 403);
        }

        if ($inscricao->status === 'confirmada') {
            return response()->json(['status' => 'success', 'redirect_url' => route('pagamento.sucesso', $inscricao)]);
        }

        // 2. PIX: criar pagamento no backend e retornar QR (mesma regra da loja)
        $paymentMethodId = $request->input('payment_method_id') ?? $request->input('paymentMethodId');
        if (strtolower((string) $paymentMethodId) === 'pix') {
            try {
                $resultado = $paymentGateway->createPixPayment($inscricao);
                if ($resultado['status'] === 'approved') {
                    return response()->json([
                        'status' => 'success',
                        'payment_id' => $resultado['payment_id'] ?? null,
                        'redirect_url' => route('pagamento.sucesso', $inscricao),
                    ]);
                }
                return response()->json([
                    'status' => $resultado['status'],
                    'payment_id' => $resultado['payment_id'] ?? null,
                    'qr_code' => $resultado['qr_code'] ?? '',
                    'qr_code_base64' => $resultado['qr_code_base64'] ?? '',
                ]);
            } catch (\Exception $e) {
                Log::error('Erro PIX Inscrição: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
        }

        // 3. Cartão: processamento via Brick (validar como na loja)
        $token = $request->input('token');
        if (empty($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados do cartão incompletos. Preencha e tente novamente.',
            ], 400);
        }

        try {
            $resultado = $paymentGateway->processPayment($request->all(), $inscricao);

            if ($resultado['status'] === 'success') {
                
                // Se aprovado imediatamente, já damos baixa no estoque
                if (isset($resultado['mp_status']) && $resultado['mp_status'] === 'approved') {
                    $inscricoesParaEstoque = [$inscricao];
                    if ($inscricao->codigo_grupo_parcial) {
                        $inscricoesParaEstoque = Inscricao::with('produtosOpcionais')
                            ->where('codigo_grupo_parcial', $inscricao->codigo_grupo_parcial)
                            ->get()
                            ->all();
                    } elseif ($inscricao->codigo_grupo && $inscricao->tipo_pagamento_grupo === 'unico') {
                        $inscricoesParaEstoque = Inscricao::with('produtosOpcionais')
                            ->where('codigo_grupo', $inscricao->codigo_grupo)
                            ->get()
                            ->all();
                    }
                    try {
                        DB::transaction(function () use ($inscricoesParaEstoque) {
                            foreach ($inscricoesParaEstoque as $insc) {
                                $insc->load('produtosOpcionais');
                                foreach ($insc->produtosOpcionais as $produto) {
                                    $qtd = $produto->pivot->quantidade;
                                    if ($qtd > 0) {
                                        $prodDb = ProdutoOpcional::lockForUpdate()->find($produto->id);
                                        if ($prodDb && $prodDb->limite_estoque !== null) {
                                            if ($prodDb->limite_estoque < $qtd) {
                                                throw new \Exception("Estoque insuficiente para o item: {$prodDb->nome} (inscricao #{$insc->id}).");
                                            }
                                            $prodDb->decrement('limite_estoque', $qtd);
                                        }
                                    }
                                }
                            }
                        });
                    } catch (\Exception $e) {
                        Log::error("Erro baixa estoque processamento imediato: " . $e->getMessage());
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'payment_id' => $resultado['payment_id'] ?? null,
                    'redirect_url' => route('pagamento.sucesso', $inscricao)
                ]);
            }

            // Se o gateway recusou (ex: cartão inválido)
            return response()->json([
                'status' => 'error', 
                'message' => $resultado['message'] ?? 'Pagamento não aprovado.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Erro Fatal Pagamento Inscrição: ' . $e->getMessage() . ' - Linha: ' . $e->getLine());
            $message = $e->getMessage() ?: 'Erro ao processar. Tente PIX ou contate o suporte.';
            return response()->json(['status' => 'error', 'message' => $message], 400);
        }
    }

    /**
     * Recebe e processa notificações de webhook do gateway de pagamento.
     * A assinatura (x-signature) é verificada antes de processar.
     */
    public function webhook(Request $request, PaymentGatewayInterface $paymentGateway)
    {
        if (!$paymentGateway->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'Assinatura inválida'], 401);
        }

        try {
            $data = $paymentGateway->handleWebhook($request);

            if ($data && $data['status'] === 'approved') {
                // Pedido da loja (PIX/cartão): mesma regra que inscrição — confirmar via webhook
                if (!empty($data['pedido_loja_id'])) {
                    $pedido = PedidoLoja::find($data['pedido_loja_id']);
                    if ($pedido && $pedido->status !== 'pago') {
                        $pedido->update([
                            'status' => 'pago',
                            'gateway_payment_id' => (string) ($data['payment_id'] ?? ''),
                        ]);
                        if ($pedido->cupom_id) {
                            Cupom::where('id', $pedido->cupom_id)->increment('usos');
                        }
                        Log::info("Pedido Loja #{$pedido->id} confirmado via webhook.");
                    }
                }

                // Inscrição (external_reference = id da inscrição)
                $inscricao = $data['inscricao_id'] ? Inscricao::with('produtosOpcionais')->find($data['inscricao_id']) : null;

                // Só processa se ainda estiver aguardando (evita duplicidade)
                if ($inscricao && $inscricao->status !== 'confirmada') {
                    $inscricoesConfirmar = [$inscricao];
                    if ($inscricao->codigo_grupo_parcial) {
                        $inscricoesConfirmar = Inscricao::with('produtosOpcionais')
                            ->where('codigo_grupo_parcial', $inscricao->codigo_grupo_parcial)
                            ->whereIn('status', ['aguardando_pagamento', 'pendente'])
                            ->get()
                            ->all();
                    } elseif ($inscricao->codigo_grupo && $inscricao->tipo_pagamento_grupo === 'unico') {
                        $inscricoesConfirmar = Inscricao::with('produtosOpcionais')
                            ->where('codigo_grupo', $inscricao->codigo_grupo)
                            ->whereIn('status', ['aguardando_pagamento', 'pendente'])
                            ->get()
                            ->all();
                    }
                    DB::transaction(function () use ($inscricoesConfirmar, $data) {
                        $cupomIdsIncrementados = [];
                        foreach ($inscricoesConfirmar as $insc) {
                            foreach ($insc->produtosOpcionais as $produto) {
                                $qtd = $produto->pivot->quantidade;
                                if ($qtd > 0) {
                                    $prodDb = ProdutoOpcional::lockForUpdate()->find($produto->id);
                                    if ($prodDb && $prodDb->limite_estoque !== null) {
                                        if ($prodDb->limite_estoque < $qtd) {
                                            throw new \Exception("Estoque insuficiente para o item: {$prodDb->nome} (inscricao #{$insc->id}).");
                                        }
                                        $prodDb->decrement('limite_estoque', $qtd);
                                    }
                                }
                            }
                            $insc->update([
                                'status' => 'confirmada',
                                'data_pagamento' => now(),
                                'metodo_pagamento' => $data['payment_method'] ?? 'Mercado Pago',
                                'transacao_id_gateway' => (string) ($data['payment_id'] ?? ''),
                            ]);
                            if ($insc->cupom_id && !in_array($insc->cupom_id, $cupomIdsIncrementados, true)) {
                                Cupom::where('id', $insc->cupom_id)->increment('usos');
                                $cupomIdsIncrementados[] = $insc->cupom_id;
                            }
                        }
                    });
                }
            }

            return response()->json(['status' => 'recebido']);

        } catch (\Exception $e) {
            Log::error("Erro Webhook: " . $e->getMessage());
            return response()->json(['status' => 'erro'], 500);
        }
    }

    /**
     * Recebe o comprovante de pagamento (pagamento manual). O organizador confirma manualmente depois.
     */
    public function storeComprovante(Request $request, Inscricao $inscricao): RedirectResponse
    {
        $user = $this->getPagamentoUser();
        if (!$user) {
            abort(403, 'Acesso não autorizado.');
        }
        $donoAtleta = $inscricao->atleta->user_id === $user->id;
        $grupoDoUsuario = $inscricao->codigo_grupo && Inscricao::where('codigo_grupo', $inscricao->codigo_grupo)
            ->whereHas('atleta', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
        if (!$donoAtleta && !$grupoDoUsuario) {
            abort(403, 'Acesso não autorizado.');
        }

        $evento = $inscricao->evento;
        if (!($evento->pagamento_manual ?? false)) {
            return redirect()->route('pagamento.show', $inscricao)
                ->withErrors(['comprovante' => 'Este evento não utiliza pagamento manual.']);
        }

        $request->validate([
            'comprovante' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ], [
            'comprovante.required' => 'Selecione o comprovante de pagamento.',
            'comprovante.mimes' => 'Envie uma imagem (JPEG, PNG) ou PDF.',
            'comprovante.max' => 'O arquivo deve ter no máximo 5 MB.',
        ]);

        if ($inscricao->comprovante_pagamento_url) {
            Storage::disk('public')->delete($inscricao->comprovante_pagamento_url);
        }

        $path = $request->file('comprovante')->store('inscricoes/comprovantes', 'public');
        $inscricao->update(['comprovante_pagamento_url' => $path]);

        return redirect()->route('pagamento.show', $inscricao)
            ->with('sucesso', 'Comprovante enviado com sucesso! O organizador irá conferir e confirmar seu pagamento.');
    }

    /**
     * Mostra a página de sucesso apenas quando a inscrição está realmente confirmada (webhook MP).
     * Se o atleta acessar sem ter o pagamento confirmado, redireciona para a inscrição.
     */
    public function sucesso(Inscricao $inscricao): View|RedirectResponse
    {
        $user = $this->getPagamentoUser();
        if (!$user || $inscricao->atleta->user_id !== $user->id) {
            abort(403);
        }
        if ($inscricao->status !== 'confirmada') {
            return redirect()->route('inscricao.show', $inscricao)
                ->with('info', 'Aguardando confirmação do pagamento pelo Mercado Pago. Acompanhe o status nesta página ou em Minhas Inscrições.');
        }
        return view('pagamento.sucesso', compact('inscricao'));
    }

    /**
     * Mostra a página de falha após um pagamento.
     */
    public function falha(Inscricao $inscricao): View
    {
        $user = $this->getPagamentoUser();
        if (!$user || $inscricao->atleta->user_id !== $user->id) {
            abort(403);
        }
        return view('pagamento.falha', compact('inscricao'));
    }
}