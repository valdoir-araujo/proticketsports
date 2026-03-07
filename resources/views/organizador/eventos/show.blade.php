<x-app-layout>
    {{-- Main Alpine Component --}}
    <div x-data="{
            tab: '{{ session('tab', 'inscritos') }}',
            showWhatsappModal: false,
            loadingWhatsapp: false,
            textoWhatsapp: '',
            feedbackCopiado: false,
            
            openWhatsappModal() {
                this.showWhatsappModal = true;
                this.loadingWhatsapp = true;
                this.feedbackCopiado = false;
                
                fetch('{{ route('organizador.eventos.gerarTextoWhatsapp', $evento) }}')
                    .then(response => response.json())
                    .then(data => {
                        this.textoWhatsapp = data.texto_whatsapp;
                        this.loadingWhatsapp = false;
                    })
                    .catch(() => {
                        this.textoWhatsapp = 'Ocorreu um erro ao gerar o texto.';
                        this.loadingWhatsapp = false;
                    });
            },
            copyToClipboard() {
                const textarea = document.createElement('textarea');
                textarea.value = this.textoWhatsapp;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                
                this.feedbackCopiado = true;
                setTimeout(() => this.feedbackCopiado = false, 2000);
            },
            shareOnWhatsApp() {
                const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(this.textoWhatsapp)}`;
                window.open(whatsappUrl, '_blank');
            }
        }"
        x-init="
            // 'repasse' mantido aqui para permitir link direto via URL, mesmo sem botão na barra
            const validTabs = ['financeiro', 'inscritos', 'percursos', 'lotes_gerais', 'produtos', 'cupons', 'repasse', 'numeracao', 'resultados', 'relatorios'];
            const urlParams = new URLSearchParams(window.location.search);
            const tabFromUrl = urlParams.get('tab');
            const hash = window.location.hash.substring(1);

            if (tabFromUrl && validTabs.includes(tabFromUrl)) {
                tab = tabFromUrl;
            } else if (hash && validTabs.includes(hash)) {
                tab = hash;
            }
            
            if (urlParams.has('lancamentosPage')) { tab = 'financeiro'; }
            if (urlParams.has('inscritosPage')) { tab = 'inscritos'; }

            $watch('tab', value => { 
                const newUrl = new URL(window.location);
                newUrl.searchParams.delete('tab');
                newUrl.hash = value;
                window.history.replaceState({}, '', newUrl);
            });
        ">

        {{-- NEW HERO HEADER --}}
        <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-24 overflow-hidden shadow-xl">
            {{-- Background Effects --}}
            <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    {{-- Title & Breadcrumb --}}
                    <div class="text-white z-10">
                        <div class="flex items-center gap-3 mb-2">
                            @if($evento->campeonato_id)
                                <a href="{{ route('organizador.campeonatos.show', $evento->campeonato_id) }}" class="inline-flex items-center text-xs font-bold text-blue-200 hover:text-white transition-colors bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm">
                                    <i class="fa-solid fa-trophy mr-2"></i> {{ $evento->campeonato->nome }}
                                </a>
                            @else
                                <span class="inline-flex items-center text-xs font-bold text-orange-200 bg-orange-500/20 px-3 py-1 rounded-full backdrop-blur-sm border border-orange-500/30">
                                    <i class="fa-solid fa-calendar-star mr-2"></i> Evento Avulso
                                </span>
                            @endif
                            <span class="text-slate-400 text-xs">•</span>
                            <a href="{{ route('organizador.dashboard', ['org_id' => $evento->organizacao_id]) }}" class="text-xs text-slate-300 hover:text-white transition-colors hover:underline">Voltar ao Painel</a>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                            {{ $evento->nome }}
                        </h2>
                    </div>

                    {{-- Actions Toolbar --}}
                    <div class="flex flex-wrap items-center gap-3 z-10">
                        <a href="{{ route('organizador.eventos.checkin.index', $evento) }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-900/50 transition-all hover:-translate-y-0.5 border border-indigo-500/50" title="Acessar sistema de entrega de kits">
                            <i class="fa-solid fa-clipboard-check mr-2"></i> Check-in
                        </a>
                        
                        <button @click="openWhatsappModal()" class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-teal-900/50 transition-all hover:-translate-y-0.5 border border-teal-500/50">
                            <i class="fa-brands fa-whatsapp mr-2"></i> Divulgar
                        </button>

                        <a href="{{ route('organizador.eventos.edit', $evento) }}" class="inline-flex items-center px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-sm font-bold backdrop-blur-md border border-white/10 transition-all hover:-translate-y-0.5">
                            <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                        </a>

                        {{-- Botão Repasse (Movido do Menu para Destaque) --}}
                        <button @click="tab = 'repasse'" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-900/50 transition-all hover:-translate-y-0.5 border border-emerald-500/50">
                            <i class="fa-solid fa-money-bill-transfer mr-2"></i> Repasse
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20 pb-12">
            
            {{-- Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <!-- Card Inscritos -->
                <div class="bg-white rounded-2xl p-5 shadow-lg border-t-4 border-blue-500 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Total Inscritos</p>
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $totalInscritos ?? 0 }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-users text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- Card Pendentes -->
                <div class="bg-white rounded-2xl p-5 shadow-lg border-t-4 border-yellow-500 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-yellow-600 uppercase tracking-wider mb-1">Pendentes Pgto</p>
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $totalPendentes ?? 0 }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- Card Confirmados -->
                <div class="bg-white rounded-2xl p-5 shadow-lg border-t-4 border-green-500 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-green-600 uppercase tracking-wider mb-1">Confirmados</p>
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $totalConfirmados ?? 0 }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-check-double text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- Card Receita -->
                <div class="bg-white rounded-2xl p-5 shadow-lg border-t-4 border-teal-500 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-teal-600 uppercase tracking-wider mb-1">Receita</p>
                            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">R$ {{ number_format($valorTotalRecebido ?? 0, 2, ',', '.') }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-dollar-sign text-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs & Content Container --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden min-h-[500px]">
                
                {{-- Tabs Navigation (ESTILO "ABAS COM BORDA TOPO" + QUEBRA DE LINHA) --}}
                <div class="bg-slate-50 border-b border-slate-200 p-4">
                    <nav class="flex flex-wrap gap-2 justify-center sm:justify-start">
                        @php
                            // Removido 'repasse' do array para não aparecer na lista de abas, pois agora está no header
                            $tabs = [
                                'financeiro' => ['label' => 'Financeiro', 'icon' => 'fa-cash-register'],
                                'inscritos' => ['label' => 'Inscritos', 'icon' => 'fa-users'],
                                'percursos' => ['label' => 'Percursos', 'icon' => 'fa-route'],
                                'lotes_gerais' => ['label' => 'Lotes', 'icon' => 'fa-layer-group'],
                                'produtos' => ['label' => 'Produtos', 'icon' => 'fa-shirt'],
                                'cupons' => ['label' => 'Cupons', 'icon' => 'fa-ticket'],
                                'numeracao' => ['label' => 'Numeração', 'icon' => 'fa-list-ol'],
                                'relatorios' => ['label' => 'Relatórios', 'icon' => 'fa-file-lines'],
                                'resultados' => ['label' => 'Resultados', 'icon' => 'fa-stopwatch', 'special' => true],
                            ];
                        @endphp

                        @foreach($tabs as $key => $data)
                            <button @click="tab = '{{ $key }}'" 
                                    :class="{ 
                                        'bg-white text-orange-600 border-t-4 border-orange-600 shadow-md font-extrabold': tab === '{{ $key }}', 
                                        'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-800 border border-slate-200': tab !== '{{ $key }}'
                                    }" 
                                    class="group relative px-4 py-3 font-bold text-sm rounded-lg transition-all duration-200 flex items-center gap-2">
                                
                                {{-- Icone com cor condicional --}}
                                <i class="fa-solid {{ $data['icon'] }} opacity-80 group-hover:opacity-100 transition-colors"
                                   :class="{ 'text-orange-600': tab === '{{ $key }}', 'text-slate-400': tab !== '{{ $key }}' }"></i>
                                
                                {{ $data['label'] }}
                            </button>
                        @endforeach
                    </nav>
                </div>

                {{-- Messages (Success/Error) --}}
                @if (session('sucesso'))
                    <div class="m-6 p-4 text-sm text-green-700 bg-green-50 border-l-4 border-green-500 rounded-r-md shadow-sm flex items-center animate-fade-in">
                        <i class="fa-solid fa-circle-check mr-3 text-lg"></i>
                        {{ session('sucesso') }}
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="m-6 p-4 text-sm text-red-700 bg-red-50 border-l-4 border-red-500 rounded-r-md shadow-sm animate-fade-in">
                        <p class="font-bold flex items-center mb-1"><i class="fa-solid fa-circle-exclamation mr-2"></i> Erros encontrados:</p>
                        <ul class="list-disc list-inside ml-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Tab Content Areas --}}
                <div class="p-6 md:p-8 bg-white">
                    <div x-show="tab === 'financeiro'" style="display: none;" class="space-y-6 animate-fade-in">@include('organizador.eventos.partials.aba-financeiro')</div>
                    <div x-show="tab === 'inscritos'" style="display: none;" class="animate-fade-in">@include('organizador.eventos.partials.aba-inscritos')</div>
                    <div x-show="tab === 'percursos'" style="display: none;" class="space-y-6 animate-fade-in">@include('organizador.eventos.partials.aba-percursos')</div>
                    <div x-show="tab === 'lotes_gerais'" style="display: none;" class="space-y-6 animate-fade-in">@include('organizador.eventos.partials.aba-lotes-gerais')</div>
                    <div x-show="tab === 'produtos'" style="display: none;" class="space-y-6 animate-fade-in">@include('organizador.eventos.partials.aba-produtos')</div>
                    <div x-show="tab === 'cupons'" style="display: none;" class="space-y-6 animate-fade-in">@include('organizador.eventos.partials.aba-cupons')</div>
                    
                    {{-- Conteúdo do Repasse mantido e acessível via botão no header --}}
                    <div x-show="tab === 'repasse'" style="display: none;" class="animate-fade-in">@include('organizador.eventos.partials.aba-repasse')</div>
                    
                    <div x-show="tab === 'relatorios'" style="display: none;" class="animate-fade-in">@include('organizador.eventos.partials.aba-relatorios')</div>
                    
                    {{-- Conteúdo Check-in removido pois a aba foi removida --}}

                    {{-- Numeração (Card Moderno) --}}
                    <div x-show="tab === 'numeracao'" style="display: none;" class="animate-fade-in">
                        <div class="bg-gradient-to-br from-amber-50 to-white p-10 rounded-2xl border border-amber-100 text-center max-w-3xl mx-auto">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-white shadow-md mb-6 text-amber-500">
                                <i class="fa-solid fa-list-ol text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-extrabold text-slate-800 mb-2">Numeração de Atletas</h3>
                            <p class="text-slate-500 mb-8">Defina faixas de numeração por categoria, ordem alfabética ou data de inscrição e gere os números automaticamente.</p>
                            <a href="{{ route('organizador.eventos.numeracao', $evento) }}" class="inline-flex items-center px-8 py-4 bg-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-500/30 hover:bg-amber-600 hover:-translate-y-1 transition-all">
                                <i class="fa-solid fa-gear mr-3"></i> Configurar Numeração
                            </a>
                        </div>
                    </div>

                    {{-- Resultados (Card Moderno) --}}
                    <div x-show="tab === 'resultados'" style="display: none;" class="animate-fade-in">
                        <div class="bg-gradient-to-br from-blue-50 to-white p-10 rounded-2xl border border-blue-100 text-center max-w-3xl mx-auto">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-white shadow-md mb-6 text-blue-600">
                                <i class="fa-solid fa-stopwatch-20 text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-extrabold text-slate-800 mb-2">Apuração de Resultados</h3>
                            <p class="text-slate-500 mb-8">Acesse o módulo de cronometragem para inserir tempos, calcular posições e gerenciar o ranking.</p>
                            <a href="{{ route('organizador.eventos.resultados.show', $evento) }}" class="inline-flex items-center px-8 py-4 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:-translate-y-1 transition-all">
                                <i class="fa-solid fa-play-circle mr-3"></i> Iniciar Apuração
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL WHATSAPP (Modernizado) --}}
    <div x-show="showWhatsappModal" 
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="showWhatsappModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/75 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                 @click="showWhatsappModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div x-show="showWhatsappModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">
                
                <div class="bg-gradient-to-r from-teal-500 to-green-500 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center">
                        <i class="fa-brands fa-whatsapp text-2xl mr-3"></i>
                        Compartilhar no WhatsApp
                    </h3>
                    <button @click="showWhatsappModal = false" class="text-white/80 hover:text-white transition-colors focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="bg-white px-6 pt-5 pb-6">
                    <div class="mt-2">
                        <template x-if="loadingWhatsapp">
                            <div class="flex flex-col items-center justify-center py-12">
                                <i class="fa-solid fa-circle-notch fa-spin text-4xl text-teal-500 mb-4"></i>
                                <p class="text-slate-500 font-medium">Gerando texto de divulgação...</p>
                            </div>
                        </template>
                        <template x-if="!loadingWhatsapp">
                            <div>
                                <label for="whatsapp-text" class="block text-sm font-bold text-slate-700 mb-2">Mensagem Pronta:</label>
                                <div class="relative">
                                    <textarea id="whatsapp-text" x-model="textoWhatsapp" rows="12" readonly class="w-full border-slate-300 rounded-xl shadow-inner bg-slate-50 focus:border-teal-500 focus:ring-teal-500 text-sm font-mono p-4"></textarea>
                                    <div class="absolute bottom-3 right-3 text-xs text-slate-400 pointer-events-none">
                                        Somente leitura
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-100 gap-2">
                    <button @click="shareOnWhatsApp()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-3 bg-green-600 text-base font-bold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-share-from-square mr-2"></i> Abrir WhatsApp
                    </button>
                    <button @click="copyToClipboard()"
                            :class="{ 'bg-slate-800 text-white': feedbackCopiado, 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50': !feedbackCopiado }"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border px-5 py-3 text-base font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        <span x-show="!feedbackCopiado" class="flex items-center"><i class="fa-regular fa-copy mr-2"></i> Copiar Texto</span>
                        <span x-show="feedbackCopiado" class="flex items-center"><i class="fa-solid fa-check mr-2"></i> Copiado!</span>
                    </button>
                    <button @click="showWhatsappModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-5 py-3 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { bg-slate-100; }
        .custom-scrollbar::-webkit-scrollbar-thumb { bg-slate-300; rounded-full; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { bg-slate-400; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>