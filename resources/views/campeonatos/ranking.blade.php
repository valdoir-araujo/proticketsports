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
             selectedCategory: '',
             etapas: {{ Js::from($etapasParaRanking) }},
             gruposAtletas: {{ Js::from($gruposAtletas) }},
             gruposEquipes: {{ Js::from($gruposEquipes) }},
             categoriasParaFiltro: {{ Js::from($categoriasParaFiltro) }},
             showFilters: window.innerWidth >= 768,

             percursoVisivel(percurso) {
                 if (!this.selectedCategory) return true;
                 return percurso.categorias.some(c => c.filtro_value === this.selectedCategory);
             },
             categoriaVisivel(cat) {
                 if (!this.selectedCategory) return true;
                 return cat.filtro_value === this.selectedCategory;
             },
             atletasFiltrados(atletas) {
                 if (!this.searchTerm) return atletas || [];
                 const t = this.searchTerm.toLowerCase();
                 return (atletas || []).filter(a => (a.nome || '').toLowerCase().includes(t));
             },
             equipesFiltradas(equipes) {
                 if (!this.searchEquipeTab) return equipes || [];
                 const t = this.searchEquipeTab.toLowerCase();
                 return (equipes || []).filter(e => (e.nome || '').toLowerCase().includes(t));
             },
             pontosEtapa(item, eventoId) {
                 const p = item.pontos_por_etapa || {};
                 const val = p[eventoId];
                 return val != null && val !== 0 ? val : '—';
             },
             formatName(str) {
                 if (!str) return '';
                 return str.toLowerCase().replace(/(?:^|\s)\S/g, a => a.toUpperCase());
             }
         }"
         @resize.window.debounce.300ms="if (window.innerWidth >= 768) showFilters = true">

        {{-- Barra de filtros e abas (sticky) — mesmo padrão da lista de inscritos --}}
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

            {{-- Filtros aba Atletas — mesmo padrão da lista de inscritos --}}
            <div x-show="activeTab === 'atletas' && showFilters"
                 x-transition
                 class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100 md:border-none md:pt-0 mt-2 md:mt-0">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-tag text-gray-400"></i>
                    </div>
                    <select x-model="selectedCategory" class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
                        <option value="">Todas as Categorias</option>
                        @foreach($categoriasParaFiltro as $filtro)
                            <option value="{{ $filtro }}">{{ $filtro }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-user text-gray-400"></i>
                    </div>
                    <input type="text" x-model.debounce.300ms="searchTerm" placeholder="Buscar por nome do atleta..." class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
                </div>
            </div>

            {{-- Filtros aba Equipes --}}
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

        <div class="py-8 min-h-[40vh]">

            {{-- Aba Atletas: por percurso e categoria --}}
            <div x-show="activeTab === 'atletas'" x-cloak class="space-y-12">
                <template x-for="percurso in gruposAtletas" :key="percurso.percurso_id">
                    <div x-show="percursoVisivel(percurso)" class="space-y-6">
                        <div class="border-l-4 border-orange-500 pl-4">
                            <h2 class="text-3xl font-bold text-slate-800" x-text="'Percurso - ' + percurso.percurso_desc"></h2>
                        </div>
                        <template x-for="categoria in percurso.categorias" :key="categoria.categoria_id">
                            <div x-show="categoriaVisivel(categoria)" class="bg-white rounded-lg shadow-md flex flex-col overflow-hidden">
                                <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                                    <h3 class="text-lg font-bold text-slate-700" x-text="categoria.categoria_label"></h3>
                                    <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full" x-text="atletasFiltrados(categoria.atletas).length + ' atletas'"></span>
                                </div>
                                <template x-if="atletasFiltrados(categoria.atletas).length > 0">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full table-fixed">
                                            <thead class="bg-gray-50 border-b">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">#</th>
                                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[180px]">Atleta</th>
                                                    <template x-for="etapa in etapas" :key="etapa.id">
                                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap" x-text="'Etapa ' + etapa.numero"></th>
                                                    </template>
                                                    <th class="px-6 py-3 text-right text-xs font-bold text-orange-600 uppercase tracking-wider w-24 whitespace-nowrap bg-orange-50">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <template x-for="(item, index) in atletasFiltrados(categoria.atletas)" :key="item.id">
                                                    <tr class="hover:bg-orange-50/50 transition-colors" :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                                        <td class="px-6 py-3 font-bold text-slate-700" x-text="(index + 1) + 'º'"></td>
                                                        <td class="px-6 py-3 font-medium text-slate-900 truncate" x-text="formatName(item.nome) || '—'"></td>
                                                        <template x-for="etapa in etapas" :key="etapa.id">
                                                            <td class="px-4 py-3 text-center text-gray-600" x-text="pontosEtapa(item, etapa.id)"></td>
                                                        </template>
                                                        <td class="px-6 py-3 text-right font-bold text-orange-600 bg-orange-50/80" x-text="item.total_pontos"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                                <div x-show="atletasFiltrados(categoria.atletas).length === 0" class="p-6 text-center text-gray-500">
                                    <p x-text="(categoria.atletas || []).length === 0 ? 'Nenhum resultado nesta categoria.' : 'Nenhum atleta corresponde ao filtro.'"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Aba Equipes: por percurso e categoria --}}
            <div x-show="activeTab === 'equipes'" x-cloak class="space-y-12">
                <template x-for="percurso in gruposEquipes" :key="percurso.percurso_id">
                    <div x-show="percursoVisivel(percurso)" class="space-y-6">
                        <div class="border-l-4 border-orange-500 pl-4">
                            <h2 class="text-3xl font-bold text-slate-800" x-text="'Percurso - ' + percurso.percurso_desc"></h2>
                        </div>
                        <template x-for="categoria in percurso.categorias" :key="categoria.categoria_id">
                            <div x-show="categoriaVisivel(categoria)" class="bg-white rounded-lg shadow-md flex flex-col overflow-hidden">
                                <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                                    <h3 class="text-lg font-bold text-slate-700" x-text="categoria.categoria_label"></h3>
                                    <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full" x-text="equipesFiltradas(categoria.atletas).length + ' equipes'"></span>
                                </div>
                                <template x-if="equipesFiltradas(categoria.atletas).length > 0">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full table-fixed">
                                            <thead class="bg-gray-50 border-b">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">#</th>
                                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[180px]">Equipe</th>
                                                    <template x-for="etapa in etapas" :key="etapa.id">
                                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap" x-text="'Etapa ' + etapa.numero"></th>
                                                    </template>
                                                    <th class="px-6 py-3 text-right text-xs font-bold text-orange-600 uppercase tracking-wider w-24 whitespace-nowrap bg-orange-50">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <template x-for="(item, index) in equipesFiltradas(categoria.atletas)" :key="item.id">
                                                    <tr class="hover:bg-orange-50/50 transition-colors" :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                                        <td class="px-6 py-3 font-bold text-slate-700" x-text="(index + 1) + 'º'"></td>
                                                        <td class="px-6 py-3 font-medium text-slate-900 truncate" x-text="formatName(item.nome) || '—'"></td>
                                                        <template x-for="etapa in etapas" :key="etapa.id">
                                                            <td class="px-4 py-3 text-center text-gray-600" x-text="pontosEtapa(item, etapa.id)"></td>
                                                        </template>
                                                        <td class="px-6 py-3 text-right font-bold text-orange-600 bg-orange-50/80" x-text="item.total_pontos"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                                <div x-show="equipesFiltradas(categoria.atletas).length === 0" class="p-6 text-center text-gray-500">
                                    <p x-text="(categoria.atletas || []).length === 0 ? 'Nenhum resultado nesta categoria.' : 'Nenhuma equipe corresponde ao filtro.'"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
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
