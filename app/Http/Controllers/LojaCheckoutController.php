<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\PedidoLoja;
use App\Models\ItemPedidoLoja;
use App\Models\ProdutoOpcional; 
use App\Models\Cupom;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Atleta;
use Carbon\Carbon;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Exceptions\MPApiException;

class LojaCheckoutController extends Controller
{
    public function __construct()
    {
        // 1. CONFIGURAÇÃO DE SEGURANÇA E SSL (CRÍTICO PARA WAMP)
        if (app()->isLocal()) {
            $certPath = 'C:\wamp64\bin\php\php8.2.29\extras\ssl\cacert.pem';
            if (file_exists($certPath)) {
                putenv("CURL_CA_BUNDLE=$certPath");
                putenv("SSL_CERT_FILE=$certPath");
            }
        }

        $token = config('services.mercadopago.access_token');
        if (!empty($token)) {
            MercadoPagoConfig::setAccessToken($token);
        }
    }

    // --- ETAPA 1: TELA DE IDENTIFICAÇÃO ---
    public function identificacao()
    {
        if ($this->getCheckoutUser()) {
            return redirect()->route('loja.checkout');
        }

        $carrinho = session('carrinho', []);
        
        if (empty($carrinho)) {
            $eventoId = session('ultimo_evento_visitado');
            if ($eventoId) {
                return redirect()->route('loja.index', ['evento_id' => $eventoId])->with('error', 'Seu carrinho está vazio.');
            }
            return redirect()->route('carrinho.index'); 
        }

        $primeiroItem = reset($carrinho);
        $produto = ProdutoOpcional::find($primeiroItem['id']);
        
        if (!$produto) return redirect()->route('carrinho.index');
        
        $evento = $produto->evento;
        session(['ultimo_evento_visitado' => $evento->id]);

        return view('loja.identificacao', compact('evento'));
    }

