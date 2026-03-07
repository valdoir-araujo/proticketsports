<x-app-layout>
    {{-- Styles inline para garantir o carregamento --}}
    <style>
        .has-[:checked] {
            --tw-border-opacity: 1;
            border-color: rgb(249 115 22 / var(--tw-border-opacity));
            background-color: rgb(255 247 237);
            --tw-ring-color: rgb(249 115 22 / var(--tw-border-opacity));
            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
        }
        [x-cloak] { display: none !important; }
        
        /* Garantir altura mínima para os containers de pagamento */
        #cardPaymentBrick_container, #pixPaymentBrick_container {
            min-height: 150px;
        }
    </style>

    {{-- CABEÇALHO HERO --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold text-orange-200 justify-center md:justify-start">
                        <i class="fa-solid fa-lock"></i> Ambiente Seguro
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Finalizar Inscrição
                    </h1>
                    <p class="text-blue-100 mt-2 text-lg font-light opacity-90">
                        Escolha a melhor forma de pagamento para garantir a sua vaga.
                    </p>
                </div>
                
                <div class="z-10">
                    <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white text-sm font-bold transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Cancelar e Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="relative z-20 -mt-20 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alerta de Erros --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-lg animate-fade-in">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i></div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-red-800">Problemas na inscrição:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Estrutura de Colunas --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ tab: 'card', processing: false }">
                
                {{-- COLUNA 1: Resumo do Pedido --}}
                <div class="lg:col-span-1 order-2 lg:order-1">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden sticky top-6">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                    <i class="fa-solid fa-receipt"></i>
                                </span>
                                Resumo do Pedido
                            </h2>
                        </div>
                        
                        <div class="p-6 space-y-5">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Evento</p>
                                <p class="font-bold text-slate-900 text-lg leading-tight">{{ $inscricao->evento->nome }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Atleta</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold">
                                        {{ substr($inscricao->atleta->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $inscricao->atleta->user->name }}</p>
                                        <p class="text-xs text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded inline-block">{{ $inscricao->categoria->nome }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-slate-200 pt-4 space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-600">Inscrição</span>
                                    <span class="font-medium text-slate-900">R$ {{ number_format($inscricao->valor_original, 2, ',', '.') }}</span>
                                </div>

                                @if($inscricao->produtosOpcionais->isNotEmpty())
                                    <div class="py-2">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Itens Adicionais</p>
                                        <ul class="space-y-2">
                                            @foreach($inscricao->produtosOpcionais as $produto)
                                                <li class="flex justify-between items-start text-xs">
                                                    <span class="text-slate-600">
                                                        {{ $produto->pivot->quantidade }}x {{ $produto->nome }}
                                                        @if($produto->pivot->tamanho) <span class="text-slate-400">({{ $produto->pivot->tamanho }})</span> @endif
                                                    </span>
                                                    @if($produto->pivot->valor_pago_por_item > 0)
                                                        <span class="font-medium text-slate-900">R$ {{ number_format($produto->pivot->valor_pago_por_item * $produto->pivot->quantidade, 2, ',', '.') }}</span>
                                                    @else
                                                        <span class="font-bold text-green-600 uppercase text-[10px]">Grátis</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if($inscricao->taxa_plataforma > 0)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-600">Taxa de Serviço</span>
                                        <span class="font-medium text-slate-900">R$ {{ number_format($inscricao->taxa_plataforma, 2, ',', '.') }}</span>
                                    </div>
                                @endif

                                @if($inscricao->cupom_id)
                                    <div class="flex justify-between items-center text-sm text-green-600 bg-green-50 p-2 rounded-lg border border-green-100">
                                        <span class="flex items-center gap-1"><i class="fa-solid fa-tag"></i> Desconto ({{ $inscricao->cupom->codigo }})</span>
                                        <span class="font-bold">- R$ {{ number_format($inscricao->valor_desconto ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-4 border-t-2 border-slate-100 flex justify-between items-center">
                                <span class="text-sm font-bold text-slate-500 uppercase">Total a Pagar</span>
                                <span class="text-3xl font-black text-indigo-700">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50 p-4 text-xs text-slate-500 text-center border-t border-slate-100">
                            <i class="fa-solid fa-shield-halved text-green-500 mr-1"></i> Ambiente 100% Seguro
                        </div>
                    </div>
                </div>

                {{-- COLUNA 2: Área de Pagamento --}}
                <div class="lg:col-span-2 order-1 lg:order-2">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 sm:p-8">
                        
                        @if($preferenceId)
                            {{-- Navegação das Abas --}}
                            <div class="flex p-1 bg-slate-100 rounded-xl mb-8">
                                <button @click="tab = 'card'" 
                                    :class="tab === 'card' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700'"
                                    class="flex-1 py-3 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2">
                                    <i class="fa-regular fa-credit-card text-lg"></i> Cartão de Crédito
                                </button>
                                <button @click="tab = 'pix'" 
                                    :class="tab === 'pix' ? 'bg-white text-green-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700'"
                                    class="flex-1 py-3 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2">
                                    <i class="fa-brands fa-pix text-lg"></i> PIX
                                </button>
                            </div>

                            {{-- Container dos Formulários --}}
                            <div id="payment-form-container" class="relative min-h-[300px]">
                                
                                {{-- Loading Overlay --}}
                                <div x-show="processing" x-transition class="absolute inset-0 bg-white/90 z-50 flex flex-col items-center justify-center rounded-xl backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-5xl text-orange-500 mb-4"></i>
                                    <span class="font-bold text-slate-700 text-lg animate-pulse">Processando pagamento...</span>
                                    <span class="text-sm text-slate-400 mt-2">Por favor, não feche a página.</span>
                                </div>

                                {{-- Aba Cartão --}}
                                <div x-show="tab === 'card'" x-transition:enter="transition ease-out duration-300">
                                    <div id="cardPaymentBrick_container">
                                        <div class="text-center py-4 text-gray-400 text-sm italic">Carregando formulário de cartão...</div>
                                    </div>
                                </div>

                                {{-- Aba Pix --}}
                                <div x-show="tab === 'pix'" x-cloak x-transition:enter="transition ease-out duration-300">
                                    <div class="text-center mb-8 p-6 bg-green-50 rounded-xl border border-green-100">
                                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl shadow-sm">
                                            <i class="fa-solid fa-bolt"></i>
                                        </div>
                                        <h3 class="font-bold text-green-800 text-lg mb-1">Pagamento Instantâneo</h3>
                                        <p class="text-sm text-green-700 max-w-sm mx-auto">Seu pagamento é aprovado na hora. Basta apontar a câmera ou copiar o código.</p>
                                    </div>
                                    <div id="pixPaymentBrick_container">
                                        <div class="text-center py-4 text-gray-400 text-sm italic">Gerando código PIX...</div>
                                    </div>
                                </div>

                            </div>
                        @else
                            {{-- Erro ao carregar preferência --}}
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="bg-red-50 p-6 rounded-full mb-4 shadow-sm">
                                    <i class="fa-solid fa-circle-exclamation text-4xl text-red-500"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800">Erro ao carregar pagamento</h3>
                                <p class="text-slate-500 mt-2 max-w-md">Não conseguimos conectar com o provedor de pagamento. Verifique sua conexão e tente novamente.</p>
                                <button onclick="window.location.reload()" class="mt-8 px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                                    <i class="fa-solid fa-rotate-right mr-2"></i> Tentar Novamente
                                </button>
                            </div>
                        @endif

                        {{-- Tela de Status (Sucesso/Erro) --}}
                        <div id="statusScreenBrick_container" class="mt-6"></div>
                        
                        {{-- Msg de Erro Genérica --}}
                        <div id="paymentError" class="hidden mt-4 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 text-center text-sm font-bold flex items-center justify-center gap-2"></div>

                    </div>
                    
                    {{-- Selos de Segurança --}}
                    <div class="mt-8 flex justify-center gap-6 grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-500">
                        <i class="fa-brands fa-cc-visa text-4xl text-blue-900"></i>
                        <i class="fa-brands fa-cc-mastercard text-4xl text-red-600"></i>
                        <i class="fa-brands fa-cc-amex text-4xl text-blue-500"></i>
                        <div class="flex items-center gap-1 text-slate-600 font-bold text-lg"><i class="fa-solid fa-shield-halved"></i> SSL</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS (Movidos para o corpo principal para garantir execução) --}}
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Iniciando Mercado Pago...');
            
            const publicKey = @json($publicKey ?? '');
            const preferenceId = @json($preferenceId ?? '');

            if (publicKey && preferenceId) {
                const mp = new MercadoPago(publicKey);
                const bricksBuilder = mp.bricks();

                // Helper de Erro
                const showError = (msg) => {
                    const el = document.getElementById('paymentError');
                    el.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
                    el.classList.remove('hidden');
                    setTimeout(() => el.classList.add('hidden'), 8000);
                };

                const renderStatusScreenBrick = async (paymentId) => {
                    document.getElementById('payment-form-container').style.display = 'none';
                    const settings = {
                        initialization: { paymentId: paymentId },
                        callbacks: {
                            onReady: () => {
                                document.getElementById('statusScreenBrick_container').scrollIntoView({ behavior: 'smooth' });
                            },
                            onError: (error) => console.error(error),
                        },
                    };
                    window.statusScreenBrickController = await bricksBuilder.create('statusScreen', 'statusScreenBrick_container', settings);
                };

                // --- CARTÃO ---
                bricksBuilder.create("cardPayment", "cardPaymentBrick_container", {
                    initialization: {
                        amount: @json((float) $inscricao->valor_pago),
                        preferenceId: preferenceId,
                    },
                    customization: { 
                        visual: { 
                            style: { theme: 'bootstrap' }, 
                            hideFormTitle: true 
                        },
                        paymentMethods: { maxInstallments: 6 } 
                    },
                    callbacks: {
                        onReady: () => {
                            console.log('Brick Cartão Pronto');
                        },
                        onSubmit: (cardFormData) => {
                            const alpineComponent = document.querySelector('[x-data]');
                            if(alpineComponent) alpineComponent.__x.$data.processing = true;
                            document.getElementById('paymentError').classList.add('hidden');

                            return new Promise((resolve, reject) => {
                                fetch(@json(route('pagamento.process', $inscricao)), {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": @json(csrf_token())
                                    },
                                    body: JSON.stringify(cardFormData),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if(alpineComponent) alpineComponent.__x.$data.processing = false;
                                    if (data.payment_id) {
                                        renderStatusScreenBrick(data.payment_id);
                                        resolve();
                                    } else {
                                        showError(data.message || 'Pagamento recusado.');
                                        reject();
                                    }
                                })
                                .catch((err) => {
                                    if(alpineComponent) alpineComponent.__x.$data.processing = false;
                                    showError('Erro de conexão.');
                                    reject();
                                });
                            });
                        },
                        onError: (error) => { console.error('Erro Cartão:', error); },
                    },
                });

                // --- PIX ---
                bricksBuilder.create("pix", "pixPaymentBrick_container", {
                    initialization: {
                        amount: @json((float) $inscricao->valor_pago),
                        preferenceId: preferenceId,
                    },
                    customization: {
                        visual: { 
                            hideFormTitle: true,
                            style: { theme: 'bootstrap' }
                        }
                    },
                    callbacks: {
                        onReady: () => {
                             console.log('Brick PIX Pronto');
                        },
                        onError: (error) => { console.error('Erro PIX:', error); },
                    },
                });
            } else {
                console.error('Mercado Pago Credenciais faltando.');
            }
        });
    </script>
</x-app-layout>