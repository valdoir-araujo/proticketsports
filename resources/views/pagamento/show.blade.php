@extends('layouts.public')

@section('title', 'Pagamento - Inscrição #' . $inscricao->id)

@section('content')
<div class="bg-gray-50 min-h-screen pt-24 pb-20 font-sans">
    {{-- Header de Segurança (igual à loja) --}}
    <div class="bg-white border-b border-gray-200 shadow-sm fixed top-16 left-0 w-full z-40 h-14 flex items-center">
        <div class="container mx-auto px-4 md:px-8 max-w-6xl flex justify-between items-center">
            <div class="flex items-center gap-2 text-green-600">
                <i class="fa-solid fa-lock text-sm"></i>
                <span class="text-xs font-bold uppercase tracking-wider">Ambiente 100% Seguro</span>
            </div>
            <div class="text-xs text-gray-500 hidden sm:block">
                Inscrição <span class="font-mono font-bold text-gray-800">#{{ $inscricao->id }}</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="container mx-auto px-4 md:px-8 max-w-6xl mt-6">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-sm">
                <p class="text-sm text-red-700 font-bold">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <div class="container mx-auto px-4 md:px-8 max-w-6xl mt-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            {{-- COLUNA 1: ÁREA DE PAGAMENTO (igual à loja) --}}
            <div class="lg:col-span-2 space-y-6" x-data="paymentDataInscricao()">
                <div>
                    <h1 class="text-2xl font-black text-slate-800">Escolha a forma de pagamento</h1>
                    <p class="text-slate-500 text-sm mt-1">Finalize sua inscrição com segurança.</p>
                </div>

                <div x-show="errorMessage" style="display: none;" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-sm">
                    <p class="text-sm text-red-700 font-bold" x-text="errorMessage"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button type="button" @click="method = 'pix'; errorMessage = ''"
                        :class="method === 'pix' ? 'border-green-500 bg-green-50 text-green-800 ring-1 ring-green-500' : 'border-slate-200 bg-white text-slate-600 hover:border-green-300 hover:bg-slate-50'"
                        class="relative py-4 px-4 rounded-xl border-2 font-bold transition-all flex flex-col items-center justify-center gap-2 shadow-sm h-24">
                        <i class="fa-brands fa-pix text-2xl"></i>
                        <span>PIX</span>
                        <div x-show="method === 'pix'" class="absolute top-2 right-2 text-green-600"><i class="fa-solid fa-circle-check"></i></div>
                    </button>
                    <button type="button" @click="method = 'card'; errorMessage = ''; $nextTick(() => { setTimeout(() => { if (typeof initCardInscricao === 'function') initCardInscricao(); }, 350); })"
                        :class="method === 'card' ? 'border-blue-500 bg-blue-50 text-blue-800 ring-1 ring-blue-500' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:bg-slate-50'"
                        class="relative py-4 px-4 rounded-xl border-2 font-bold transition-all flex flex-col items-center justify-center gap-2 shadow-sm h-24">
                        <i class="fa-regular fa-credit-card text-2xl"></i>
                        <span>Cartão de Crédito</span>
                        <div x-show="method === 'card'" class="absolute top-2 right-2 text-blue-600"><i class="fa-solid fa-circle-check"></i></div>
                    </button>
                </div>

                {{-- PIX --}}
                <div x-show="method === 'pix'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8 text-center">
                        <div x-show="!qrCodeBase64">
                            <div class="mb-6">
                                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800">Pagamento Instantâneo</h3>
                                <p class="text-slate-500 text-sm mt-2 max-w-sm mx-auto">Gere o QR Code e pague pelo aplicativo do seu banco. A aprovação é imediata.</p>
                            </div>
                            <button type="button" @click="generatePix()" :disabled="loading"
                                class="w-full md:w-auto px-10 py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-200 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 mx-auto disabled:opacity-70 disabled:cursor-not-allowed text-lg">
                                <span x-show="!loading">Gerar QR Code PIX</span>
                                <span x-show="loading" class="flex items-center gap-2"><i class="fa-solid fa-circle-notch fa-spin"></i> Gerando...</span>
                            </button>
                        </div>
                        <div x-show="qrCodeBase64" style="display: none;">
                            <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg mb-6 inline-flex items-center gap-2 border border-green-200 text-sm font-bold">
                                <i class="fa-regular fa-clock"></i> Aguardando pagamento...
                            </div>
                            <div class="mb-6 flex justify-center">
                                <div class="p-2 bg-white border-2 border-slate-100 rounded-xl shadow-sm">
                                    <img :src="'data:image/png;base64,' + qrCodeBase64" class="w-64 h-64 rounded-lg object-contain" alt="QR Code PIX">
                                </div>
                            </div>
                            <div class="max-w-md mx-auto mb-6">
                                <label class="text-xs font-bold text-slate-400 uppercase mb-2 block tracking-wide">Pix Copia e Cola</label>
                                <div class="flex shadow-sm">
                                    <input type="text" x-model="qrCode" readonly class="w-full bg-slate-50 border border-slate-200 text-slate-600 text-xs p-3 rounded-l-lg font-mono focus:outline-none focus:ring-0">
                                    <button type="button" @click="copyCode()" class="bg-slate-800 text-white px-5 rounded-r-lg font-bold text-xs hover:bg-slate-900 transition-colors flex items-center gap-2">
                                        <i class="fa-regular fa-copy"></i> <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600">Após pagar, a confirmação é automática pelo Mercado Pago. Acompanhe o status em <a href="{{ route('atleta.inscricoes') }}" class="text-blue-600 hover:underline font-medium">Minhas Inscrições</a>.</p>
                        </div>
                    </div>
                </div>

                {{-- Cartão: Card Payment Brick (somente cartão, mais estável que o Payment Brick) --}}
                <div x-show="method === 'card'" x-transition:enter="transition ease-out duration-300" style="display: none;" x-cloak>
                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-6 relative min-h-[380px]">
                        <div id="loading-card-inscricao" class="absolute inset-0 flex items-center justify-center bg-white z-20 rounded-xl">
                            <div class="flex flex-col items-center">
                                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-500 mb-2"></i>
                                <span class="text-sm text-slate-500">Carregando formulário de cartão...</span>
                            </div>
                        </div>
                        <div id="cardPaymentBrick_container_inscricao" class="min-h-[340px] w-full"></div>
                    </div>
                </div>

                <div class="text-center md:text-left mt-8">
                    <a href="{{ route('inscricao.show', $inscricao) }}" class="inline-flex items-center text-sm font-bold text-slate-400 hover:text-red-600 transition-colors gap-2 group">
                        <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Cancelar e voltar
                    </a>
                </div>
            </div>

            {{-- COLUNA 2: RESUMO --}}
            <div class="lg:col-span-1 lg:sticky lg:top-36">
                <div class="bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 p-5 border-b border-slate-200">
                        <h3 class="font-bold text-slate-800 text-lg">Resumo da Inscrição</h3>
                    </div>
                    <div class="p-5 space-y-3 border-b border-slate-100">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Evento</p>
                            <p class="font-bold text-slate-800">{{ $inscricao->evento->nome }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Atleta</p>
                            <p class="font-medium text-slate-800">{{ $inscricao->atleta->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $inscricao->categoria->nome }}</p>
                        </div>
                    </div>
                    <div class="p-5 space-y-2 bg-slate-50/50">
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Inscrição + itens</span>
                            <span>R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 mt-2 border-t border-slate-200">
                            <span class="font-bold text-slate-800">Total</span>
                            <span class="text-2xl font-black text-green-600">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span>
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
(function() {
    var processUrlInscricao = @json(route('pagamento.process', $inscricao));
    var csrfInscricao = @json(csrf_token());
    var emailInscricao = @json($inscricao->atleta->user->email ?? '');
    var valorInscricao = @json((float) $inscricao->valor_pago);
    var publicKeyInscricao = @json($publicKey ?? '');
    var successUrlInscricao = @json(route('pagamento.sucesso', $inscricao));

    window.paymentDataInscricao = function() {
        return {
            method: 'pix',
            errorMessage: '',
            loading: false,
            qrCode: '',
            qrCodeBase64: '',
            copied: false,
            init: function() {
                if (!publicKeyInscricao) this.errorMessage = 'Chave do Mercado Pago não configurada.';
            },
            generatePix: function() {
                var self = this;
                this.loading = true;
                this.errorMessage = '';
                var payload = { payment_method_id: 'pix', payer: { email: emailInscricao }, device_id: (typeof MP_DEVICE_SESSION_ID !== 'undefined' ? MP_DEVICE_SESSION_ID : '') };
                fetch(processUrlInscricao, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfInscricao, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(function(res) { return res.json().then(function(data) { if (!res.ok) throw new Error(data.message || 'Erro ' + res.status); return data; }); })
                .then(function(data) {
                    self.loading = false;
                    if (data.status === 'success' && data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    if (data.qr_code_base64) {
                        self.qrCodeBase64 = data.qr_code_base64;
                        self.qrCode = data.qr_code || '';
                    } else {
                        self.errorMessage = data.message || 'Erro ao gerar PIX. Tente novamente.';
                    }
                })
                .catch(function(err) {
                    self.loading = false;
                    self.errorMessage = err.message || 'Erro de conexão. Tente novamente.';
                });
            },
            copyCode: function() {
                var self = this;
                if (!this.qrCode) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(this.qrCode).then(function() {
                        self.copied = true;
                        setTimeout(function() { self.copied = false; }, 2000);
                    });
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = this.qrCode;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    ta.remove();
                    this.copied = true;
                    setTimeout(function() { this.copied = false; }, 2000);
                }
            }
        };
    };

    var cardBrickCreatedInscricao = false;
    function setCardErrorInscricao(msg) {
        var root = document.querySelector('[x-data*="paymentDataInscricao"]');
        if (root && root.__x && root.__x.$data) root.__x.$data.errorMessage = msg;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    window.initCardInscricao = function() {
        if (cardBrickCreatedInscricao || !publicKeyInscricao) return;
        var container = document.getElementById('cardPaymentBrick_container_inscricao');
        if (!container) return;
        if (window.location.protocol !== 'https:' && !/localhost|127\.0\.0\.1/.test(window.location.hostname)) {
            setCardErrorInscricao('Pagamento com cartão disponível apenas em HTTPS. Use PIX para pagar.');
            document.getElementById('loading-card-inscricao').style.display = 'none';
            return;
        }
        var loadingEl = document.getElementById('loading-card-inscricao');
        var amount = Number(valorInscricao);
        if (!amount || amount <= 0) {
            setCardErrorInscricao('Valor inválido. Use PIX ou contate o suporte.');
            if (loadingEl) loadingEl.style.display = 'none';
            return;
        }
        if (typeof MercadoPago === 'undefined') {
            setCardErrorInscricao('Sistema de cartão não carregou. Recarregue a página ou use PIX.');
            if (loadingEl) loadingEl.style.display = 'none';
            return;
        }
        cardBrickCreatedInscricao = true;
        var mp = new MercadoPago(publicKeyInscricao, { locale: 'pt-BR' });
        var bricksBuilder = mp.bricks();
        var settings = {
            initialization: { amount: amount },
            callbacks: {
                onReady: function() { if (loadingEl) loadingEl.style.display = 'none'; },
                onSubmit: function(formData) {
                    var payload = Object.assign({}, formData);
                    if (payload.paymentMethodId && !payload.payment_method_id) payload.payment_method_id = payload.paymentMethodId;
                    if (payload.issuerId !== undefined && payload.issuer_id === undefined) payload.issuer_id = payload.issuerId;
                    payload.device_id = payload.device_id || payload.deviceId || (typeof MP_DEVICE_SESSION_ID !== 'undefined' ? MP_DEVICE_SESSION_ID : '');
                    var root = document.querySelector('[x-data*="paymentDataInscricao"]');
                    if (root && root.__x) root.__x.$data.loading = true;
                    return fetch(processUrlInscricao, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfInscricao, 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    })
                    .then(function(r) { return r.json().then(function(d) { if (!r.ok) throw new Error(d.message || 'Erro'); return d; }); })
                    .then(function(data) {
                        if (root && root.__x) root.__x.$data.loading = false;
                        if (data.redirect_url) { window.location.href = data.redirect_url; return; }
                        if (data.status === 'success' && data.payment_id) { window.location.href = successUrlInscricao; return; }
                        var msg = data.message || 'Pagamento não aprovado.';
                        if (root && root.__x) root.__x.$data.errorMessage = msg;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        throw new Error(msg);
                    })
                    .catch(function(err) {
                        if (root && root.__x) {
                            root.__x.$data.loading = false;
                            root.__x.$data.errorMessage = err.message || 'Erro ao processar. Tente PIX ou contate o suporte.';
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        throw err;
                    });
                },
                onError: function(err) {
                    if (loadingEl) loadingEl.style.display = 'none';
                    console.error('[ProTicket] Card Brick onError:', err);
                    setCardErrorInscricao('Erro no formulário de cartão. Use PIX para pagar.');
                }
            }
        };
        bricksBuilder.create('cardPayment', 'cardPaymentBrick_container_inscricao', settings)
            .then(function() {})
            .catch(function(err) {
                cardBrickCreatedInscricao = false;
                if (loadingEl) loadingEl.style.display = 'none';
                console.error('[ProTicket] Card Brick create falhou:', err);
                setCardErrorInscricao('Formulário de cartão indisponível. Use PIX para pagar.');
            });
    };
})();
</script>
@endpush
