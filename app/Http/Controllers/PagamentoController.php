<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Inscricao;
use App\Models\ProdutoOpcional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagamentoController extends Controller
{
    /**
     * Mostra a página de checkout com os componentes de pagamento (Bricks).
     */
    public function show(Inscricao $inscricao, PaymentGatewayInterface $paymentGateway): View|RedirectResponse
    {
        // 1. Validação de Segurança e Status
        $user = auth()->user();
        $donoAtleta = $user && $inscricao->atleta->user_id === $user->id;
        $grupoDoUsuario = $inscricao->codigo_grupo && Inscricao::where('codigo_grupo', $inscricao->codigo_grupo)
            ->whereHas('atleta', fn ($q) => $q->where('user_id', $user?->id))
            ->exists();
        $podePagarGrupo = $user && $inscricao->codigo_grupo; // Quem criou o grupo (redirecionado) pode pagar
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
                });

                return redirect()->route('pagamento.sucesso', $inscricao)
                    ->with('sucesso', 'Inscrição gratuita confirmada com sucesso!');

            } catch (\Exception $e) {
                Log::error("Erro ao processar gratuidade: " . $e->getMessage());
                return redirect()->route('inscricao.show', $inscricao)
                    ->withErrors(['msg' => 'Erro ao processar gratuidade: ' . $e->getMessage()]);
            }
        }

        // 3. Gera o Checkout no Mercado Pago
        try {
            // Gera a preferência no Mercado Pago
            $preferenceId = $paymentGateway->createPreference($inscricao);
            
            // Pega a chave pública do arquivo de configuração
            $publicKey = config('services.mercadopago.public_key') ?? env('MERCADOPAGO_PUBLIC_KEY');

            if (empty($publicKey)) {
                throw new \Exception('Chave pública do Mercado Pago não configurada.');
            }

            return view('pagamento.show', compact('inscricao', 'publicKey', 'preferenceId'));

        } catch (\Exception $e) {
            Log::error("Erro Checkout MP Inscrição #{$inscricao->id}: " . $e->getMessage());
            
            return redirect()->route('inscricao.show', $inscricao)
                             ->withErrors(['msg' => 'Erro ao carregar sistema de pagamento. Tente novamente.']);
        }
    }

    /**
     * Processa o pagamento recebido do front-end via AJAX (Brick).
     */
    public function process(Request $request, Inscricao $inscricao, PaymentGatewayInterface $paymentGateway): JsonResponse
    {
        // 1. Validações Iniciais (dono da inscrição ou quem paga o grupo)
        $userId = auth()->id();
        $donoAtleta = $inscricao->atleta->user_id === $userId;
        $podePagarGrupo = $userId && $inscricao->codigo_grupo;
        if (!$donoAtleta && !$podePagarGrupo) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado.'], 403);
        }

        if ($inscricao->status === 'confirmada') {
            return response()->json(['status' => 'success', 'redirect_url' => route('pagamento.sucesso', $inscricao)]);
        }

        // 2. Processamento com Tratamento de Erros
        try {
            // Processa no Gateway (Service)
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
                                        if ($prodDb && !is_null($prodDb->limite_estoque)) {
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
            // Erro Fatal (Código quebrado, API fora do ar, etc)
            Log::error("Erro Fatal no Pagamento: " . $e->getMessage() . " - Linha: " . $e->getLine());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro interno ao processar. Por favor, contate o suporte ou tente outra forma de pagamento.'
            ], 500);
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
                $inscricao = Inscricao::with('produtosOpcionais')->find($data['inscricao_id']);

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
                        foreach ($inscricoesConfirmar as $insc) {
                            foreach ($insc->produtosOpcionais as $produto) {
                                $qtd = $produto->pivot->quantidade;
                                if ($qtd > 0) {
                                    $prodDb = ProdutoOpcional::lockForUpdate()->find($produto->id);
                                    if ($prodDb && !is_null($prodDb->limite_estoque)) {
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
     * Mostra a página de sucesso após um pagamento.
     */
    public function sucesso(Inscricao $inscricao): View
    {
        if ($inscricao->atleta->user_id !== auth()->id()) {
            abort(403);
        }
        return view('pagamento.sucesso', compact('inscricao'));
    }

    /**
     * Mostra a página de falha após um pagamento.
     */
    public function falha(Inscricao $inscricao): View
    {
        if ($inscricao->atleta->user_id !== auth()->id()) {
            abort(403);
        }
        return view('pagamento.falha', compact('inscricao'));
    }
}