    // --- LÓGICA DE VALIDAÇÃO (SEM SENHA) ---
    public function verificarIdentificacao(Request $request)
    {
        $request->validate([
            'identificacao' => 'required',
            'nascimento'    => 'required|date',
        ]);

        $input = $request->input('identificacao');
        $nascimentoInput = $request->input('nascimento'); 
        
        $user = null;

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $input)->first();
        } else {
            $cpfLimpo = preg_replace('/\D/', '', $input);
            $atleta = Atleta::where('cpf', $cpfLimpo)->first();
            if ($atleta) {
                $user = $atleta->user;
            } else {
                $user = User::query();
                if (\Schema::hasColumn('users', 'cpf')) {
                     $user->where('cpf', $cpfLimpo);
                } elseif (\Schema::hasColumn('users', 'documento')) {
                     $user->where('documento', $cpfLimpo);
                } else {
                    $user = null;
                }
                if ($user instanceof \Illuminate\Database\Eloquent\Builder) {
                    $user = $user->first();
                }
            }
        }

        if ($user) {
            $formatarData = fn($d) => $d ? Carbon::parse($d)->format('Y-m-d') : null;
            $dataInput = $formatarData($nascimentoInput);
            $dataUser = $formatarData($user->data_nascimento); 
            
            if (!$dataUser) {
                $atleta = Atleta::where('user_id', $user->id)->first();
                $dataUser = $atleta ? $formatarData($atleta->data_nascimento) : null;
            }

            if ($dataUser && $dataUser === $dataInput) {
                session(['checkout_user_id' => $user->id]);
                session()->save(); 
                return redirect()->route('loja.checkout');
            } else {
                return back()->withInput()->withErrors(['identificacao' => 'Os dados informados não conferem.']);
            }
        } else {
            return redirect()->route('register')
                ->withInput(['email' => str_contains($input, '@') ? $input : '', 'cpf' => !str_contains($input, '@') ? $input : ''])
                ->with('warning', 'Não encontramos seu cadastro. Crie sua conta para finalizar.');
        }
    }

    // --- ETAPA 2: RESUMO DO PEDIDO (CHECKOUT) ---
    public function index()
    {
        $user = $this->getCheckoutUser();
        if (!$user) return redirect()->route('loja.identificacao');

        $carrinho = session('carrinho', []);
        
        if (empty($carrinho)) {
            $eventoId = session('ultimo_evento_visitado');
            if ($eventoId) {
                return redirect()->route('loja.index', ['evento_id' => $eventoId])->with('error', 'Seu carrinho está vazio.');
            }
            return redirect()->route('carrinho.index');
        }

        $primeiroItem = reset($carrinho);
        $produto = ProdutoOpcional::find($primeiroItem['id']);
        
        if (!$produto) return redirect()->route('carrinho.index');
        
        $evento = $produto->evento;
        session(['ultimo_evento_visitado' => $evento->id]);

        $atleta = Atleta::where('user_id', $user->id)->first();
        $inscricao = null;

        if ($atleta) {
            $inscricao = Inscricao::where('atleta_id', $atleta->id)
                                  ->where('evento_id', $evento->id)
                                  ->where('status', '!=', 'cancelado')
                                  ->latest()
                                  ->first();
        }

        $resumo = $this->calcularTotalCarrinho($carrinho);
        $cupom = null;
        $descontoCupom = 0;
        $cupomCodigo = session('loja_cupom_codigo', '');
        if ($cupomCodigo !== '') {
            $cupom = Cupom::where('evento_id', $evento->id)
                ->where('codigo', $cupomCodigo)
                ->where('ativo', true)
                ->where(function ($q) {
                    $q->whereNull('data_validade')->orWhere('data_validade', '>=', now());
                })
                ->first();
            if ($cupom && ($cupom->limite_uso === null || (int) $cupom->usos < (int) $cupom->limite_uso)) {
                $descontoCupom = $cupom->tipo_desconto === 'percentual'
                    ? $resumo['total'] * ($cupom->valor / 100)
                    : min((float) $cupom->valor, $resumo['total']);
            } else {
                $cupom = null;
                session()->forget('loja_cupom_codigo');
            }
        }
        $totalGeral = max(0, $resumo['total'] - $descontoCupom);

        return view('loja.checkout', array_merge(
            ['user' => $user, 'carrinho' => $carrinho, 'evento' => $evento, 'inscricao' => $inscricao, 'cupom' => $cupom, 'descontoCupom' => $descontoCupom],
            ['subtotal' => $resumo['subtotal'], 'valorTaxa' => $resumo['taxa'], 'totalGeral' => $totalGeral]
        ));
    }

    /** POST: aplicar ou remover cupom no checkout da loja. */
    public function aplicarCupom(Request $request)
    {
        $request->validate(['codigo_cupom' => 'nullable|string|max:50']);
        $codigo = trim($request->input('codigo_cupom', ''));
        if ($codigo === '') {
            session()->forget('loja_cupom_codigo');
            return redirect()->route('loja.checkout')->with('sucesso', 'Cupom removido.');
        }
        session(['loja_cupom_codigo' => $codigo]);
        return redirect()->route('loja.checkout');
    }

    // --- ETAPA 3: SALVAR PEDIDO ---
    public function processar(Request $request)
    {
        $user = $this->getCheckoutUser();
        if (!$user) return redirect()->route('loja.identificacao');

        $carrinho = session('carrinho', []);
        if (empty($carrinho)) return redirect()->route('loja.index');

        $primeiroItem = reset($carrinho);
        $produto = ProdutoOpcional::find(array_key_first($carrinho));
        $evento = $produto ? $produto->evento : null;
        if (!$evento) return redirect()->route('loja.index');

        $resumo = $this->calcularTotalCarrinho($carrinho);
        $cupom = null;
        $descontoCupom = 0;
        $cupomCodigo = session('loja_cupom_codigo', '');
        if ($cupomCodigo !== '') {
            $cupom = Cupom::where('evento_id', $evento->id)
                ->where('codigo', $cupomCodigo)
                ->where('ativo', true)
                ->where(function ($q) {
                    $q->whereNull('data_validade')->orWhere('data_validade', '>=', now());
                })
                ->first();
            if ($cupom && ($cupom->limite_uso === null || (int) $cupom->usos < (int) $cupom->limite_uso)) {
                $descontoCupom = $cupom->tipo_desconto === 'percentual'
                    ? $resumo['total'] * ($cupom->valor / 100)
                    : min((float) $cupom->valor, $resumo['total']);
            } else {
                $cupom = null;
            }
        }
        $totalFinal = max(0, $resumo['total'] - $descontoCupom);

        DB::beginTransaction();
        try {
            $pedido = new PedidoLoja();
            $pedido->user_id = $user->id;
            $pedido->evento_id = $resumo['evento_id'];
            $pedido->valor_total = $totalFinal;
            $pedido->taxa_servico = $resumo['taxa'];
            $pedido->valor_desconto = $descontoCupom;
            $pedido->status = 'pendente';
            if ($cupom) $pedido->cupom_id = $cupom->id;
            if ($request->has('inscricao_id')) {
                 $pedido->inscricao_id = $request->inscricao_id;
            }
            $pedido->save();

            foreach ($carrinho as $id => $item) {
                $produtoDb = ProdutoOpcional::find($id); 
                if ($produtoDb) {
                    ItemPedidoLoja::create([
                        'pedido_loja_id' => $pedido->id,
                        'produto_opcional_id' => $id,
                        'quantidade' => $item['quantidade'],
                        'valor_unitario' => $produtoDb->valor,
                        'tamanho' => null
                    ]);
                }
            }

            DB::commit();
            session()->forget('carrinho');
            session()->forget('loja_cupom_codigo');
            session()->save();
            
            return redirect()->route('loja.pedido.pagamento', ['pedido' => $pedido->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro Pedido: " . $e->getMessage());
            return back()->with('error', 'Erro ao processar pedido.');
        }
    }

    // --- ETAPA 4: EXIBIR TELA DE PAGAMENTO ---
    public function pagamento(Request $request, PedidoLoja $pedido)
    {
        $user = $this->getCheckoutUser();
        $isOwner = $user && $user->id == $pedido->user_id;
        
        if (!$isOwner) {
            return redirect()->route('loja.identificacao')->with('error', 'Sessão expirada.');
        }

        if ($pedido->status === 'pago') {
            return redirect()->route('loja.index', ['evento_id' => $pedido->evento_id])->with('success', 'Pago.');
        }

        $publicKey = config('services.mercadopago.public_key'); 
        $preferenceId = null; 

        return view('loja.pagamento', [
            'pedido' => $pedido,
            'preferenceId' => $preferenceId, 
            'publicKey' => $publicKey
        ]);
    }

    /**
     * Processa pagamento (PIX ou cartão) via Mercado Pago.
     * PIX: funciona em qualquer ambiente. Cartão: Brick carrega apenas em HTTPS (após deploy).
     * Validação pós-upload: testar PIX e cartão em ambiente com HTTPS; conferir .env (MERCADOPAGO_*).
     */
    public function processarPagamento(Request $request)
    {
        // Normaliza campos do Brick (pode vir em camelCase ou snake_case)
        $paymentMethodId = $request->input('payment_method_id') ?? $request->input('paymentMethodId');
        $request->merge(['payment_method_id' => $paymentMethodId]);

        // 1. Validação
        $request->validate([
            'payment_method_id' => 'required',
            'pedido_id' => 'required'
        ]);

        $pedido = PedidoLoja::with('itens.produto')->findOrFail($request->pedido_id);

        // 2. Autorização: apenas o dono do pedido pode processar pagamento (sessão ou auth)
        $user = $this->getCheckoutUser();
        if (!$user || (int) $user->id !== (int) $pedido->user_id) {
            Log::warning('Tentativa de pagamento em pedido alheio.', ['pedido_id' => $pedido->id, 'ip' => $request->ip()]);
            return response()->json(['status' => 'error', 'message' => 'Acesso negado a este pedido.'], 403);
        }

        $isPix = (strtolower((string) $paymentMethodId) === 'pix');

        // Para cartão: token e installments são obrigatórios
        if (!$isPix) {
            $token = $request->input('token');
            if (empty($token)) {
                Log::warning('Pagamento cartão: token não enviado pelo Brick.', ['pedido_id' => $pedido->id]);
                return response()->json(['status' => 'error', 'message' => 'Dados do cartão incompletos. Tente preencher novamente e enviar.'], 400);
            }
        }

        try {
            $client = new PaymentClient();

            // 2. Prepara Payer (Com CPF se disponível, CRÍTICO para PIX e cartão)
            $payerData = [
                "email" => $request->input('payer.email') ?? $user->email,
                "first_name" => $user->name,
            ];

            $docNumber = $user->cpf ?? $user->documento ?? null;
            if (!$docNumber) {
                $atleta = Atleta::where('user_id', $user->id)->first();
                $docNumber = $atleta ? $atleta->cpf : null;
            }

            if ($docNumber) {
                $payerData['identification'] = [
                    'type' => 'CPF',
                    'number' => preg_replace('/\D/', '', (string) $docNumber)
                ];
            }

            // Se o Brick enviou identification (comum em cartão), usa com prioridade
            $payerFromRequest = $request->input('payer');
            if (is_array($payerFromRequest) && !empty($payerFromRequest['identification'])) {
                $payerData['identification'] = $payerFromRequest['identification'];
                if (isset($payerFromRequest['identification']['number'])) {
                    $payerData['identification']['number'] = preg_replace('/\D/', '', (string) $payerFromRequest['identification']['number']);
                }
            }

            // 3. Items para additional_info (qualidade da integração: category_id, description, id, quantity, title, unit_price)
            $itemsLoja = [];
            foreach ($pedido->itens as $item) {
                $produto = $item->produto;
                $nome = $produto ? $produto->nome : 'Item';
                $desc = $produto ? ($produto->descricao ?? $produto->nome) : 'Produto loja';
                $itemsLoja[] = [
                    'id' => 'ped-' . $pedido->id . '-item-' . $item->id,
                    'title' => substr($nome, 0, 200),
                    'description' => substr($desc, 0, 200),
                    'category_id' => 'others',
                    'quantity' => (int) $item->quantidade,
                    'unit_price' => (float) $item->valor_unitario,
                ];
            }
            if (empty($itemsLoja)) {
                $itemsLoja[] = [
                    'id' => 'ped-' . $pedido->id,
                    'title' => 'Pedido #' . $pedido->id,
                    'description' => 'Pedido Loja #' . $pedido->id,
                    'category_id' => 'others',
                    'quantity' => 1,
                    'unit_price' => (float) $pedido->valor_total,
                ];
            }

            $notificationUrl = URL::route('webhook.mercadopago', [], true);
            if (app()->environment('production') && str_starts_with($notificationUrl, 'http://')) {
                $notificationUrl = 'https://' . substr($notificationUrl, 7);
            }

            // 4. Monta Requisição
            $paymentRequest = [
                "transaction_amount" => (float) $pedido->valor_total,
                "description" => "Pedido Loja #" . $pedido->id,
                "payment_method_id" => $paymentMethodId,
                "payer" => $payerData,
                "metadata" => ["pedido_loja_id" => $pedido->id],
                "notification_url" => $notificationUrl,
                "statement_descriptor" => "PROTICKET",
                "additional_info" => [
                    "items" => $itemsLoja,
                ],
            ];

            // Adiciona dados específicos de Cartão (Token, installments, issuer_id)
            if (!$isPix) {
                $paymentRequest["token"] = $request->input('token');
                $installments = max(1, (int) ($request->input('installments') ?? 1));
                $paymentRequest["installments"] = $installments;
                $issuerId = $request->input('issuer_id') ?? $request->input('issuerId');
                if ($issuerId !== null && $issuerId !== '') {
                    $paymentRequest["issuer_id"] = $issuerId;
                }
            }

            // Header de idempotência e Device ID (qualidade da integração)
            $idempotencyKey = 'loja_pedido_' . $pedido->id . '_' . uniqid('', true);
            $requestOptions = new RequestOptions();
            $headers = ['X-Idempotency-Key: ' . $idempotencyKey];
            $deviceId = $request->input('device_id');
            if (!empty($deviceId)) {
                $headers[] = 'X-Meli-Session-Id: ' . $deviceId;
            }
            $requestOptions->setCustomHeaders($headers);

            // 5. Cria o Pagamento na API
            $payment = $client->create($paymentRequest, $requestOptions);

            // 5. Analisa a Resposta
            if ($payment->status === 'approved') {
                $pedido->update(['status' => 'pago', 'gateway_payment_id' => $payment->id]);
                if ($pedido->cupom_id) {
                    Cupom::where('id', $pedido->cupom_id)->increment('usos');
                }
                return response()->json(['status' => 'approved', 'id' => $payment->id]);
            } 
            elseif ($payment->status === 'pending' || $payment->status === 'in_process') {
                $pedido->update(['status' => 'pendente', 'gateway_payment_id' => $payment->id]);
                
                $response = ['status' => $payment->status, 'id' => $payment->id];
                
                // Se for PIX, retorna o QR Code para a View exibir
                if ($isPix) {
                    $response['qr_code'] = $payment->point_of_interaction->transaction_data->qr_code;
                    $response['qr_code_base64'] = $payment->point_of_interaction->transaction_data->qr_code_base64;
                }
                
                return response()->json($response);
            } 
            else {
                // Pagamento Rejeitado
                return response()->json([
                    'status' => 'rejected', 
                    'message' => $payment->status_detail ?? 'Pagamento recusado.'
                ]);
            }

        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $content = $apiResponse ? $apiResponse->getContent() : [];
            $errorMsg = $content['message'] ?? 'Erro desconhecido na API do Mercado Pago.';
            if (!empty($content['cause']) && is_array($content['cause'])) {
                $firstCause = reset($content['cause']);
                if (is_array($firstCause) && isset($firstCause['description'])) {
                    $errorMsg = $firstCause['description'];
                } elseif (is_string($firstCause)) {
                    $errorMsg = $firstCause;
                }
            }
            Log::error('Erro MP API (Pix/Cartão): ', ['msg' => $e->getMessage(), 'content' => $content]);
            return response()->json(['status' => 'error', 'message' => $errorMsg], 400);

        } catch (\Exception $e) {
            Log::error('Erro MP Geral: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function sucesso(PedidoLoja $pedido)
    {
        $user = $this->getCheckoutUser();
        if (!$user || (int) $user->id !== (int) $pedido->user_id) {
            return redirect()->route('loja.identificacao')->with('error', 'Acesso negado. Identifique-se para ver este pedido.');
        }
        return view('loja.sucesso', compact('pedido'));
    }

    public function pendente(PedidoLoja $pedido)
    {
        $user = $this->getCheckoutUser();
        if (!$user || (int) $user->id !== (int) $pedido->user_id) {
            return redirect()->route('loja.identificacao')->with('error', 'Acesso negado. Identifique-se para ver este pedido.');
        }
        return view('loja.pendente', compact('pedido'));
    }

    private function getCheckoutUser()
    {
        if (Auth::check()) return Auth::user();
        if (session()->has('checkout_user_id')) return User::find(session('checkout_user_id'));
        return null;
    }

    private function calcularTotalCarrinho(array $carrinho)
    {
        $subtotal = 0; $eventoId = null; $taxaServico = 0;
        foreach ($carrinho as $id => $item) {
            $produto = ProdutoOpcional::find($id);
            if ($produto) {
                $subtotal += $produto->valor * $item['quantidade'];
                $eventoId = $produto->evento_id;
            }
        }
        if ($eventoId) { $evento = Evento::find($eventoId); if ($evento) $taxaServico = $evento->taxaservico ?? 0; }
        $valorTaxa = $subtotal * ($taxaServico / 100);
        $totalGeral = $subtotal + $valorTaxa;

        return ['subtotal' => $subtotal, 'taxa' => $valorTaxa, 'total' => $totalGeral, 'evento_id' => $eventoId];
    }
}