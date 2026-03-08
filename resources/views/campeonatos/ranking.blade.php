@extends('layouts.public')

@section('title', 'Ranking - ' . $campeonato->nome . ' - Proticketsports')

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    <header class="bg-gray-900 text-white py-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 flex flex-col md:flex-row md:items-center gap-6">
            @if($campeonato->logo_url)
                <div class="flex-shrink-0">
                    <img src="{{ asset('storage/' . $campeonato->logo_url) }}" alt="{{ $campeonato->nome }}" class="h-24 w-auto object-contain bg-white/10 rounded-lg p-2" onerror="this.style.display='none'">
                </div>
            @endif
            <div>
                <nav class="text-sm text-gray-400 mb-2">
                    <a href="{{ route('campeonatos.index') }}" class="hover:text-orange-400 transition-colors">Campeonatos</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('campeonatos.show', $campeonato) }}" class="hover:text-orange-400 transition-colors">{{ $campeonato->nome }}</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Ranking</span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight">Ranking do Campeonato</h1>
                <p class="text-gray-300 mt-1">Pontuação por etapa e total · {{ $campeonato->ano }} @if($campeonato->organizacao)· {{ $campeonato->organizacao->nome }}@endif</p>
                <a href="{{ route('campeonatos.show', $campeonato) }}" class="mt-2 inline-flex items-center gap-x-2 text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Voltar ao campeonato</span>
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{
             activeTab: 'atletas',
             searchTerm: '',
             searchEquipeTab: '',
             etapas: {{ Js::from($etapasParaRanking) }},
             rankingAtletas: {{ Js::from($rankingAtletasParaJs) }},
             rankingEquipes: {{ Js::from($rankingEquipesParaJs) }},
             showFilters: window.innerWidth >= 768,

             get filteredAtletas() {
                 if (!this.searchTerm) return this.rankingAtletas;
                 const t = this.searchTerm.toLowerCase();
                 return this.rankingAtletas.filter(a => (a.nome_atleta || '').toLowerCase().includes(t));
             },
             get filteredEquipes() {
                 if (!this.searchEquipeTab) return this.rankingEquipes;
                 const t = this.searchEquipeTab.toLowerCase();
                 return this.rankingEquipes.filter(e => (e.nome_equipe || '').toLowerCase().includes(t));
             },
             pontosEtapa(item, eventoId) {
                 const p = item.pontos_por_etapa || {};
                 return p[eventoId] != null ? p[eventoId] : '—';
             },
             formatName(str) {
                 if (!str) return '';
                 return str.toLowerCase().replace(/(?:^|\s)\S/g, a => a.toUpperCase());
             }
         }"
         @resize.window.debounce.300ms="if (window.innerWidth >= 768) showFilters = true">

        {{-- Barra de filtros e abas (sticky) — mesmo estilo da lista de inscritos --}}
        <section class="sticky top-[64px] z-30 bg-white shadow-md py-4 border-b">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-2 md:mb-4">
                <div class="flex flex-row gap-3 w-full md:w-auto">
                    <button @click="activeTab = 'atletas'"
                            :class="activeTab === 'atletas' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/40 border-transparent' : 'bg-white text-slate-600 border-gray-200 shadow-sm hover:border-orange-300 hover:text-orange-600 hover:bg-orange-50'"
                            class="flex-1 md:flex-none px-5 py-2.5 rounded-xl border font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-person-running"></i>
                        <span>Atletas</span>
                    </button>
                    <button @click="activeTab = 'equipes'"
                            :class="activeTab === 'equipes' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/40 border-transparent' : 'bg-white text-slate-600 border-gray-200 shadow-sm hover:border-orange-300 hover:text-orange-600 hover:bg-orange-50'"
                            class="flex-1 md:flex-none px-5 py-2.5 rounded-xl border font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-people-group"></i>
                        <span>Equipes</span>
                    </button>
                </div>
                <div class="md:hidden w-full">
                    <button @click="showFilters = !showFilters"
                            class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-slate-800 text-white font-bold text-sm rounded-xl shadow-md hover:bg-slate-700 transition-colors">
                        <i class="fa-solid text-base" :class="showFilters ? 'fa-xmark' : 'fa-magnifying-glass'"></i>
                        <span x-text="showFilters ? 'Ocultar Filtros' : 'Buscar / Filtrar'"></span>
                    </button>
                </div>
            </div>

            {{-- Filtro aba Atletas --}}
            <div x-show="activeTab === 'atletas' && showFilters"
                 x-transition
                 class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100 md:border-none md:pt-0 mt-2 md:mt-0">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-user text-gray-400"></i>
                    </div>
                    <input type="text" x-model.debounce.300ms="searchTerm" placeholder="Buscar por nome do atleta..." class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
                </div>
            </div>

            {{-- Filtro aba Equipes --}}
            <div x-show="activeTab === 'equipes' && showFilters"
                 x-transition
                 class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100 md:border-none md:pt-0 mt-2 md:mt-0">
                <div class="relative lg:col-span-2">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-search text-gray-400"></i>
                    </div>
                    <input type="text" x-model.debounce.300ms="searchEquipeTab" placeholder="Buscar nome da equipe..." class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
                </div>
            </div>
        </section>

        {{-- Conteúdo --}}
        <div class="py-8 min-h-[40vh]">

            {{-- Aba Atletas --}}
            <div x-show="activeTab === 'atletas'" x-cloak class="space-y-6">
                <div class="border-l-4 border-orange-500 pl-4">
                    <h2 class="text-2xl font-bold text-slate-800">Pontuação por etapa e total</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Atletas do campeonato</p>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-700">Ranking de atletas</h3>
                        <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full" x-text="filteredAtletas.length + ' atletas'"></span>
                    </div>
                    <template x-if="filteredAtletas.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-fixed">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">#</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[180px]">Atleta</th>
                                        <template x-for="etapa in etapas" :key="etapa.id">
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap" x-text="'Etapa ' + etapa.numero"></th>
                                        </template>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-orange-600 uppercase tracking-wider w-24 whitespace-nowrap">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(item, index) in filteredAtletas" :key="item.atleta_id">
                                        <tr class="hover:bg-orange-50/50 transition-colors" :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                            <td class="px-6 py-3 font-bold text-slate-700" x-text="(index + 1) + 'º'"></td>
                                            <td class="px-6 py-3 font-medium text-slate-900 truncate" x-text="formatName(item.nome_atleta) || '—'"></td>
                                            <template x-for="etapa in etapas" :key="etapa.id">
                                                <td class="px-4 py-3 text-center text-gray-600" x-text="pontosEtapa(item, etapa.id)"></td>
                                            </template>
                                            <td class="px-6 py-3 text-right font-bold text-orange-600" x-text="item.total_pontos"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <div x-show="filteredAtletas.length === 0" class="p-8 text-center text-gray-500">
                        <p x-text="rankingAtletas.length === 0 ? 'Nenhum resultado lançado ainda para atletas.' : 'Nenhum atleta corresponde ao filtro.'"></p>
                    </div>
                </div>
            </div>

            {{-- Aba Equipes --}}
            <div x-show="activeTab === 'equipes'" x-cloak class="space-y-6">
                <div class="border-l-4 border-orange-500 pl-4">
                    <h2 class="text-2xl font-bold text-slate-800">Pontuação por etapa e total</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Equipes do campeonato</p>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-700">Ranking de equipes</h3>
                        <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full" x-text="filteredEquipes.length + ' equipes'"></span>
                    </div>
                    <template x-if="filteredEquipes.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-fixed">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">#</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[180px]">Equipe</th>
                                        <template x-for="etapa in etapas" :key="etapa.id">
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap" x-text="'Etapa ' + etapa.numero"></th>
                                        </template>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-orange-600 uppercase tracking-wider w-24 whitespace-nowrap">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(item, index) in filteredEquipes" :key="item.equipe_id">
                                        <tr class="hover:bg-orange-50/50 transition-colors" :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                            <td class="px-6 py-3 font-bold text-slate-700" x-text="(index + 1) + 'º'"></td>
                                            <td class="px-6 py-3 font-medium text-slate-900 truncate" x-text="formatName(item.nome_equipe) || '—'"></td>
                                            <template x-for="etapa in etapas" :key="etapa.id">
                                                <td class="px-4 py-3 text-center text-gray-600" x-text="pontosEtapa(item, etapa.id)"></td>
                                            </template>
                                            <td class="px-6 py-3 text-right font-bold text-orange-600" x-text="item.total_pontos"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <div x-show="filteredEquipes.length === 0" class="p-8 text-center text-gray-500">
                        <p x-text="rankingEquipes.length === 0 ? 'Nenhum resultado lançado ainda para equipes.' : 'Nenhuma equipe corresponde ao filtro.'"></p>
                    </div>
                </div>
            </div>

        </div>

        <div class="flex flex-wrap gap-4 pb-8">
            <a href="{{ route('campeonatos.show', $campeonato) }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-orange-600 font-bold transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao campeonato
            </a>
            <a href="{{ route('campeonatos.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-orange-600 font-bold transition-colors">
                <i class="fa-solid fa-trophy"></i> Todos os campeonatos
            </a>
        </div>
    </div>
@endsection
