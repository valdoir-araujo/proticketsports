<x-app-layout>
    {{-- TinyMCE para o regulamento (texto formatado) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>

    {{-- Dados para o componente Alpine (evita parsing de objeto gigante no atributo) --}}
    <script>
        (function registerEventoShow() {
            function register() {
                Alpine.data('eventoShow', function() {
                return {
                    tab: 'inscritos',
                    init: function() {
                        this.tab = this.$el.getAttribute('data-initial-tab') || 'inscritos';
                        var validTabs = ['financeiro', 'inscritos', 'percursos', 'lotes_gerais', 'produtos', 'cupons', 'contatos', 'regulamento', 'repasse', 'numeracao', 'formas_pgto', 'resultados', 'relatorios'];
                        var urlParams = new URLSearchParams(window.location.search);
                        var tabFromUrl = urlParams.get('tab');
                        var hash = window.location.hash.substring(1);
                        if (tabFromUrl && validTabs.indexOf(tabFromUrl) >= 0) this.tab = tabFromUrl;
                        else if (hash && validTabs.indexOf(hash) >= 0) this.tab = hash;
                        if (urlParams.has('lancamentosPage')) this.tab = 'financeiro';
                        if (urlParams.has('inscritosPage')) this.tab = 'inscritos';
                        var self = this;
                        this.$watch('tab', function(value) {
                            var newUrl = new URL(window.location);
                            newUrl.searchParams.delete('tab');
                            newUrl.hash = value;
                            window.history.replaceState({}, '', newUrl);
                            if (value === 'regulamento') self.$nextTick(function() { window.dispatchEvent(new CustomEvent('regulamento-tab-visible')); });
                        });
                        if (this.tab === 'regulamento') this.$nextTick(function() { window.dispatchEvent(new CustomEvent('regulamento-tab-visible')); });
                    }
                };
            });
            }
            if (window.Alpine) register(); else document.addEventListener('alpine:init', register);
        })();
    </script>

    {{-- Main Alpine Component --}}
    <div data-initial-tab="{{ session('tab', 'inscritos') }}"
         x-data="eventoShow()">

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
                                'contatos' => ['label' => 'Contatos', 'icon' => 'fa-address-card'],
                                'regulamento' => ['label' => 'Regulamento', 'icon' => 'fa-file-contract'],
                                'numeracao' => ['label' => 'Numeração', 'icon' => 'fa-list-ol'],
                                'relatorios' => ['label' => 'Relatórios', 'icon' => 'fa-file-lines'],
                                'formas_pgto' => ['label' => 'Formas Pgto', 'icon' => 'fa-brands fa-pix'],
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
                    <div x-show="tab === 'contatos'" style="display: none;" class="space-y-6 animate-fade-in">@include('organizador.eventos.partials.aba-contatos')</div>
                    <div x-show="tab === 'regulamento'" style="display: none;" class="space-y-6 animate-fade-in">@include('organizador.eventos.partials.aba-regulamento')</div>
                    
                    {{-- Conteúdo do Repasse mantido e acessível via botão no header --}}
                    <div x-show="tab === 'repasse'" style="display: none;" class="animate-fade-in">@include('organizador.eventos.partials.aba-repasse')</div>
                    
                    <div x-show="tab === 'relatorios'" style="display: none;" class="animate-fade-in">@include('organizador.eventos.partials.aba-relatorios')</div>

                    {{-- Formas de Pagamento (PIX manual, chave, QR Code) --}}
                    <div x-show="tab === 'formas_pgto'" style="display: none;">@include('organizador.eventos.partials.aba-formas-pgto')</div>

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

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { bg-slate-100; }
        .custom-scrollbar::-webkit-scrollbar-thumb { bg-slate-300; rounded-full; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { bg-slate-400; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>