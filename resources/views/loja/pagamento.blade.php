@extends('layouts.public')

@section('title', 'Pagamento Seguro - Pedido #' . $pedido->id)

@section('content')
<div class="bg-gray-50 min-h-screen pt-24 pb-20 font-sans">
    
    {{-- Header de Segurança --}}
    <div class="bg-white border-b border-gray-200 shadow-sm fixed top-16 left-0 w-full z-40 h-14 flex items-center">
        <div class="container mx-auto px-4 md:px-8 max-w-6xl flex justify-between items-center">
            <div class="flex items-center gap-2 text-green-600">
                <i class="fa-solid fa-lock text-sm"></i>
                <span class="text-xs font-bold uppercase tracking-wider">Ambiente 100% Seguro</span>
            </div>
            <div class="text-xs text-gray-500 hidden sm:block">
                Pedido <span class="font-mono font-bold text-gray-800">#{{ $pedido->id }}</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 md:px-8 max-w-6xl mt-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            {{-- COLUNA 1: ÁREA DE PAGAMENTO (Esquerda) --}}
            <div class="lg:col-span-2 space-y-6" x-data="paymentData()">
                
                <div>
                    <h1 class="text-2xl font-black text-slate-800">Escolha a forma de pagamento</h1>
                    <p class="text-slate-500 text-sm mt-1">Finalize sua compra com segurança.</p>
                </div>

                {{-- Mensagem de Erro Global --}}
                <div x-show="errorMessage" style="display: none;" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-bold" x-text="errorMessage"></p>
                        </div>
                    </div>
                </div>

                {{-- SELETOR DE ABAS --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Botão PIX --}}
                    <button @click="selectPix()" 
                        :class="method === 'pix' ? 'border-green-500 bg-green-50 text-green-800 ring-1 ring-green-500' : 'border-slate-200 bg-white text-slate-600 hover:border-green-300 hover:bg-slate-50'"
                        class="relative py-4 px-4 rounded-xl border-2 font-bold transition-all flex flex-col items-center justify-center gap-2 shadow-sm h-24">
                        <i class="fa-brands fa-pix text-2xl"></i>
                        <span>PIX</span>
                        <div x-show="method === 'pix'" class="absolute top-2 right-2 text-green-600">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </button>
                    
                    {{-- Botão Cartão --}}
                    <button @click="selectCard()" 
                        :class="method === 'card' ? 'border-blue-500 bg-blue-50 text-blue-800 ring-1 ring-blue-500' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:bg-slate-50'"
                        class="relative py-4 px-4 rounded-xl border-2 font-bold transition-all flex flex-col items-center justify-center gap-2 shadow-sm h-24">
                        <i class="fa-regular fa-credit-card text-2xl"></i>
                        <span>Cartão de Crédito</span>
                        <div x-show="method === 'card'" class="absolute top-2 right-2 text-blue-600">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </button>
                </div>

                {{-- CONTEÚDO: PIX --}}
                <div x-show="method === 'pix'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8 text-center">
                        
                        {{-- Estado 1: Botão Gerar --}}
                        <div x-show="!qrCodeBase64">
                            <div class="mb-6">
                                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800">Pagamento Instantâneo</h3>
                                <p class="text-slate-500 text-sm mt-2 max-w-sm mx-auto">
                                    Gere o QR Code e pague pelo aplicativo do seu banco. A aprovação é imediata.
                                </p>
                            </div>
                            
                            <button @click="generatePix()" :disabled="loading" class="w-full md:w-auto px-10 py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-200 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 mx-auto disabled:opacity-70 disabled:cursor-not-allowed text-lg">
                                <span x-show="!loading">Gerar QR Code PIX</span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i> Gerando...
                                </span>
                            </button>
                        </div>

                        {{-- Estado 2: Código Gerado --}}
                        <div x-show="qrCodeBase64" style="display: none;">
                            <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg mb-6 inline-flex items-center gap-2 border border-green-200 text-sm font-bold">
                                <i class="fa-regular fa-clock"></i> Aguardando pagamento...
                            </div>
                            
                            <div class="mb-6 flex justify-center">
                                <div class="p-2 bg-white border-2 border-slate-100 rounded-xl shadow-sm">
                                    {{-- Imagem do QR Code Base64 --}}
                                    <img :src="'data:image/png;base64,' + qrCodeBase64" class="w-64 h-64 rounded-lg object-contain">
                                </div>
                            </div>

                            <div class="max-w-md mx-auto mb-6">
                                <label class="text-xs font-bold text-slate-400 uppercase mb-2 block tracking-wide">Pix Copia e Cola</label>
                                <div class="flex shadow-sm">
                                    <input type="text" x-model="qrCode" readonly class="w-full bg-slate-50 border border-slate-200 text-slate-600 text-xs p-3 rounded-l-lg font-mono focus:outline-none focus:ring-0">
                                    <button @click="copyCode()" class="bg-slate-800 text-white px-5 rounded-r-lg font-bold text-xs hover:bg-slate-900 transition-colors flex items-center gap-2">
                                        <i class="fa-regular fa-copy"></i> <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-center gap-4">
                                <a href="{{ route('pedido.sucesso', $pedido->id) }}" class="text-sm text-blue-600 hover:underline">Já realizei o pagamento</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONTEÚDO: CARTÃO --}}
                <div x-show="method === 'card'" x-transition:enter="transition ease-out duration-300" style="display: none;" x-cloak>
                    {{-- Aviso localhost: Brick do MP pode não carregar em HTTP/localhost --}}
                    <div x-show="isLocalhost" class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                        <p class="font-bold flex items-center gap-2"><i class="fa-solid fa-info-circle"></i> Ambiente local (localhost)</p>
                        <p class="mt-1">O formulário de cartão do Mercado Pago pode não carregar em <strong>HTTP/localhost</strong>. Para testar cartão, use um túnel (ngrok, Laragon Share) com HTTPS ou faça o deploy em homologação/produção. Você pode usar <strong>PIX</strong> para testar o fluxo normalmente.</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-6 relative min-h-[320px]">
                        {{-- Loading do Cartão --}}
                        <div id="loading-card" class="absolute inset-0 flex items-center justify-center bg-white z-20 rounded-xl">
                             <div class="flex flex-col items-center">
                                 <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-500 mb-2"></i>
                                 <span class="text-sm text-slate-500">Carregando formulário seguro...</span>
                             </div>
                        </div>
                        
                        {{-- Brick Container: precisa estar visível no DOM quando o Brick é criado --}}
                        <div id="cardPaymentBrick_container" class="min-h-[280px]"></div>
                    </div>
                </div>

                <div class="text-center md:text-left mt-8">
                    <a href="{{ route('loja.index', ['evento_id' => $pedido->evento_id ?? 0]) }}" class="inline-flex items-center text-sm font-bold text-slate-400 hover:text-red-600 transition-colors gap-2 group">
                        <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Cancelar e voltar para a loja
                    </a>
                </div>

            </div>

            {{-- COLUNA 2: RESUMO (Direita Sticky) --}}
            <div class="lg:col-span-1 lg:sticky lg:top-36">
                <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 p-5 border-b border-slate-200">
                        <h3 class="font-bold text-slate-800 text-lg">Resumo do Pedido</h3>
                    </div>
                    
                    {{-- Itens --}}
                    <div class="p-5 space-y-3 border-b border-slate-100">
                        @php 
                            $itens = $pedido->itens ?? \App\Models\ItemPedidoLoja::where('pedido_loja_id', $pedido->id)->get();
                        @endphp
                        @foreach($itens as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600"><span class="font-bold text-slate-800">{{ $item->quantidade }}x</span> {{ $item->produto ? $item->produto->nome : 'Produto' }}</span>
                                <span class="font-medium text-slate-800">R$ {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totais --}}
                    <div class="p-5 space-y-2 bg-slate-50/50">
                         <div class="flex justify-between text-sm text-slate-500">
                            <span>Subtotal</span>
                            <span>R$ {{ number_format($pedido->valor_total - $pedido->taxa_servico, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Taxa</span>
                            <span>R$ {{ number_format($pedido->taxa_servico, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 mt-2 border-t border-slate-200">
                            <span class="font-bold text-slate-800">Total</span>
                            <span class="text-2xl font-black text-green-600">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-slate-100 p-4 text-center border-t border-slate-200">
                         <img src="https://logopng.com.br/logos/mercado-pago-106.png" alt="Mercado Pago" class="h-5 opacity-50 grayscale mx-auto">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SDK e Device ID já carregados no layout (public). Aqui só a lógica do Brick e envio. --}}
<script>
    function paymentData() {
        return {
            method: 'pix', // Inicia com PIX
            loading: false,
            qrCode: '',
            qrCodeBase64: '',
            copied: false,
            cardInitialized: false,
            errorMessage: '',
            mp: null,
            isLocalhost: false,

            init() {
                this.isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
                const publicKey = "{{ $publicKey }}";
                if (publicKey) {
                    this.mp = new MercadoPago(publicKey, { locale: 'pt-BR' });
                } else {
                    this.errorMessage = "Erro de configuração: Chave Pública do Mercado Pago não encontrada.";
                }
            },

            selectPix() {
                this.method = 'pix';
                this.errorMessage = '';
            },

            selectCard() {
                this.method = 'card';
                this.errorMessage = '';
                // Aguarda a aba ficar visível no DOM antes de criar o Brick (evita container com display:none)
                this.$nextTick(() => {
                    setTimeout(() => this.initCard(), 350);
                });
            },

            // --- LÓGICA DO PIX MANUAL (AJAX) ---
            generatePix() {
                this.loading = true;
                this.errorMessage = '';
                
                // Dados para envio
                const payload = {
                    payment_method_id: 'pix',
                    transaction_amount: {{ $pedido->valor_total }},
                    payer: { email: '{{ optional($pedido->user)->email ?? "cliente@email.com" }}' },
                    pedido_id: {{ $pedido->id }},
                    device_id: (typeof MP_DEVICE_SESSION_ID !== 'undefined' ? MP_DEVICE_SESSION_ID : '')
                };

                fetch("{{ route('pagamento.processar') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(payload)
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Erro no servidor: ' + res.status);
                    }
                    return data;
                })
                .then(data => {
                    this.loading = false;
                    
                    // Verifica sucesso, inclusive se já aprovado
                    if (data.status === 'approved') {
                        window.location.href = "{{ route('pedido.sucesso', $pedido->id) }}";
                        return;
                    }

                    if (data.status === 'rejected') {
                         this.errorMessage = 'Pagamento recusado: ' + (data.message || 'Verifique seus dados.');
                         return;
                    }

                    // Sucesso PIX: Exibe o QR Code Base64
                    if(data.qr_code_base64) {
                        this.qrCode = data.qr_code;
                        this.qrCodeBase64 = data.qr_code_base64;
                    } else {
                        // Fallback de erro
                        console.error('Dados MP:', data);
                        this.errorMessage = 'Erro ao gerar PIX: O gateway não retornou a imagem do QR Code.';
                    }
                })
                .catch(err => {
                    this.loading = false;
                    console.error("Erro Fetch PIX:", err);
                    this.errorMessage = 'Falha na comunicação: ' + err.message;
                });
            },

            copyCode() {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(this.qrCode).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
                } else {
                    const textArea = document.createElement("textarea");
                    textArea.value = this.qrCode;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand("Copy");
                    textArea.remove();
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            },

            // --- LÓGICA DO CARTÃO (BRICK) ---
            initCard() {
                if(this.cardInitialized) return;
                if(!this.mp) this.init();
                
                if(!this.mp) return; 

                const bricksBuilder = this.mp.bricks();
                
                const settings = {
                    initialization: {
                        amount: {{ $pedido->valor_total }},
                    },
                    customization: {
                        paymentMethods: {
                            ticket: "nb",
                            bankTransfer: "nb", 
                            creditCard: "all",
                            debitCard: "nb",    
                            maxInstallments: 12
                        },
                        visual: {
                            style: {
                                theme: 'bootstrap',
                                customVariables: {
                                    formBackgroundColor: '#ffffff',
                                    baseColor: '#3b82f6',
                                }
                            }
                        }
                    },
                    callbacks: {
                        onReady: () => {
                            const loading = document.getElementById('loading-card');
                            if(loading) loading.style.display = 'none';
                        },
                        onSubmit: (cardFormData) => {
                            return new Promise((resolve, reject) => {
                                // Garante pedido_id e normaliza para o backend (Brick pode enviar camelCase)
                                const payload = {
                                    ...cardFormData,
                                    pedido_id: {{ $pedido->id }},
                                    device_id: (typeof MP_DEVICE_SESSION_ID !== 'undefined' ? MP_DEVICE_SESSION_ID : '') || cardFormData.deviceId || cardFormData.device_id || ''
                                };
                                if (payload.paymentMethodId && !payload.payment_method_id) {
                                    payload.payment_method_id = payload.paymentMethodId;
                                }
                                if (payload.issuerId !== undefined && payload.issuer_id === undefined) {
                                    payload.issuer_id = payload.issuerId;
                                }

                                fetch("{{ route('pagamento.processar') }}", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Accept": "application/json"
                                    },
                                    body: JSON.stringify(payload)
                                })
                                .then(async res => {
                                    const data = await res.json().catch(() => ({}));
                                    if (!res.ok) {
                                        throw new Error(data.message || 'Erro no pagamento ( ' + res.status + ' )');
                                    }
                                    return data;
                                })
                                .then((data) => {
                                    if (data.status === 'approved') {
                                        resolve();
                                        window.location.href = "{{ route('pedido.sucesso', $pedido->id) }}";
                                    } else if (data.status === 'rejected') {
                                        reject();
                                        this.errorMessage = "Pagamento não aprovado: " + (data.message || "Verifique os dados do cartão.");
                                        window.scrollTo({ top: 0, behavior: 'smooth' });
                                    } else {
                                        reject();
                                        this.errorMessage = data.message || "Pagamento não aprovado. Tente novamente.";
                                        window.scrollTo({ top: 0, behavior: 'smooth' });
                                    }
                                })
                                .catch((err) => {
                                    reject();
                                    console.error("Erro Fetch Card:", err);
                                    this.errorMessage = "Erro ao processar cartão: " + (err.message || "Tente novamente ou use PIX.");
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                });
                            });
                        },
                        onError: (error) => {
                            console.error('Erro no Brick:', error);
                            const loadingEl = document.getElementById('loading-card');
                            if (loadingEl) loadingEl.style.display = 'none';
                            this.errorMessage = "Erro no formulário de cartão. Tente novamente ou use PIX.";
                        },
                    },
                };
                
                bricksBuilder.create('payment', 'cardPaymentBrick_container', settings)
                    .then(() => {
                        this.cardInitialized = true;
                    })
                    .catch(e => {
                        console.error('Falha ao criar Brick', e);
                        const loadingEl = document.getElementById('loading-card');
                        if (loadingEl) loadingEl.style.display = 'none';
                        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                            this.errorMessage = 'Em localhost o formulário de cartão pode não carregar. Use PIX para testar ou acesse via HTTPS.';
                        } else {
                            this.errorMessage = 'Falha ao carregar o sistema de cartão. Tente recarregar a página ou use PIX.';
                        }
                    });
            }
        }
    }
</script>
@endpush