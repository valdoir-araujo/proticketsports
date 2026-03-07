<x-app-layout>
    {{-- 
        LÓGICA JAVASCRIPT (MANTIDA ORIGINAL PARA GARANTIR FUNCIONALIDADE) 
    --}}
    <script>
        window.checkinData = @json($inscricoes);
        window.csrfToken = "{{ csrf_token() }}";
        
        window.checkinRoutes = {
            store: "{{ route('organizador.eventos.checkin.store', ['evento' => $evento->slug, 'inscricao' => 'ID_REF']) }}",
            undo: "{{ route('organizador.eventos.checkin.undo', ['evento' => $evento->slug, 'inscricao' => 'ID_REF']) }}"
        };

        function checkinComponent() {
            return {
                search: '',
                atletas: window.checkinData || [],
                processingId: null,

                // Função auxiliar para mascarar o CPF (LGPD)
                maskCpf(cpf) {
                    if (!cpf) return 'CPF não inf.';
                    
                    // Remove caracteres não numéricos para garantir
                    const nums = cpf.replace(/\D/g, '');
                    
                    // Se não tiver 11 dígitos, retorna como está (pode ser passaporte ou erro)
                    if (nums.length !== 11) return cpf;

                    // Formato: 123.***.***-45
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
                        
                        return nome.includes(term) || cpf.includes(term) || num.includes(term) || cat.includes(term);
                    });
                },

                async doCheckin(atleta) {
                    if (!atleta.temp_numero) {
                        alert('Informe o número do atleta.');
                        return;
                    }

                    if (atleta.status === 'aguardando_pagamento') {
                        if (!confirm('⚠️ PAGAMENTO PENDENTE!\n\nDeseja liberar o kit mesmo assim?')) return;
                    }

                    this.processingId = atleta.id;
                    const url = window.checkinRoutes.store.replace('ID_REF', atleta.id);

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            },
                            body: JSON.stringify({ numero_atleta: atleta.temp_numero })
                        });

                        const data = await response.json();

                        if (!response.ok) throw new Error(data.message || 'Erro');

                        atleta.numero_atleta = atleta.temp_numero;
                        atleta.checkin_realizado = true;

                    } catch (error) {
                        console.error(error);
                        alert('Erro ao salvar.');
                    } finally {
                        this.processingId = null;
                    }
                },

                async undoCheckin(atleta) {
                    if(!confirm('Deseja cancelar a entrega deste kit?')) return;

                    this.processingId = atleta.id;
                    const url = window.checkinRoutes.undo.replace('ID_REF', atleta.id);

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            }
                        });

                        if (!response.ok) throw new Error('Erro');

                        atleta.checkin_realizado = false;
                        atleta.numero_atleta = null;
                        
                    } catch (error) {
                        alert('Erro ao desfazer.');
                    } finally {
                        this.processingId = null;
                    }
                }
            }
        }
    </script>

    {{-- CABEÇALHO HERO (MODERNIZADO) --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-32 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                
                {{-- Título e Breadcrumb --}}
                <div class="text-white z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('organizador.eventos.show', $evento) }}" class="inline-flex items-center text-xs font-bold text-blue-200 hover:text-white transition-colors bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm border border-white/10 hover:bg-white/20">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Evento
                        </a>
                        <span class="text-slate-400 text-xs">•</span>
                        <span class="text-xs text-orange-300 font-bold uppercase tracking-wider">Sistema de Check-in</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md flex items-center gap-3">
                        <i class="fa-solid fa-box-open text-orange-500"></i> Entrega de Kits
                    </h2>
                    <p class="text-blue-100 mt-1 font-medium text-lg opacity-90">{{ $evento->nome }}</p>
                </div>

                {{-- Cards de Estatísticas (Compactos no Header) --}}
                <div class="flex gap-4 z-10 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 min-w-[140px] text-center">
                        <p class="text-[10px] uppercase font-bold text-blue-200 tracking-wider">Total Inscritos</p>
                        <p class="text-3xl font-black text-white">{{ $totalInscritos }}</p>
                    </div>
                    <div class="bg-green-500/20 backdrop-blur-md rounded-2xl p-4 border border-green-400/30 min-w-[140px] text-center">
                        <p class="text-[10px] uppercase font-bold text-green-200 tracking-wider">Entregues</p>
                        <p class="text-3xl font-black text-green-400">{{ $totalCheckins }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="relative z-20 -mt-24 pb-12" x-data="checkinComponent()">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- BARRA DE BUSCA FLUTUANTE (Largura reduzida de max-w-4xl para max-w-2xl) --}}
            <div class="bg-white p-2 rounded-2xl shadow-xl shadow-slate-900/10 border border-slate-100 flex items-center ring-4 ring-white/50 max-w-2xl mx-auto transform transition-all focus-within:scale-[1.01] focus-within:ring-indigo-100">
                <div class="pl-6 text-indigo-500">
                    <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                </div>
                <input type="text" x-model="search" 
                       placeholder="Buscar por Nome, CPF, Número ou Categoria..." 
                       class="w-full border-none focus:ring-0 text-xl font-medium placeholder-slate-300 text-slate-800 bg-transparent py-4 pl-4 h-16"
                       autofocus>
                
                <div class="pr-6 border-l border-slate-100 pl-6 hidden sm:block">
                    <div class="text-right leading-tight">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Encontrados</span>
                        <span class="text-2xl font-black text-indigo-600 block" x-text="filteredAtletas.length">0</span>
                    </div>
                </div>
            </div>

            {{-- ESTADO VAZIO --}}
            <div x-show="filteredAtletas.length === 0" class="flex flex-col items-center justify-center py-24 text-center animate-fade-in" style="display: none;">
                <div class="w-24 h-24 bg-white rounded-full shadow-lg flex items-center justify-center mb-6 text-slate-300">
                    <i class="fa-solid fa-user-slash text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-700">Nenhum atleta encontrado</h3>
                <p class="text-slate-500 mt-2 text-lg">Verifique o nome, número ou CPF digitado.</p>
                <button @click="search = ''" class="mt-6 px-6 py-2 bg-indigo-50 text-indigo-600 font-bold rounded-full hover:bg-indigo-100 transition-colors">
                    Limpar Busca
                </button>
            </div>

            {{-- GRID DE CARDS MODERNIZADO --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-show="filteredAtletas.length > 0">
                
                <template x-for="atleta in filteredAtletas" :key="atleta.id">
                    <div class="group relative bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative flex flex-col transition-all duration-300 hover:shadow-xl hover:border-indigo-200"
                         :class="atleta.checkin_realizado ? 'ring-2 ring-emerald-500 border-transparent' : ''">
                        
                        {{-- Banner Aviso Pagamento --}}
                        <template x-if="atleta.status === 'aguardando_pagamento'">
                            <div class="bg-orange-500 text-white text-[10px] font-bold text-center py-1 uppercase tracking-wider">
                                Pagamento Pendente
                            </div>
                        </template>

                        <div class="p-5 flex flex-col gap-4 flex-grow">
                            {{-- Header: Avatar + Nome --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    {{-- Avatar --}}
                                    <div class="relative shrink-0">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-sm"
                                             :class="atleta.checkin_realizado ? 'bg-emerald-500' : (atleta.status === 'aguardando_pagamento' ? 'bg-orange-400' : 'bg-indigo-600')">
                                            <span x-text="atleta.iniciais"></span>
                                        </div>
                                        <div x-show="atleta.checkin_realizado" class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-sm">
                                            <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                                        </div>
                                    </div>
                                    
                                    {{-- Nome & CPF --}}
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-slate-800 leading-tight truncate text-base" x-text="atleta.nome" :title="atleta.nome"></h3>
                                        <p class="text-xs text-slate-400 font-mono mt-0.5 flex items-center gap-1">
                                            <i class="fa-regular fa-id-card"></i>
                                            {{-- APLICADA A MÁSCARA AQUI --}}
                                            <span x-text="maskCpf(atleta.cpf)"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Categoria --}}
                            <div class="flex">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-[11px] font-bold uppercase tracking-wide border border-slate-200 truncate max-w-full">
                                    <i class="fa-solid fa-layer-group mr-1 opacity-50"></i>
                                    <span x-text="atleta.categoria"></span>
                                </span>
                            </div>

                            {{-- Produtos / Itens do Kit (DESIGN NOVO E VIBRANTE) --}}
                            <template x-if="atleta.produtos && atleta.produtos.length > 0">
                                <div class="mt-auto bg-orange-50 rounded-xl p-3 border border-orange-100">
                                    <p class="text-[10px] font-black text-orange-600 uppercase tracking-widest mb-2 flex items-center gap-1.5 border-b border-orange-200 pb-1">
                                        <i class="fa-solid fa-box-open"></i> ENTREGAR ITENS:
                                    </p>
                                    <div class="space-y-2">
                                        <template x-for="prod in atleta.produtos">
                                            <div class="flex items-center justify-between bg-white px-3 py-2.5 rounded-lg border border-orange-200 shadow-sm">
                                                <div class="flex flex-col leading-tight min-w-0">
                                                    <span class="text-xs font-bold text-slate-800 truncate" x-text="prod.nome"></span>
                                                    <span x-show="prod.tamanho" class="text-[11px] text-orange-600 font-bold mt-0.5" x-text="'Tam: ' + prod.tamanho"></span>
                                                </div>
                                                <span class="shrink-0 flex items-center justify-center w-7 h-7 bg-orange-600 text-white rounded-full text-xs font-black shadow-md" 
                                                      x-text="prod.quantidade + 'x'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Footer de Ação --}}
                        <div class="p-4 bg-slate-50 border-t border-slate-100 mt-auto">
                            
                            {{-- Estado: Check-in Realizado --}}
                            <div x-show="atleta.checkin_realizado" class="flex items-center justify-between">
                                <div class="flex items-center gap-3 text-emerald-700">
                                    <div class="bg-emerald-100 p-2 rounded-lg">
                                        <i class="fa-solid fa-check text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider">Entregue</p>
                                        <p class="text-sm font-bold">Kit Nº <span x-text="atleta.numero_atleta" class="text-lg"></span></p>
                                    </div>
                                </div>
                                <button @click="undoCheckin(atleta)" class="text-slate-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Desfazer">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>

                            {{-- Estado: Pendente --}}
                            <div x-show="!atleta.checkin_realizado" class="flex gap-2">
                                <div class="relative flex-grow">
                                    <input type="number" x-model="atleta.temp_numero" placeholder="Nº Kit" 
                                           class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-center font-bold text-slate-700 h-12 pl-2"
                                           @keydown.enter="doCheckin(atleta)"
                                           :disabled="processingId === atleta.id">
                                </div>
                                <button @click="doCheckin(atleta)" 
                                        class="rounded-xl w-14 h-12 flex items-center justify-center text-white shadow-lg shadow-indigo-200 transition-all active:scale-95"
                                        :class="atleta.status === 'aguardando_pagamento' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-indigo-600 hover:bg-indigo-700'"
                                        :disabled="processingId === atleta.id">
                                    <span x-show="processingId !== atleta.id"><i class="fa-solid fa-arrow-right"></i></span>
                                    <span x-show="processingId === atleta.id"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>

        </div>
    </div>

    <style>
        .animate-fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>