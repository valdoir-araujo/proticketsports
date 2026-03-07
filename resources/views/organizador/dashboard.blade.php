<x-app-layout>
    {{-- Header Moderno com Identidade Visual (Azul Marinho + Laranja Forte) --}}
    {{-- Mudança: Gradiente agora vai de Slate-900 para Blue-900, criando um azul marinho profundo --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
        
        {{-- Padrão de Pintinhas Laranjadas (Dots Pattern) --}}
        {{-- Mudança: Aumentei a opacidade de 0.15 para 0.30 para os pontos ficarem "mais fortes" --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none" 
             style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;">
        </div>

        {{-- Background Effects (Luzes Ambiente) --}}
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/30 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-600/20 blur-3xl pointer-events-none mix-blend-screen"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-2 relative z-10">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold mb-2 backdrop-blur-md shadow-lg">
                    <span class="w-2 h-2 rounded-full bg-orange-500 mr-2 animate-pulse shadow-[0_0_10px_rgba(249,115,22,0.7)]"></span>
                    Painel do Organizador
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight drop-shadow-md">
                    Olá, {{ explode(' ', $organizador->name)[0] }}! 👋
                </h1>
                <p class="text-blue-100 text-lg max-w-2xl font-medium">
                    Gerencie a <span class="text-white font-bold border-b-2 border-orange-500/50">{{ $organizacao->nome ?? 'sua organização' }}</span> e impulsione seus eventos.
                </p>
            </div>

            {{-- Botão Voltar (Condicional) --}}
            @if(request()->has('org_id'))
                <a href="{{ route('organizador.dashboard') }}" class="group flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-all duration-300 text-white font-bold text-sm backdrop-blur-md shadow-lg hover:shadow-white/10 hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-left mr-2 transition-transform group-hover:-translate-x-1 text-orange-400"></i>
                    Voltar à Seleção
                </a>
            @endif
        </div>
    </div>

    {{-- Conteúdo Principal (Sobreposto ao Header) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 space-y-8 pb-12 relative z-10">
        
        {{-- Grid de KPIs (Estatísticas) com Cores de Destaque --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Card Campeonatos (Indigo Theme) -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-indigo-100/50 border-t-4 border-indigo-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-125"></div>
                <div class="relative flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1">Campeonatos</p>
                        <h3 class="text-3xl font-extrabold text-slate-800">{{ $totalCampeonatos ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-all duration-300">
                        <i class="fa-solid fa-trophy text-lg"></i>
                    </div>
                </div>
                <div class="relative mt-4 flex items-center text-xs font-medium text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 mr-2"></span>
                    Circuitos Ativos
                </div>
            </div>

            <!-- Card Eventos (Blue Theme) -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border-t-4 border-blue-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-125"></div>
                <div class="relative flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Eventos Realizados</p>
                        <h3 class="text-3xl font-extrabold text-slate-800">{{ $totalEventos }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-all duration-300">
                        <i class="fa-solid fa-calendar-days text-lg"></i>
                    </div>
                </div>
                <div class="relative mt-4 flex items-center text-xs font-medium text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-blue-400 mr-2"></span>
                    Competições
                </div>
            </div>

            <!-- Card Inscritos (Emerald Theme) -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-emerald-100/50 border-t-4 border-emerald-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-125"></div>
                <div class="relative flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-1">Inscrições</p>
                        <div class="flex items-baseline gap-1">
                            <h3 class="text-3xl font-extrabold text-slate-800">{{ $totalInscritosConfirmados ?? 0 }}</h3>
                            <span class="text-sm text-slate-400 font-semibold">/ {{ $totalInscritos ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-all duration-300">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                </div>
                <div class="relative mt-4">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-slate-500">Taxa de Confirmação</span>
                        @php 
                            $percent = ($totalInscritos > 0) ? ($totalInscritosConfirmados / $totalInscritos) * 100 : 0;
                        @endphp
                        <span class="font-bold text-emerald-600">{{ round($percent) }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-1.5 rounded-full shadow-[0_0_10px_rgba(16,185,129,0.5)]" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Card Faturamento (Amber Theme) -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-amber-100/50 border-t-4 border-amber-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-125"></div>
                <div class="relative flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-1">Faturamento</p>
                        <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">R$ {{ number_format($faturamento ?? 0, 2, ',', '.') }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-all duration-300">
                        <i class="fa-solid fa-dollar-sign text-lg"></i>
                    </div>
                </div>
                <div class="relative mt-4 flex items-center text-xs font-medium text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-amber-400 mr-2"></span>
                    Confirmado em Caixa
                </div>
            </div>
        </div>

        {{-- Ações Rápidas --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('organizador.financeiro.index') }}" class="flex items-center p-4 bg-white border border-slate-100 rounded-xl shadow-sm hover:shadow-md hover:border-teal-300 transition-all group relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-500"></div>
                <div class="w-12 h-12 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center mr-4 group-hover:bg-teal-500 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-money-check-dollar text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-teal-700 transition-colors">Financeiro</h4>
                    <p class="text-xs text-slate-500 font-medium">Gerenciar pagamentos</p>
                </div>
                <i class="fa-solid fa-arrow-right ml-auto text-slate-300 group-hover:text-teal-500 group-hover:translate-x-1 transition-all"></i>
            </a>

            <a href="{{ route('organizador.campeonatos.create') }}" class="flex items-center p-4 bg-white border border-slate-100 rounded-xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all group relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
                <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mr-4 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-trophy text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-indigo-700 transition-colors">Campeonato</h4>
                    <p class="text-xs text-slate-500 font-medium">Criar novo circuito</p>
                </div>
                <i class="fa-solid fa-arrow-right ml-auto text-slate-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
            </a>

            <a href="{{ route('organizador.eventos.create') }}" class="flex items-center p-4 bg-white border border-slate-100 rounded-xl shadow-sm hover:shadow-md hover:border-orange-300 transition-all group relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500"></div>
                <div class="w-12 h-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center mr-4 group-hover:bg-orange-500 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-calendar-plus text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-orange-700 transition-colors">Evento</h4>
                    <p class="text-xs text-slate-500 font-medium">Criar evento avulso</p>
                </div>
                <i class="fa-solid fa-arrow-right ml-auto text-slate-300 group-hover:text-orange-500 group-hover:translate-x-1 transition-all"></i>
            </a>
        </div>

        {{-- Layout Principal: Duas Colunas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- COLUNA DE CAMPEONATOS -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden">
                <div class="p-5 border-b border-slate-50 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center">
                        <span class="w-2 h-8 bg-indigo-500 rounded-full mr-3 shadow-sm"></span>
                        Campeonatos & Circuitos
                    </h3>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full border border-indigo-100">
                        {{ count($campeonatosAtivos ?? []) }} ATIVOS
                    </span>
                </div>
                
                <div class="p-4 flex-grow space-y-3">
                    @forelse($campeonatosAtivos ?? [] as $campeonato)
                        <div class="group relative flex items-center p-3 rounded-xl bg-white border border-slate-100 hover:border-indigo-200 hover:shadow-md hover:shadow-indigo-50 transition-all duration-300">
                            
                            {{-- Imagem --}}
                            <div class="relative w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-100 shadow-sm group-hover:ring-2 ring-indigo-100 transition-all">
                                @if($campeonato->logo_url)
                                    <img src="{{ asset('storage/' . $campeonato->logo_url) }}" alt="Logo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-50">
                                        <i class="fa-solid fa-trophy text-2xl group-hover:text-indigo-400 transition-colors"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Infos --}}
                            <div class="ml-4 flex-grow min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 truncate pr-2 group-hover:text-indigo-600 transition-colors">
                                    {{ $campeonato->nome }}
                                </h4>
                                <div class="flex items-center mt-1.5 space-x-3 text-xs font-medium text-slate-500">
                                    <span class="flex items-center bg-slate-50 px-2 py-0.5 rounded text-slate-600">
                                        <i class="fa-regular fa-calendar mr-1.5 text-indigo-400"></i>
                                        {{ $campeonato->ano }}
                                    </span>
                                    <span class="flex items-center bg-slate-50 px-2 py-0.5 rounded text-slate-600">
                                        <i class="fa-solid fa-flag-checkered mr-1.5 text-indigo-400"></i>
                                        {{ $campeonato->eventos_count }} Etapas
                                    </span>
                                </div>
                            </div>

                            {{-- Botão Ação --}}
                            <div class="ml-2 z-10">
                                <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-50 text-slate-400 hover:text-white hover:bg-indigo-500 transition-all shadow-sm group-hover:translate-x-1">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </div>

                            {{-- Link Absolute --}}
                            <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="absolute inset-0 z-0 rounded-xl focus:outline-none"></a>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-48 text-center px-4 border-2 border-dashed border-slate-200 rounded-xl m-2">
                            <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center mb-3 text-indigo-300">
                                <i class="fa-solid fa-trophy-slash text-2xl"></i>
                            </div>
                            <p class="text-slate-600 font-bold">Sem campeonatos</p>
                            <p class="text-xs text-slate-400 mt-1 mb-4 max-w-[200px]">Crie circuitos para agrupar seus eventos e gerar rankings.</p>
                            <a href="{{ route('organizador.campeonatos.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                                + Criar Primeiro Campeonato
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- COLUNA DE EVENTOS AVULSOS -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden">
                <div class="p-5 border-b border-slate-50 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center">
                        <span class="w-2 h-8 bg-orange-500 rounded-full mr-3 shadow-sm"></span>
                        Próximos Eventos
                    </h3>
                    <span class="text-xs font-bold text-orange-600 bg-orange-50 px-3 py-1.5 rounded-full border border-orange-100">
                        {{ count($eventosAvulsos ?? []) }} AGENDADOS
                    </span>
                </div>
                
                <div class="p-4 flex-grow space-y-3">
                    @forelse($eventosAvulsos as $evento)
                        <div class="group relative flex items-center p-3 rounded-xl bg-white border border-slate-100 hover:border-orange-200 hover:shadow-md hover:shadow-orange-50 transition-all duration-300">
                            
                            {{-- Imagem/Thumbnail --}}
                            <div class="relative w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-100 shadow-sm group-hover:ring-2 ring-orange-100 transition-all">
                                @if($evento->thumbnail_url)
                                    <img src="{{ asset('storage/' . $evento->thumbnail_url) }}" alt="Thumbnail" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-50">
                                        <i class="fa-solid fa-calendar-days text-2xl group-hover:text-orange-400 transition-colors"></i>
                                    </div>
                                @endif
                                
                                {{-- Data Badge (Overlay) --}}
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent pt-2 pb-0.5 backdrop-blur-[1px]">
                                    <div class="text-white text-[10px] font-bold text-center">
                                        {{ $evento->data_evento->format('d/m') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Infos --}}
                            <div class="ml-4 flex-grow min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 truncate pr-2 group-hover:text-orange-600 transition-colors">
                                    {{ $evento->nome }}
                                </h4>
                                <div class="flex items-center mt-1.5 space-x-3 text-xs font-medium text-slate-500">
                                    <span class="flex items-center bg-slate-50 px-2 py-0.5 rounded text-slate-600">
                                        <i class="fa-regular fa-clock mr-1.5 text-orange-400"></i>
                                        {{ $evento->data_evento->format('H:i') }}
                                    </span>
                                    @if($evento->local)
                                    <span class="flex items-center truncate max-w-[120px] bg-slate-50 px-2 py-0.5 rounded text-slate-600">
                                        <i class="fa-solid fa-location-dot mr-1.5 text-orange-400"></i>
                                        {{ $evento->local }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Botão Ação --}}
                            <div class="ml-2 z-10">
                                <a href="{{ route('organizador.eventos.show', $evento) }}" class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-50 text-slate-400 hover:text-white hover:bg-orange-500 transition-all shadow-sm group-hover:translate-x-1">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </div>

                            <a href="{{ route('organizador.eventos.show', $evento) }}" class="absolute inset-0 z-0 rounded-xl focus:outline-none"></a>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-48 text-center px-4 border-2 border-dashed border-slate-200 rounded-xl m-2">
                            <div class="w-14 h-14 bg-orange-50 rounded-full flex items-center justify-center mb-3 text-orange-300">
                                <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                            </div>
                            <p class="text-slate-600 font-bold">Agenda vazia</p>
                            <p class="text-xs text-slate-400 mt-1 mb-4 max-w-[200px]">Crie eventos avulsos que não pertencem a um campeonato.</p>
                            <a href="{{ route('organizador.eventos.create') }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg text-xs font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-200">
                                + Criar Evento
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>