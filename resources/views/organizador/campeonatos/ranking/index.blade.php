<x-app-layout>
    {{-- CABEÇALHO HERO MODERNIZADO --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-32 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                
                {{-- Título e Breadcrumb --}}
                <div class="text-white z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="inline-flex items-center text-xs font-bold text-blue-200 hover:text-white transition-colors bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm border border-white/10 hover:bg-white/20">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Campeonato
                        </a>
                        <span class="text-slate-400 text-xs">•</span>
                        <span class="text-xs text-yellow-400 font-bold uppercase tracking-wider">Resultados</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md flex items-center gap-3">
                        <i class="fa-solid fa-trophy text-yellow-500"></i> Ranking Geral
                    </h2>
                    <p class="text-blue-100 mt-1 font-medium text-lg opacity-90">
                        {{ $campeonato->nome }} <span class="text-slate-400">|</span> {{ $campeonato->ano }}
                    </p>
                </div>

                {{-- Botão Exportar (Placeholder Visual) --}}
                <div class="z-10">
                    <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2.5 bg-white text-slate-700 rounded-xl text-sm font-bold shadow-lg hover:bg-slate-50 transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-print mr-2 text-indigo-600"></i> Imprimir / PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="relative z-20 -mt-24 pb-12" x-data="{ tab: 'individual' }">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden min-h-[600px]">
                
                {{-- Navegação das Abas --}}
                <div class="bg-slate-50 border-b border-slate-200 px-6 pt-4">
                    <nav class="flex space-x-4" aria-label="Tabs">
                        <button @click="tab = 'individual'" 
                                :class="{ 'border-orange-500 text-orange-600 bg-white shadow-sm rounded-t-lg': tab === 'individual', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-t-lg': tab !== 'individual' }" 
                                class="group relative px-6 py-3 font-bold text-sm border-t-4 transition-all duration-200 flex items-center gap-2">
                            <i class="fa-solid fa-person-biking" :class="{ 'text-orange-500': tab === 'individual', 'text-slate-400': tab !== 'individual' }"></i>
                            Classificação Individual
                        </button>
                        <button @click="tab = 'equipes'" 
                                :class="{ 'border-orange-500 text-orange-600 bg-white shadow-sm rounded-t-lg': tab === 'equipes', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-t-lg': tab !== 'equipes' }" 
                                class="group relative px-6 py-3 font-bold text-sm border-t-4 transition-all duration-200 flex items-center gap-2">
                            <i class="fa-solid fa-people-group" :class="{ 'text-orange-500': tab === 'equipes', 'text-slate-400': tab !== 'equipes' }"></i>
                            Classificação por Equipes
                        </button>
                    </nav>
                </div>

                {{-- CRUCIAL: ORDENAÇÃO DAS ETAPAS PARA CABEÇALHO E DADOS --}}
                @php
                    // Garante que a ordem cronológica seja respeitada em TODAS as tabelas
                    $etapasOrdenadas = $etapas->sortBy('data_evento');
                @endphp

                {{-- ABA 1: CLASSIFICAÇÃO INDIVIDUAL --}}
                <div x-show="tab === 'individual'" class="bg-white p-0">
                    {{-- Usa $rankingAgrupado para a estrutura hierárquica --}}
                    @php $dadosRanking = $rankingAgrupado ?? []; @endphp

                    @if(count($dadosRanking) > 0)
                        <div class="p-6 space-y-8">
                            {{-- Loop Hierárquico: Percurso -> Gênero -> Categoria --}}
                            @foreach($dadosRanking as $percursoNome => $generos)
                                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                                    {{-- Cabeçalho do Percurso --}}
                                    <div class="bg-slate-800 text-white px-6 py-3 flex items-center gap-3">
                                        <i class="fa-solid fa-route text-orange-500"></i>
                                        <h3 class="font-bold text-lg">{{ $percursoNome }}</h3>
                                    </div>

                                    @foreach($generos as $generoNome => $categorias)
                                        <div class="bg-slate-50 border-b border-slate-100 last:border-0">
                                            @foreach($categorias as $categoriaNome => $atletas)
                                                
                                                {{-- Cabeçalho da Categoria --}}
                                                <div class="px-6 py-3 bg-indigo-50/50 flex items-center justify-between border-t border-indigo-100 first:border-0">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-solid fa-layer-group text-indigo-600"></i>
                                                        <span class="font-bold text-indigo-900 text-base">{{ $categoriaNome }}</span>
                                                        <span class="text-xs uppercase tracking-wide text-slate-500 ml-2">({{ $generoNome }})</span>
                                                    </div>
                                                    <span class="text-xs font-bold text-indigo-600 bg-white px-2 py-0.5 rounded-full border border-indigo-200">
                                                        {{ count($atletas) }} atletas
                                                    </span>
                                                </div>

                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-slate-100">
                                                        <thead class="bg-white">
                                                            <tr>
                                                                <th class="px-4 py-2 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider w-12">Pos</th>
                                                                <th class="px-4 py-2 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Atleta</th>
                                                                
                                                                {{-- LOOP CABEÇALHO (Ordenado) --}}
                                                                @foreach($etapasOrdenadas as $etapa)
                                                                    <th class="px-4 py-2 text-center w-24 border-l border-slate-50" title="{{ $etapa->nome }}">
                                                                        <div class="flex flex-col">
                                                                            <span class="text-[10px] font-black text-slate-700 uppercase tracking-wider">Etapa {{ $loop->iteration }}</span>
                                                                            <span class="text-[9px] font-medium text-slate-400">{{ $etapa->data_evento->format('d/m') }}</span>
                                                                        </div>
                                                                    </th>
                                                                @endforeach
                                                                
                                                                <th class="px-4 py-2 text-center text-[10px] font-black text-indigo-600 uppercase tracking-wider w-20 bg-indigo-50/30 border-l border-indigo-100">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-slate-50">
                                                            @foreach($atletas as $posicao => $dados)
                                                                {{-- Proteção para garantir que $dados é array --}}
                                                                @if(!is_array($dados) && !is_object($dados)) @continue @endif

                                                                <tr class="hover:bg-slate-50 transition-colors">
                                                                    <td class="px-4 py-2 text-center">
                                                                        @if($loop->iteration <= 3)
                                                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold shadow-sm
                                                                                {{ $loop->iteration == 1 ? 'bg-yellow-400' : ($loop->iteration == 2 ? 'bg-slate-400' : 'bg-orange-400') }}">
                                                                                {{ $loop->iteration }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-sm font-bold text-slate-500">{{ $loop->iteration }}º</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2">
                                                                        <div class="flex flex-col">
                                                                            <span class="font-bold text-slate-800 text-sm truncate max-w-[200px]">
                                                                                {{ $dados['atleta']->user->name ?? 'Nome Indisponível' }}
                                                                            </span>
                                                                            @if(isset($dados['atleta']->equipe))
                                                                                <span class="text-[10px] text-slate-500 truncate max-w-[200px]">{{ $dados['atleta']->equipe->nome }}</span>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                    
                                                                    {{-- LOOP DADOS (Sincronizado com Cabeçalho) --}}
                                                                    @foreach($etapasOrdenadas as $etapa)
                                                                        <td class="px-4 py-2 text-center border-l border-slate-50">
                                                                            @if(isset($dados['pontos_por_etapa'][$etapa->id]) && $dados['pontos_por_etapa'][$etapa->id] > 0)
                                                                                <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                                                                                    {{ $dados['pontos_por_etapa'][$etapa->id] }}
                                                                                </span>
                                                                            @else
                                                                                <span class="text-xs text-slate-300">-</span>
                                                                            @endif
                                                                        </td>
                                                                    @endforeach
                                                                    
                                                                    <td class="px-4 py-2 text-center border-l border-indigo-50 bg-indigo-50/30">
                                                                        <span class="text-sm font-black text-indigo-700">{{ $dados['pontos_totais'] ?? 0 }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center text-slate-500">
                            <i class="fa-solid fa-person-circle-question text-4xl mb-3 text-slate-300"></i>
                            <p>Nenhum resultado individual calculado ainda.</p>
                        </div>
                    @endif
                </div>

                {{-- ABA 2: CLASSIFICAÇÃO POR EQUIPES --}}
                <div x-show="tab === 'equipes'" style="display: none;" class="bg-white p-0">
                    {{-- CORREÇÃO: Verifica se $rankingEquipes existe e não é vazio --}}
                    @if(isset($rankingEquipes) && count($rankingEquipes) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider w-20">Pos</th>
                                        <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider w-1/4">Equipe</th>
                                        <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Integrantes</th>
                                        
                                        {{-- Cabeçalho Equipes (Ordenado) --}}
                                        @foreach($etapasOrdenadas as $etapa)
                                            <th class="px-4 py-4 text-center w-24 border-l border-slate-200" title="{{ $etapa->nome }}">
                                                <div class="flex flex-col">
                                                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Etapa {{ $loop->iteration }}</span>
                                                    <span class="text-[9px] font-medium text-slate-400">{{ $etapa->data_evento->format('d/m') }}</span>
                                                </div>
                                            </th>
                                        @endforeach
                                        
                                        <th class="px-6 py-4 text-center text-xs font-black text-slate-800 uppercase tracking-wider w-32 bg-slate-100 border-l border-slate-200">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach ($rankingEquipes as $dadosEquipe)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 text-center">
                                                @if($loop->iteration <= 3)
                                                    <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center text-white font-bold shadow-md
                                                        {{ $loop->iteration == 1 ? 'bg-yellow-400' : ($loop->iteration == 2 ? 'bg-slate-400' : 'bg-orange-400') }}">
                                                        {{ $loop->iteration }}
                                                    </div>
                                                @else
                                                    <span class="text-sm font-bold text-slate-600">{{ $loop->iteration }}º</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                {{-- CORREÇÃO DE SEGURANÇA: Verifica se a equipe existe --}}
                                                <div class="text-sm font-bold text-slate-800">
                                                    {{ isset($dadosEquipe['equipe']) && $dadosEquipe['equipe'] ? \Illuminate\Support\Str::title(mb_strtolower($dadosEquipe['equipe']->nome)) : 'Equipe Removida' }}
                                                </div>
                                                @if(isset($dadosEquipe['equipe']) && $dadosEquipe['equipe']->cidade)
                                                    <div class="text-xs text-slate-400">{{ $dadosEquipe['equipe']->cidade->nome ?? $dadosEquipe['equipe']->cidade }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-wrap gap-1">
                                                    @if(isset($dadosEquipe['equipe']) && $dadosEquipe['equipe'])
                                                        @forelse($dadosEquipe['equipe']->users ?? [] as $atleta)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                                <i class="fa-solid fa-user text-[10px] mr-1 text-slate-400"></i>
                                                                {{ explode(' ', $atleta->name)[0] }}
                                                            </span>
                                                        @empty
                                                            <span class="text-xs text-slate-400 italic">Sem integrantes</span>
                                                        @endforelse
                                                    @else
                                                        <span class="text-xs text-slate-300">-</span>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            {{-- Dados Equipes (Mesma Ordem do Cabeçalho) --}}
                                            @foreach($etapasOrdenadas as $etapa)
                                                <td class="px-4 py-4 text-center border-l border-slate-50">
                                                    @if(isset($dadosEquipe['pontos_por_etapa'][$etapa->id]) && $dadosEquipe['pontos_por_etapa'][$etapa->id] > 0)
                                                        <span class="text-sm font-bold text-slate-700">
                                                            {{ $dadosEquipe['pontos_por_etapa'][$etapa->id] }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-slate-300">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            
                                            <td class="px-6 py-4 text-center border-l border-indigo-50 bg-indigo-50/30">
                                                <span class="text-base font-black text-indigo-700">{{ $dadosEquipe['pontos_totais'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center text-slate-500">
                            <i class="fa-solid fa-users-slash text-4xl mb-3 text-slate-300"></i>
                            <p>Nenhum resultado por equipes calculado ainda.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>