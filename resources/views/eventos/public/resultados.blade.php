@extends('layouts.public')

@section('title', 'Ranking e resultados - ' . $evento->nome)

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    {{-- Cabeçalho no mesmo estilo da lista de inscritos --}}
    <section class="relative bg-cover bg-center py-6 md:py-12">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $evento->banner_url ? asset('storage/' . $evento->banner_url) : 'https://images.unsplash.com/photo-1573521193826-58c7a24275a7?q=80&w=1974&auto=format&fit=crop' }}');"></div>
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>
        <div class="container relative mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8 items-center">
                    <div class="md:col-span-2 text-center md:text-left">
                        <p class="text-orange-400 font-semibold text-xs md:text-sm uppercase tracking-wider">Ranking da etapa</p>
                        <h1 class="text-xl md:text-3xl font-extrabold text-white mt-1 leading-tight">{{ $evento->nome }}</h1>
                        <nav class="mt-2 md:mt-3 flex flex-wrap items-center gap-2 text-xs md:text-sm">
                            <a href="{{ route('eventos.public.show', $evento) }}" class="font-semibold text-slate-300 hover:text-white transition-colors inline-flex items-center gap-x-1.5">
                                <i class="fa-solid fa-arrow-left"></i>
                                <span>Voltar para a página do evento</span>
                            </a>
                            @if($evento->campeonato)
                                <span class="text-slate-500">·</span>
                                <a href="{{ route('campeonatos.show', $evento->campeonato) }}" class="text-slate-300 hover:text-white transition-colors">Ver campeonato</a>
                            @endif
                        </nav>
                    </div>
                    <div class="md:col-span-1 flex justify-center md:justify-end">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 md:p-4 flex items-center gap-x-4 md:gap-x-6 text-white text-center">
                            <div>
                                <span class="text-2xl md:text-3xl font-bold">{{ $inscricoes->whereNotNull('resultado')->count() }}</span>
                                <p class="text-[10px] md:text-xs uppercase tracking-wider text-slate-300">Com resultado</p>
                            </div>
                            @if($evento->data_evento)
                                <div class="border-l border-white/20 h-8 md:h-10"></div>
                                <div>
                                    <span class="text-2xl md:text-3xl font-bold">{{ $evento->data_evento->format('d') }}</span>
                                    <p class="text-[10px] md:text-xs uppercase tracking-wider text-slate-300">{{ $evento->data_evento->translatedFormat('M') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div x-data="{
            activeTab: 'atletas',
            searchTerm: '',
            selectedCategory: '',
            searchEquipeTab: '',
            inscricoes: {{ Js::from($inscricoes) }},
            rankingEquipes: {{ Js::from($rankingEquipesEtapa->map(fn($r) => ['equipe_nome' => $r['equipe']->nome, 'pontos_totais' => $r['pontos_totais']])->values()) }},
            showFilters: window.innerWidth >= 768,
            isMobile: window.innerWidth < 768,

            get filteredInscricoes() {
                if (!this.searchTerm && !this.selectedCategory) return this.inscricoes;
                return this.inscricoes.filter(insc => {
                    const nameMatch = !this.searchTerm || (insc.atleta?.user?.name || '').toLowerCase().includes(this.searchTerm.toLowerCase());
                    const fullCat = insc.categoria?.percurso ? (insc.categoria.percurso.descricao + ' | ' + insc.categoria.nome + ' - ' + (insc.categoria.genero ? insc.categoria.genero.charAt(0).toUpperCase() + insc.categoria.genero.slice(1) : '')) : '';
                    const catMatch = !this.selectedCategory || fullCat === this.selectedCategory;
                    return nameMatch && catMatch;
                });
            },

            get groupedInscricoes() {
                const groups = {};
                this.filteredInscricoes.forEach(insc => {
                    const percursoId = insc.categoria?.percurso?.id ?? 0;
                    const percursoDesc = insc.categoria?.percurso?.descricao ?? 'Geral';
                    const catId = insc.categoria?.id ?? 0;
                    const catNome = insc.categoria ? (insc.categoria.nome + ' - ' + (insc.categoria.genero ? insc.categoria.genero.charAt(0).toUpperCase() + insc.categoria.genero.slice(1) : '')) : 'Sem Categoria';
                    if (!groups[percursoId]) groups[percursoId] = { id: percursoId, descricao: percursoDesc, categorias: {} };
                    if (!groups[percursoId].categorias[catId]) groups[percursoId].categorias[catId] = { id: catId, nome: catNome, inscricoes: [] };
                    groups[percursoId].categorias[catId].inscricoes.push(insc);
                });
                return Object.values(groups).map(p => {
                    p.categorias = Object.values(p.categorias).sort((a,b) => a.id - b.id);
                    p.categorias.forEach(c => {
                        c.inscricoes.sort((a, b) => (a.resultado?.posicao_categoria ?? 999) - (b.resultado?.posicao_categoria ?? 999));
                    });
                    return p;
                }).sort((a,b) => a.id - b.id);
            },

            get filteredEquipes() {
                if (!this.searchEquipeTab) return this.rankingEquipes;
                return this.rankingEquipes.filter(e => (e.equipe_nome || '').toLowerCase().includes(this.searchEquipeTab.toLowerCase()));
            },

            statusLabel(status) {
                if (!status) return '—';
                const s = (status + '').toLowerCase();
                if (s === 'completou') return 'Completou';
                if (s === 'nao_completou') return 'Não completou';
                if (s === 'nao_iniciada') return 'Não iniciada';
                if (s === 'desqualificado') return 'Desqualificado';
                return status;
            },

            statusClass(status) {
                if (!status) return 'bg-gray-100 text-gray-600';
                const s = (status + '').toLowerCase();
                if (s === 'completou') return 'bg-green-100 text-green-800';
                if (s === 'nao_completou') return 'bg-slate-100 text-slate-700';
                if (s === 'desqualificado') return 'bg-red-100 text-red-800';
                return 'bg-yellow-100 text-yellow-800';
            },

            formatName(str) {
                if (!str) return '';
                return str.toLowerCase().replace(/(?:^|\s)\S/g, a => a.toUpperCase());
            }
        }"
        @resize.window.debounce.300ms="isMobile = window.innerWidth < 768; if(!isMobile) showFilters = true">

        {{-- Barra de filtros e abas (sticky) — mesmo estilo da lista de inscritos --}}
        <section class="sticky top-[64px] z-30 bg-white shadow-md py-4 border-b">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-2 md:mb-4">
                        <div class="flex flex-row gap-3 w-full md:w-auto">
                            <button @click="activeTab = 'atletas'"
                                    :class="activeTab === 'atletas' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/40 border-transparent' : 'bg-white text-slate-600 border-gray-200 shadow-sm hover:border-orange-300 hover:text-orange-600 hover:bg-orange-50'"
                                    class="flex-1 md:flex-none px-5 py-2.5 rounded-xl border font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-person-running"></i>
                                <span>Por Atleta</span>
                            </button>
                            <button @click="activeTab = 'equipes'"
                                    :class="activeTab === 'equipes' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/40 border-transparent' : 'bg-white text-slate-600 border-gray-200 shadow-sm hover:border-orange-300 hover:text-orange-600 hover:bg-orange-50'"
                                    class="flex-1 md:flex-none px-5 py-2.5 rounded-xl border font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-people-group"></i>
                                <span>Por Equipe</span>
                            </button>
                        </div>
                        <div class="md:hidden w-full mt-1">
                            <button @click="showFilters = !showFilters"
                                    class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-slate-800 text-white font-bold text-sm rounded-xl shadow-md hover:bg-slate-700 transition-colors">
                                <i class="fa-solid text-base" :class="showFilters ? 'fa-xmark' : 'fa-magnifying-glass'"></i>
                                <span x-text="showFilters ? 'Ocultar Filtros' : 'Buscar / Filtrar'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Filtros aba Atletas --}}
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
                            <input type="text" x-model.debounce.300ms="searchTerm" placeholder="Buscar por nome..." class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
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
                </div>
            </div>
        </section>

        {{-- Área de conteúdo --}}
        <div class="container mx-auto p-4 md:p-8">
            <div class="max-w-6xl mx-auto mt-8 min-h-[40vh]">

                @if($inscricoes->isEmpty() && $rankingEquipesEtapa->isEmpty())
                    <div class="bg-white rounded-lg shadow-md p-12 text-center text-gray-500">
                        <p>Ainda não há resultados publicados para esta etapa.</p>
                        <a href="{{ route('eventos.public.show', $evento) }}" class="mt-4 inline-flex items-center gap-2 text-orange-600 font-semibold text-sm">
                            <i class="fa-solid fa-arrow-left"></i> Voltar ao evento
                        </a>
                    </div>
                @else
                    {{-- Conteúdo aba Atletas: mesmo estilo da lista de inscritos — Percurso + cards com tabela --}}
                    <div x-show="activeTab === 'atletas'" class="space-y-12">
                        <template x-for="percurso in groupedInscricoes" :key="percurso.id">
                            <div class="space-y-6">
                                <div class="border-l-4 border-orange-500 pl-4">
                                    <h2 class="text-3xl font-bold text-slate-800" x-text="'Percurso - ' + percurso.descricao"></h2>
                                </div>
                                <template x-for="categoria in percurso.categorias" :key="categoria.id">
                                    <div class="bg-white rounded-lg shadow-md flex flex-col">
                                        <div class="bg-gray-50 p-4 rounded-t-lg border-b flex justify-between items-center">
                                            <h3 class="text-lg font-bold text-slate-700" x-text="categoria.nome"></h3>
                                            <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full flex-shrink-0" x-text="categoria.inscricoes.length + ' atletas'"></span>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full table-fixed">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20 whitespace-nowrap">Nº</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">Atleta</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32 whitespace-nowrap">Tempo</th>
                                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20 whitespace-nowrap">Pos.</th>
                                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24 whitespace-nowrap">Pontos</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28 whitespace-nowrap">Situação</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    <template x-for="(inscricao, index) in categoria.inscricoes" :key="inscricao.id">
                                                        <tr class="hover:bg-orange-50/50 transition-colors" :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 text-center" x-text="inscricao.numero_atleta || (index + 1)"></td>
                                                            <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 truncate" x-text="formatName(inscricao.atleta?.user?.name) || '—'"></td>
                                                            <td class="px-6 py-2 whitespace-nowrap text-sm font-mono text-gray-700" x-text="inscricao.resultado?.tempo_formatado || '—'"></td>
                                                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-700 text-center" x-text="inscricao.resultado?.posicao_categoria ? inscricao.resultado.posicao_categoria + 'º' : '—'"></td>
                                                            <td class="px-6 py-2 whitespace-nowrap text-sm text-center">
                                                                <span class="font-bold text-orange-600" x-text="inscricao.resultado?.pontos_etapa != null ? inscricao.resultado.pontos_etapa : '—'"></span>
                                                            </td>
                                                            <td class="px-6 py-2 whitespace-nowrap text-sm">
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                                      :class="statusClass(inscricao.resultado?.status_corrida)"
                                                                      x-text="statusLabel(inscricao.resultado?.status_corrida)"></span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div x-show="filteredInscricoes.length === 0" class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
                            <p>Nenhum atleta corresponde aos filtros selecionados.</p>
                        </div>
                    </div>

                    {{-- Conteúdo aba Equipes: total de pontos por equipe --}}
                    <div x-show="activeTab === 'equipes'" x-cloak class="space-y-6">
                        <div class="border-l-4 border-orange-500 pl-4">
                            <h2 class="text-3xl font-bold text-slate-800">Total de pontos por equipe</h2>
                        </div>
                        <div class="bg-white rounded-lg shadow-md flex flex-col overflow-hidden">
                            <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                                <h3 class="text-lg font-bold text-slate-700">Ranking por equipes</h3>
                                <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full flex-shrink-0" x-text="filteredEquipes.length + ' equipes'"></span>
                            </div>
                            <template x-if="filteredEquipes.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full table-fixed">
                                        <thead class="bg-gray-50 border-b">
                                            <tr>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Pos</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">Equipe</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Pontos</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <template x-for="(equipe, index) in filteredEquipes" :key="equipe.equipe_nome">
                                                <tr class="hover:bg-orange-50/50 transition-colors" :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                                    <td class="px-6 py-3 text-center text-sm font-medium text-gray-500">
                                                        <span class="bg-gray-100 px-3 py-1 rounded-full text-xs font-bold" x-text="(index + 1) + 'º'"></span>
                                                    </td>
                                                    <td class="px-6 py-3 text-sm font-bold text-slate-800" x-text="equipe.equipe_nome"></td>
                                                    <td class="px-6 py-3 text-right">
                                                        <span class="font-bold text-orange-600 text-lg bg-orange-50/30 px-3 py-1 rounded-lg" x-text="equipe.pontos_totais"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                            <div x-show="filteredEquipes.length === 0" class="p-8 text-center text-gray-500">
                                <p x-text="rankingEquipes.length === 0 ? 'Nenhuma equipe com resultado nesta etapa.' : 'Nenhuma equipe corresponde ao filtro.'"></p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
