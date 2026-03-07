<x-app-layout>
    {{-- CABEÇALHO HERO (MODERNIZADO) --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-32 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                
                {{-- Título e Breadcrumb --}}
                <div class="text-white z-10">
                    <div class="flex items-center gap-3 mb-2 text-blue-200 text-sm font-medium">
                        {{-- Botão Voltar com Contexto da Organização --}}
                        <a href="{{ route('organizador.dashboard', ['org_id' => $campeonato->organizacao_id]) }}" class="hover:text-white transition-colors flex items-center">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Painel
                        </a>
                        <span class="opacity-50">/</span>
                        <span class="text-white">Gerenciar Campeonato</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        {{ $campeonato->nome }} <span class="text-orange-500 font-light">{{ $campeonato->ano }}</span>
                    </h2>
                </div>

                {{-- Barra de Ações --}}
                <div class="flex flex-wrap items-center gap-3 z-10">
                    <a href="{{ route('organizador.campeonatos.regras.index', $campeonato) }}" class="inline-flex items-center px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-sm font-bold backdrop-blur-md border border-white/10 transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-list-ol mr-2"></i> Regras
                    </a>
                    
                    <a href="{{ route('organizador.campeonatos.ranking.index', $campeonato) }}" class="inline-flex items-center px-4 py-2.5 bg-yellow-500 hover:bg-yellow-400 text-slate-900 rounded-xl text-sm font-bold shadow-lg shadow-yellow-500/30 transition-all hover:-translate-y-0.5 border border-yellow-400">
                        <i class="fa-solid fa-trophy mr-2"></i> Ranking
                    </a>

                    <a href="{{ route('organizador.campeonatos.edit', $campeonato) }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-900/50 transition-all hover:-translate-y-0.5 border border-blue-500/50">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20 pb-12">
        
        {{-- Card: Detalhes do Campeonato --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 md:p-8 mb-8 flex flex-col md:flex-row items-start gap-8">
            {{-- Logo --}}
            <div class="w-full md:w-auto flex-shrink-0">
                <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl bg-slate-50 border border-slate-200 p-2 shadow-sm flex items-center justify-center overflow-hidden relative group">
                    @if($campeonato->logo_url)
                        <img src="{{ asset('storage/' . $campeonato->logo_url) }}" alt="Logo" class="w-full h-full object-cover rounded-xl group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="text-slate-300 flex flex-col items-center justify-center">
                            <i class="fa-solid fa-trophy text-4xl mb-2"></i>
                            <span class="text-xs font-bold uppercase">Sem Logo</span>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Informações --}}
            <div class="flex-grow">
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Sobre o Campeonato</h3>
                <p class="text-slate-600 leading-relaxed text-sm md:text-base">
                    {{ $campeonato->descricao ?? 'Nenhuma descrição fornecida para este campeonato.' }}
                </p>
                
                <div class="mt-6 flex flex-wrap gap-4 text-sm text-slate-500">
                    <div class="flex items-center bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <i class="fa-regular fa-calendar-days mr-2 text-indigo-500"></i>
                        <span class="font-medium text-slate-700">Temporada {{ $campeonato->ano }}</span>
                    </div>
                    <div class="flex items-center bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <i class="fa-solid fa-flag-checkered mr-2 text-orange-500"></i>
                        <span class="font-medium text-slate-700">{{ $campeonato->eventos->count() }} Etapas cadastradas</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção: Lista de Etapas --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 flex items-center">
                        <span class="w-2 h-6 bg-orange-500 rounded-full mr-3 shadow-sm"></span>
                        Etapas do Campeonato
                    </h3>
                    <p class="text-sm text-slate-500 mt-1 ml-5">Gerencie os eventos que compõem este circuito.</p>
                </div>
                
                <a href="{{ route('organizador.eventos.create', ['campeonato_id' => $campeonato->id, 'org_id' => $campeonato->organizacao_id]) }}" class="inline-flex items-center px-5 py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-sm font-bold shadow-md shadow-orange-500/30 transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus mr-2"></i> Adicionar Etapa
                </a>
            </div>

            @if($campeonato->eventos->isEmpty())
                <div class="text-center py-16 px-6">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 border border-slate-100">
                        <i class="fa-regular fa-calendar-xmark text-4xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-700">Nenhuma etapa encontrada</h4>
                    <p class="text-slate-500 mt-2 max-w-md mx-auto">Este campeonato ainda não possui etapas cadastradas. Clique no botão acima para adicionar a primeira etapa.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-bold border-b border-slate-100">
                                <th class="px-6 py-4">Etapa / Nome</th>
                                <th class="px-6 py-4">Data</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($campeonato->eventos as $evento)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mr-3 font-bold text-sm border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors shadow-sm">
                                                {{ $loop->iteration }}
                                            </div>
                                            <span class="font-bold text-slate-700 group-hover:text-indigo-700 transition-colors text-base">{{ $evento->nome }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-calendar text-slate-400"></i>
                                            {{ $evento->data_evento->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClasses = [
                                                'publicado' => 'bg-green-100 text-green-700 border-green-200',
                                                'inscricoes_abertas' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'encerrado' => 'bg-slate-100 text-slate-600 border-slate-200',
                                                'concluido' => 'bg-slate-100 text-slate-600 border-slate-200',
                                                'rascunho' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            ];
                                            $statusLabels = [
                                                'publicado' => 'Publicado',
                                                'inscricoes_abertas' => 'Inscrições Abertas',
                                                'encerrado' => 'Encerrado',
                                                'concluido' => 'Concluído',
                                                'rascunho' => 'Rascunho',
                                            ];
                                            $classe = $statusClasses[$evento->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                            $label = $statusLabels[$evento->status] ?? ucfirst(str_replace('_', ' ', $evento->status));
                                        @endphp
                                        
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $classe }}">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ str_replace(['bg-100', 'text-700', 'border-200'], ['bg-500', '', ''], $classe) }} opacity-70"></span>
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('organizador.eventos.show', $evento) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:text-orange-600 hover:border-orange-200 hover:bg-orange-50 transition-all shadow-sm group/btn">
                                            Gerenciar
                                            <i class="fa-solid fa-chevron-right ml-2 text-xs transition-transform group-hover/btn:translate-x-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>