<x-app-layout>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        window.checkinData = @json($inscricoes);
        window.csrfToken = "{{ csrf_token() }}";
        window.checkinRoutes = {
            store: "{{ route('organizador.eventos.checkin.store', ['evento' => $evento->slug, 'inscricao' => 'ID_REF']) }}",
            undo: "{{ route('organizador.eventos.checkin.undo', ['evento' => $evento->slug, 'inscricao' => 'ID_REF']) }}"
        };
        window.checkinStorageKey = 'checkin_{{ $evento->slug ?? "event" }}';

        function checkinComponent() {
            var storageKey = window.checkinStorageKey || 'checkin_event';
            var saved = {};
            try {
                var raw = sessionStorage.getItem(storageKey);
                if (raw) saved = JSON.parse(raw);
            } catch (e) {}
            var initialAtletas = Array.isArray(window.checkinData) ? window.checkinData : [];
            return {
                search: (saved && typeof saved.search === 'string') ? saved.search : '',
                atletas: initialAtletas,
                processingId: null,
                // QR Scanner
                scanModalOpen: false,
                scannerInstance: null,
                scanError: '',
                lastScannedCode: '',
                lastScannedAt: 0,
                // Quick check-in (após escanear)
                quickCheckinAtleta: null,
                // Toast
                toast: { show: false, message: '', type: 'success' },

                get totalCheckinsRealizados() {
                    return this.atletas.filter(a => a.checkin_realizado).length;
                },

                maskCpf(cpf) {
                    if (!cpf) return 'CPF não inf.';
                    const nums = cpf.replace(/\D/g, '');
                    if (nums.length !== 11) return cpf;
                    return nums.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.***.***-$4");
                },

                get filteredAtletas() {
                    if (this.search === '') return this.atletas;
                    const term = this.search.toLowerCase();
                    return this.atletas.filter(item => {
                        const nome = (item.nome || '').toLowerCase();
                        const cpf = (item.cpf || '').toLowerCase();
                        const num = (item.numero_atleta || '').toLowerCase();
                        const cat = (item.categoria || '').toLowerCase();
                        const percurso = (item.percurso || '').toLowerCase();
                        const codigo = (item.codigo_inscricao || '').toLowerCase();
                        return nome.includes(term) || cpf.includes(term) || num.includes(term) || cat.includes(term) || percurso.includes(term) || codigo.includes(term);
                    });
                },

                getItensEntreguesPayload(atleta) {
                    if (!atleta.produtos || !atleta.produtos.length) return {};
                    const obj = {};
                    atleta.produtos.forEach(p => { if (p.pivot_id != null) obj[p.pivot_id] = !!p.entregue; });
                    return obj;
                },

                showToast(message, type = 'success') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 3200);
                },

                saveSearchToStorage() {
                    try {
                        var key = window.checkinStorageKey || 'checkin_event';
                        var o = {};
                        try { var r = sessionStorage.getItem(key); if (r) o = JSON.parse(r); } catch(e) {}
                        o.search = this.search;
                        sessionStorage.setItem(key, JSON.stringify(o));
                    } catch (e) {}
                },

                async openScan() {
                    if (typeof Html5Qrcode === 'undefined') {
                        this.showToast('Leitor de QR não carregou. Recarregue a página.', 'error');
                        return;
                    }
                    if (!window.isSecureContext) {
                        this.showToast('No celular use o site em HTTPS para a câmera funcionar.', 'error');
                        return;
                    }
                    this.scanModalOpen = true;
                    this.scanError = '';
                    this.lastScannedCode = '';
                    const self = this;
                    this.$nextTick(() => {
                        const el = document.getElementById('qr-reader');
                        if (!el) return;
                        self.scannerInstance = new Html5Qrcode('qr-reader');
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                        const onSuccess = (decodedText) => self.onScanSuccess(decodedText);
                        const tryStart = (cameraIdOrConfig) => {
                            self.scannerInstance.start(cameraIdOrConfig, config, onSuccess, () => {}).catch(err => {
                                console.error(err);
                                self.scanError = 'Câmera indisponível. Use o site em HTTPS, permita acesso à câmera ou use "Escolher foto" abaixo.';
                            });
                        };
                        Html5Qrcode.getCameras().then(cameras => {
                            if (cameras && cameras.length) {
                                const back = cameras.find(c => /back|traseira|environment/i.test(c.label));
                                tryStart(back ? back.id : cameras[0].id);
                            } else {
                                tryStart({ facingMode: 'environment' });
                            }
                        }).catch(() => tryStart({ facingMode: 'environment' }));
                    });
                },

                triggerFileScan() {
                    const input = document.getElementById('qr-file-input');
                    if (input) input.click();
                },

                onFileScanned(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file || typeof Html5Qrcode === 'undefined') return;
                    const self = this;
                    const scanner = new Html5Qrcode('qr-reader');
                    scanner.scanFile(file, false).then(decodedText => {
                        self.onScanSuccess(decodedText);
                    }).catch(() => {
                        self.showToast('Nenhum QR Code encontrado na imagem.', 'error');
                    }).finally(() => {
                        event.target.value = '';
                    });
                },

                onScanSuccess(decodedText) {
                    const code = (decodedText || '').trim();
                    if (!code) return;
                    if (this.lastScannedCode === code && (Date.now() - this.lastScannedAt) < 2500) return;
                    this.lastScannedCode = code;
                    this.lastScannedAt = Date.now();

                    if (this.scannerInstance) {
                        this.scannerInstance.stop().then(() => {
                            if (this.scannerInstance) this.scannerInstance.clear();
                            this.scannerInstance = null;
                        }).catch(() => {});
                    }
                    this.scanModalOpen = false;

                    const atleta = this.atletas.find(a => (a.codigo_inscricao || '').toUpperCase() === code.toUpperCase());
                    if (!atleta) {
                        this.showToast('Inscrição não encontrada neste evento.', 'error');
                        return;
                    }
                    if (atleta.checkin_realizado) {
                        this.showToast('Check-in já realizado para este atleta.', 'info');
                        this.search = atleta.codigo_inscricao || '';
                        return;
                    }
                    atleta.temp_numero = atleta.temp_numero || '';
                    this.quickCheckinAtleta = atleta;
                },

                closeScan() {
                    if (this.scannerInstance) {
                        this.scannerInstance.stop().then(() => {
                            if (this.scannerInstance) this.scannerInstance.clear();
                        }).catch(() => {});
                        this.scannerInstance = null;
                    }
                    this.scanModalOpen = false;
                },

                closeQuickCheckin() {
                    this.quickCheckinAtleta = null;
                },

                async confirmQuickCheckin() {
                    const atleta = this.quickCheckinAtleta;
                    if (!atleta || !atleta.temp_numero) {
                        this.showToast('Informe o número do kit.', 'error');
                        return;
                    }
                    if (atleta.status === 'aguardando_pagamento' && !confirm('Pagamento pendente. Liberar kit mesmo assim?')) return;

                    this.processingId = atleta.id;
                    const url = window.checkinRoutes.store.replace('ID_REF', atleta.id);
                    const body = { numero_atleta: atleta.temp_numero, itens_entregues: this.getItensEntreguesPayload(atleta) };
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Erro');
                        atleta.numero_atleta = atleta.temp_numero;
                        atleta.checkin_realizado = true;
                        this.quickCheckinAtleta = null;
                        this.showToast('Check-in realizado com sucesso!');
                    } catch (e) {
                        this.showToast('Erro ao salvar. Tente novamente.', 'error');
                    } finally {
                        this.processingId = null;
                    }
                },

                async doCheckin(atleta) {
                    if (!atleta.temp_numero) {
                        this.showToast('Informe o número do atleta.');
                        return;
                    }
                    if (atleta.status === 'aguardando_pagamento' && !confirm('Pagamento pendente. Liberar o kit mesmo assim?')) return;

                    this.processingId = atleta.id;
                    const url = window.checkinRoutes.store.replace('ID_REF', atleta.id);
                    const body = { numero_atleta: atleta.temp_numero, itens_entregues: this.getItensEntreguesPayload(atleta) };
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                            body: JSON.stringify(body)
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Erro');
                        atleta.numero_atleta = atleta.temp_numero;
                        atleta.checkin_realizado = true;
                        this.showToast('Check-in realizado!');
                    } catch (error) {
                        this.showToast('Erro ao salvar.', 'error');
                    } finally {
                        this.processingId = null;
                    }
                },

                async undoCheckin(atleta) {
                    if (!confirm('Desfazer entrega deste kit?')) return;
                    this.processingId = atleta.id;
                    const url = window.checkinRoutes.undo.replace('ID_REF', atleta.id);
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }
                        });
                        if (!response.ok) throw new Error('Erro');
                        atleta.checkin_realizado = false;
                        atleta.numero_atleta = null;
                        this.showToast('Check-in desfeito.');
                    } catch (error) {
                        this.showToast('Erro ao desfazer.', 'error');
                    } finally {
                        this.processingId = null;
                    }
                },

                init() {
                    var self = this;
                    if (self.atletas.length === 0 && Array.isArray(window.checkinData)) self.atletas = window.checkinData;
                    self.$watch('search', function() { self.saveSearchToStorage(); });
                    self.saveSearchToStorage();
                    window.addEventListener('beforeunload', function() {
                        if (self.scannerInstance) self.scannerInstance.stop().catch(function(){});
                    });
                }
            };
        }
        window.checkinComponent = checkinComponent;
        document.addEventListener('alpine:init', function() {
            if (window.Alpine) window.Alpine.data('checkinComponent', checkinComponent);
        });
    </script>

    <div class="min-h-screen bg-slate-100" x-data="checkinComponent()" data-checkin-version="persist-v2">
        {{-- Hero + CTA Escanear --}}
        <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-6 pb-8 sm:pt-8 sm:pb-10 overflow-hidden">
            <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#0ea5e9 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div class="text-white">
                        <a href="{{ route('organizador.eventos.show', $evento) }}" class="inline-flex items-center text-xs font-bold text-blue-200 hover:text-white transition-colors mb-3">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Evento
                        </a>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                            <i class="fa-solid fa-box-open text-orange-400"></i> Check-in
                        </h1>
                        <p class="text-blue-100 mt-0.5 font-medium">{{ $evento->nome }}</p>
                    </div>
                    {{-- Stats ao vivo --}}
                    <div class="flex gap-3 sm:gap-4">
                        <div class="bg-white/10 backdrop-blur rounded-2xl px-4 py-3 text-center min-w-[100px] sm:min-w-[120px]">
                            <p class="text-[10px] uppercase font-bold text-blue-200 tracking-wider">Total</p>
                            <p class="text-2xl sm:text-3xl font-black text-white" x-text="atletas.length"></p>
                        </div>
                        <div class="bg-emerald-500/25 backdrop-blur rounded-2xl px-4 py-3 text-center min-w-[100px] sm:min-w-[120px] border border-emerald-400/30">
                            <p class="text-[10px] uppercase font-bold text-emerald-200 tracking-wider">Entregues</p>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-300" x-text="totalCheckinsRealizados"></p>
                        </div>
                    </div>
                </div>
                {{-- CTA Principal: Escanear QR (grande, visível) --}}
                <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row gap-4">
                    <button type="button" @click="openScan()"
                            class="flex-1 inline-flex items-center justify-center gap-3 px-6 py-4 sm:py-5 rounded-2xl font-bold text-lg text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-lg shadow-emerald-900/30 active:scale-[0.98] transition-all border border-emerald-400/30">
                        <i class="fa-solid fa-qrcode text-3xl"></i>
                        <span>Escanear QR Code do recibo</span>
                    </button>
                    <div class="flex-1 max-w-md relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input type="text" x-model="search"
                               @blur="saveSearchToStorage()"
                               placeholder="Buscar por nome, código ou CPF..."
                               class="w-full h-14 pl-12 pr-4 rounded-2xl border-0 shadow-lg text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-white/50 font-medium">
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista --}}
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div x-show="filteredAtletas.length === 0" class="text-center py-16 rounded-2xl bg-white shadow border border-slate-200" style="display: none;">
                <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                    <i class="fa-solid fa-user-slash text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700">Nenhum resultado</h3>
                <p class="text-slate-500 mt-1">Ajuste a busca ou <button @click="search = ''" class="text-indigo-600 font-semibold hover:underline">limpar</button>.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6" x-show="filteredAtletas.length > 0">
                <template x-for="atleta in filteredAtletas" :key="atleta.id">
                    <div class="bg-white rounded-2xl shadow border overflow-hidden flex flex-col transition-all duration-200 hover:shadow-lg"
                         :class="atleta.checkin_realizado ? 'ring-2 ring-emerald-500 border-emerald-200' : 'border-slate-200'">
                        <template x-if="atleta.status === 'aguardando_pagamento'">
                            <div class="bg-amber-500 text-white text-[10px] font-bold text-center py-1.5 uppercase tracking-wider">Pagamento pendente</div>
                        </template>
                        <div class="p-4 sm:p-5 flex flex-col flex-grow">
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
                                         :class="atleta.checkin_realizado ? 'bg-emerald-500' : (atleta.status === 'aguardando_pagamento' ? 'bg-amber-500' : 'bg-indigo-500')">
                                        <span x-text="atleta.iniciais"></span>
                                    </div>
                                    <div x-show="atleta.checkin_realizado" class="absolute -bottom-0.5 -right-0.5 bg-white rounded-full p-0.5 shadow">
                                        <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-slate-800 truncate" x-text="atleta.nome"></h3>
                                    <p class="text-xs text-slate-500 font-mono" x-text="maskCpf(atleta.cpf)"></p>
                                    <p class="text-[11px] text-slate-400 font-mono mt-0.5" x-text="atleta.codigo_inscricao"></p>
                                </div>
                            </div>
                            {{-- Percurso e Categoria em destaque --}}
                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-if="atleta.percurso">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-800">
                                        <i class="fa-solid fa-route text-sm"></i>
                                        <span class="text-xs font-bold" x-text="atleta.percurso"></span>
                                    </div>
                                </template>
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700">
                                    <i class="fa-solid fa-layer-group text-sm"></i>
                                    <span class="text-xs font-bold" x-text="atleta.categoria"></span>
                                </div>
                            </div>
                            {{-- Produtos comprados: descrição clara + opção "Retirou" (padrão todos marcados) --}}
                            <template x-if="atleta.produtos && atleta.produtos.length > 0">
                                <div class="mt-3 pt-3 border-t border-slate-200">
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <i class="fa-solid fa-box-open"></i> Itens do kit
                                    </p>
                                    <div class="space-y-2">
                                        <template x-for="prod in atleta.produtos" :key="prod.pivot_id">
                                            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                                <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                                    <input type="checkbox" x-model="prod.entregue"
                                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4"
                                                           :disabled="atleta.checkin_realizado">
                                                    <span class="text-xs font-semibold text-slate-600">Retirou</span>
                                                </label>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold text-slate-800" x-text="prod.nome"></p>
                                                    <p class="text-xs text-slate-500" x-text="(prod.tamanho && prod.tamanho !== 'U' ? 'Tamanho: ' + prod.tamanho + ' • ' : '') + 'Quantidade: ' + prod.quantidade"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="p-4 bg-slate-50 border-t border-slate-100">
                            <template x-if="atleta.checkin_realizado">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-emerald-700">
                                        <i class="fa-solid fa-check-circle text-xl"></i>
                                        <span class="font-semibold">Kit Nº <span x-text="atleta.numero_atleta"></span></span>
                                    </div>
                                    <button @click="undoCheckin(atleta)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Desfazer">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!atleta.checkin_realizado">
                                <div class="flex gap-2">
                                    <input type="text" x-model="atleta.temp_numero" placeholder="Nº kit"
                                           class="flex-1 h-12 rounded-xl border-slate-200 text-center font-bold text-slate-800"
                                           @keydown.enter="doCheckin(atleta)"
                                           :disabled="processingId === atleta.id">
                                    <button @click="doCheckin(atleta)"
                                            class="h-12 w-14 rounded-xl flex items-center justify-center text-white font-bold shadow-md active:scale-95 transition-transform"
                                            :class="atleta.status === 'aguardando_pagamento' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-indigo-600 hover:bg-indigo-700'"
                                            :disabled="processingId === atleta.id">
                                        <span x-show="processingId !== atleta.id"><i class="fa-solid fa-check"></i></span>
                                        <span x-show="processingId === atleta.id"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal: Scanner QR (fullscreen no mobile) --}}
        <div x-show="scanModalOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex flex-col bg-slate-900"
             @keydown.escape.window="closeScan()">
            <div class="flex items-center justify-between p-4 bg-slate-800/90 text-white">
                <span class="font-bold flex items-center gap-2"><i class="fa-solid fa-qrcode"></i> Aponte para o QR Code do recibo</span>
                <button type="button" @click="closeScan()" class="p-2 rounded-full hover:bg-white/10 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center p-4 min-h-0">
                <div id="qr-reader" class="w-full max-w-[320px] overflow-hidden rounded-2xl bg-black min-h-[280px]"></div>
                <p x-show="scanError" x-text="scanError" class="mt-4 text-amber-400 text-sm text-center max-w-sm"></p>
                <input type="file" id="qr-file-input" accept="image/*" capture="environment" class="hidden" @change="onFileScanned($event)">
                <button type="button" @click="triggerFileScan()" class="mt-4 inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium border border-white/20">
                    <i class="fa-solid fa-image"></i> Escolher foto do QR Code
                </button>
                <p class="mt-2 text-xs text-slate-400 text-center max-w-xs">Se a câmera não abrir (ex.: iPhone), tire uma foto do QR e escolha aqui.</p>
            </div>
        </div>

        {{-- Modal: Check-in rápido (após escanear) --}}
        <div x-show="quickCheckinAtleta !== null" x-cloak
             x-transition
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50"
             @keydown.escape.window="closeQuickCheckin()">
            <div class="w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden"
                 @click.outside="closeQuickCheckin()">
                <div class="bg-emerald-600 text-white px-6 py-4 text-center">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-90">Check-in rápido</p>
                    <p class="text-xl font-bold mt-0.5" x-text="quickCheckinAtleta && quickCheckinAtleta.nome"></p>
                    <p class="text-sm font-mono opacity-90 mt-1" x-text="quickCheckinAtleta && quickCheckinAtleta.codigo_inscricao"></p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Número do kit</label>
                        <input type="text" x-model="quickCheckinAtleta && quickCheckinAtleta.temp_numero"
                               placeholder="Ex: 42"
                               class="w-full h-14 rounded-xl border-2 border-slate-200 text-center text-xl font-bold text-slate-800 focus:border-emerald-500 focus:ring-emerald-500"
                               @keydown.enter="confirmQuickCheckin()">
                    </div>
                    <template x-if="quickCheckinAtleta && quickCheckinAtleta.produtos && quickCheckinAtleta.produtos.length > 0">
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Itens que retirou (desmarque pendentes)</p>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <template x-for="prod in quickCheckinAtleta.produtos" :key="prod.pivot_id">
                                    <label class="flex items-center gap-3 p-2 rounded-lg bg-slate-50 border border-slate-100 cursor-pointer">
                                        <input type="checkbox" x-model="prod.entregue" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                        <span class="text-sm font-medium text-slate-800" x-text="prod.nome"></span>
                                        <span class="text-xs text-slate-500" x-text="(prod.tamanho && prod.tamanho !== 'U' ? prod.tamanho + ' • ' : '') + 'x' + prod.quantidade"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div class="flex gap-3">
                        <button type="button" @click="closeQuickCheckin()"
                                class="flex-1 h-14 rounded-xl border-2 border-slate-200 font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmQuickCheckin()"
                                :disabled="!quickCheckinAtleta || !quickCheckinAtleta.temp_numero || processingId === (quickCheckinAtleta && quickCheckinAtleta.id)"
                                class="flex-1 h-14 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                            <span x-show="processingId !== (quickCheckinAtleta && quickCheckinAtleta.id)">Confirmar check-in</span>
                            <span x-show="processingId === (quickCheckinAtleta && quickCheckinAtleta.id)"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast --}}
        <div x-show="toast.show" x-cloak x-transition
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] px-6 py-3 rounded-xl shadow-lg font-semibold text-white max-w-[90vw]"
             :class="toast.type === 'error' ? 'bg-red-500' : (toast.type === 'info' ? 'bg-blue-500' : 'bg-emerald-500')">
            <span x-text="toast.message"></span>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        #qr-reader video { border-radius: 1rem; }
        #qr-reader__scan_region { background: transparent !important; }
    </style>
</x-app-layout>
