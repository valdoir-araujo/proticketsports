@extends('layouts.public')

@section('title', 'Lista de Inscritos - ' . $evento->nome)

@section('content')
    {{-- Cabeçalho Moderno e Compacto da Página --}}
    <section class="relative bg-cover bg-center py-6 md:py-12">
        {{-- Imagem de fundo --}}
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $evento->banner_url ? asset('storage/' . $evento->banner_url) : 'https://images.unsplash.com/photo-1573521193826-58c7a24275a7?q=80&w=1974&auto=format&fit=crop' }}');"></div>
        {{-- Overlay escuro para legibilidade --}}
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>

        {{-- Conteúdo Alinhado --}}
        <div class="container relative mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8 items-center">
                    {{-- Coluna Esquerda: Título e Navegação --}}
                    <div class="md:col-span-2 text-center md:text-left">
                        <p class="text-orange-400 font-semibold text-xs md:text-sm uppercase tracking-wider">Lista de Inscritos</p>
                        <h1 class="text-xl md:text-3xl font-extrabold text-white mt-1 leading-tight">{{ $evento->nome }}</h1>
                        <a href="{{ route('eventos.public.show', $evento) }}" class="mt-2 md:mt-3 inline-flex items-center gap-x-2 text-xs md:text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Voltar para a página do evento</span>
                        </a>
                    </div>

                    {{-- Coluna Direita: Stats --}}
                    <div class="md:col-span-1 flex justify-center md:justify-end">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 md:p-4 flex items-center gap-x-4 md:gap-x-6 text-white text-center">
                            <div>
                                <span class="text-2xl md:text-3xl font-bold">{{ $inscricoes->total() }}</span>
                                <p class="text-[10px] md:text-xs uppercase tracking-wider text-slate-300">Inscritos</p>
                            </div>
                            <div class="border-l border-white/20 h-8 md:h-10"></div>
                            <div>
                                <span class="text-2xl md:text-3xl font-bold">{{ $evento->data_evento->format('d') }}</span>
                                <p class="text-[10px] md:text-xs uppercase tracking-wider text-slate-300">{{ $evento->data_evento->translatedFormat('M') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Container Principal com Alpine.js --}}
    <div x-data="{
            activeTab: 'atletas', // Controle das abas (atletas ou equipes)
            searchTerm: '',
            searchCity: '',
            searchTeam: '',
            searchTeamTab: '', // Filtro exclusivo da aba de equipes
            selectedCategory: '',
            inscricoes: {{ Js::from($inscricoes->items()) }},
            storageUrl: '{{ asset('storage') }}',
            
            // Estado de visualização dos filtros (Responsivo)
            showFilters: window.innerWidth >= 768,
            isMobile: window.innerWidth < 768,

            get filteredInscricoes() {
                if (!this.searchTerm && !this.searchCity && !this.searchTeam && !this.selectedCategory) {
                    return this.inscricoes;
                }

                return this.inscricoes.filter(inscricao => {
                    const nameMatch = this.searchTerm === '' || (inscricao.atleta?.user?.name || '').toLowerCase().includes(this.searchTerm.toLowerCase());
                    const cityMatch = this.searchCity === '' || (inscricao.atleta?.cidade?.nome || '').toLowerCase().includes(this.searchCity.toLowerCase());
                    
                    const teamName = inscricao.equipe?.nome || 'individual';
                    const teamMatch = this.searchTeam === '' || teamName.toLowerCase().includes(this.searchTeam.toLowerCase());
                    
                    const fullCategoryName = inscricao.categoria?.percurso
                        ? `${inscricao.categoria.percurso.descricao} | ${inscricao.categoria.nome} - ${this.formatName(inscricao.categoria.genero)}`
                        : '';
                    const categoryMatch = this.selectedCategory === '' || fullCategoryName === this.selectedCategory;

                    return nameMatch && cityMatch && teamMatch && categoryMatch;
                });
            },

            get groupedInscricoes() {
                const groups = this.filteredInscricoes.reduce((acc, inscricao) => {
                    const percursoId = inscricao.categoria?.percurso?.id ?? 0;
                    const percursoDesc = inscricao.categoria?.percurso?.descricao ?? 'Percurso não definido';
                    const categoriaId = inscricao.categoria?.id ?? 0;
                    const categoriaKey = inscricao.categoria ? `${inscricao.categoria.nome} - ${this.formatName(inscricao.categoria.genero)}` : 'Sem Categoria';

                    if (!acc[percursoId]) {
                        acc[percursoId] = { id: percursoId, descricao: percursoDesc, categorias: {} };
                    }
                    if (!acc[percursoId].categorias[categoriaId]) {
                        acc[percursoId].categorias[categoriaId] = { id: categoriaId, nome: categoriaKey, inscricoes: [] };
                    }
                    acc[percursoId].categorias[categoriaId].inscricoes.push(inscricao);
                    return acc;
                }, {});

                return Object.values(groups).map(percurso => {
                    percurso.categorias = Object.values(percurso.categorias).sort((a, b) => a.id - b.id);
                    return percurso;
                }).sort((a, b) => a.id - b.id);
            },

            // Função que agrupa, conta totais e consolida status dos inscritos
            get listEquipes() {
                const teamsData = {};
                
                this.inscricoes.forEach(inscricao => {
                    const rawName = inscricao.equipe?.nome;
                    const teamName = (rawName && rawName.trim() !== '') ? this.formatName(rawName) : 'Individual';
                    
                    if (!teamsData[teamName]) {
                        // Adicionadas propriedades para contar confirmados e pendentes
                        teamsData[teamName] = { 
                            nome: teamName, 
                            quantidade: 0, 
                            confirmados: 0, 
                            pendentes: 0, 
                            inscricoes: [] 
                        };
                    }
                    
                    teamsData[teamName].quantidade++;
                    
                    // Incrementa os status
                    if (inscricao.status === 'confirmada') {
                        teamsData[teamName].confirmados++;
                    } else {
                        teamsData[teamName].pendentes++;
                    }

                    teamsData[teamName].inscricoes.push(inscricao);
                });
                
                let result = Object.values(teamsData);

                // Aplica filtro de busca da aba de equipes
                if (this.searchTeamTab) {
                    result = result.filter(eq => eq.nome.toLowerCase().includes(this.searchTeamTab.toLowerCase()));
                }

                // Ordena por quantidade total (descendente) e alfabético em caso de empate
                result.sort((a, b) => {
                    if (b.quantidade !== a.quantidade) return b.quantidade - a.quantidade;
                    return a.nome.localeCompare(b.nome);
                });

                return result;
            },

            formatName(str) {
                if (!str) return '';
                return str.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
            }
        }"
        @resize.window.debounce.300ms="isMobile = window.innerWidth < 768; if(!isMobile) showFilters = true">

        {{-- BARRA DE FILTROS E ABAS FIXA (STICKY) --}}
        <section class="sticky top-[64px] z-30 bg-white shadow-md py-4 border-b">
            <div class="container mx-auto px-4">
                 <div class="max-w-6xl mx-auto">
                    
                    {{-- Cabeçalho com Sistema de Abas Integrado --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-2 md:mb-4">
                        
                        {{-- SELECIONADOR DE ABAS (BOTÕES SEPARADOS E MODERNOS) --}}
                        <div class="flex flex-row gap-3 w-full md:w-auto">
                            <button @click="activeTab = 'atletas'" 
                                    :class="activeTab === 'atletas' 
                                        ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/40 border-transparent' 
                                        : 'bg-white text-slate-600 border-gray-200 shadow-sm hover:border-orange-300 hover:text-orange-600 hover:bg-orange-50'"
                                    class="flex-1 md:flex-none px-5 py-2.5 rounded-xl border font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2 group">
                                <i class="fa-solid fa-user-tag text-base transition-transform duration-300 group-hover:scale-110"></i> 
                                <span>Por Atleta</span>
                            </button>
                            
                            <button @click="activeTab = 'equipes'" 
                                    :class="activeTab === 'equipes' 
                                        ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/40 border-transparent' 
                                        : 'bg-white text-slate-600 border-gray-200 shadow-sm hover:border-orange-300 hover:text-orange-600 hover:bg-orange-50'"
                                    class="flex-1 md:flex-none px-5 py-2.5 rounded-xl border font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2 group">
                                <i class="fa-solid fa-users text-base transition-transform duration-300 group-hover:scale-110"></i> 
                                <span>Por Equipe</span>
                            </button>
                        </div>
                        
                        {{-- Botão Toggle Mobile (Lupa) --}}
                        <div class="md:hidden w-full mt-1">
                            <button @click="showFilters = !showFilters" 
                                    class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-slate-800 text-white font-bold text-sm rounded-xl shadow-md hover:bg-slate-700 transition-colors">
                                <i class="fa-solid text-base" :class="showFilters ? 'fa-xmark' : 'fa-magnifying-glass'"></i>
                                <span x-text="showFilters ? 'Ocultar Filtros' : 'Buscar / Filtrar'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Filtros Aba 1: ATLETAS --}}
                    <div x-show="activeTab === 'atletas' && showFilters" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
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

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fa-solid fa-map-marker-alt text-gray-400"></i>
                            </div>
                            <input type="text" x-model.debounce.300ms="searchCity" placeholder="Buscar por cidade..." class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
                        </div>

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fa-solid fa-users text-gray-400"></i>
                            </div>
                            <input type="text" x-model.debounce.300ms="searchTeam" placeholder="Buscar por equipe..." class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
                        </div>
                    </div>

                    {{-- Filtros Aba 2: EQUIPES --}}
                    <div x-show="activeTab === 'equipes' && showFilters" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100 md:border-none md:pt-0 mt-2 md:mt-0" style="display: none;">
                        
                        <div class="relative lg:col-span-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fa-solid fa-search text-gray-400"></i>
                            </div>
                            <input type="text" x-model.debounce.300ms="searchTeamTab" placeholder="Buscar nome da equipe..." class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 bg-slate-50">
                        </div>
                    </div>

                 </div>
            </div>
        </section>

        {{-- AREA DE CONTEUDO --}}
        <div class="container mx-auto p-4 md:p-8">
            <div class="max-w-6xl mx-auto mt-8 min-h-[40vh]">
                
                {{-- CONTEÚDO DA ABA 1: LISTA DE ATLETAS --}}
                <div x-show="activeTab === 'atletas'" class="space-y-12">
                    <template x-for="percurso in groupedInscricoes" :key="percurso.id">
                        <div class="space-y-6">
                            <div class="border-l-4 border-orange-500 pl-4">
                                <h2 class="text-3xl font-bold text-slate-800" x-text="`Percurso - ${percurso.descricao}`"></h2>
                            </div>
                            
                            <template x-for="categoria in percurso.categorias" :key="categoria.id">
                                <div class="bg-white rounded-lg shadow-md flex flex-col">
                                    <div class="bg-gray-50 p-4 rounded-t-lg border-b flex justify-between items-center">
                                        <h3 class="text-lg font-bold text-slate-700" x-text="categoria.nome"></h3>
                                        <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full flex-shrink-0" x-text="`${categoria.inscricoes.length} inscritos`"></span>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full table-fixed">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20 whitespace-nowrap">Nº</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-4/12 min-w-[200px]">Atleta</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-3/12 min-w-[150px]">Cidade/UF</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-3/12 min-w-[150px]">Equipe</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12 whitespace-nowrap">Situação</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <template x-for="(inscricao, index) in categoria.inscricoes" :key="inscricao.id">
                                                    {{-- EFEITO ZEBRADO NA ABA 1 --}}
                                                    <tr class="hover:bg-orange-50/50 transition-colors" :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 text-center" x-text="index + 1"></td>
                                                        <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 truncate" x-text="formatName(inscricao.atleta?.user?.name) || 'Nome Indisponível'"></td>
                                                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 truncate" x-text="inscricao.atleta?.cidade ? `${formatName(inscricao.atleta.cidade.nome)} / ${inscricao.atleta.cidade.estado.uf}` : 'N/A'"></td>
                                                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 truncate" x-text="formatName(inscricao.equipe?.nome) || 'Individual'"></td>
                                                        <td class="px-6 py-2 whitespace-nowrap text-sm">
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                                    :class="inscricao.status === 'confirmada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                                                    x-text="inscricao.status === 'confirmada' ? 'Confirmada' : 'Pendente'">
                                                            </span>
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
                        <p>Nenhum atleta inscrito corresponde aos filtros selecionados.</p>
                    </div>

                    @if($inscricoes->hasPages())
                    <div class="mt-4 flex justify-center">
                        {{ $inscricoes->withQueryString()->links() }}
                    </div>
                    @endif
                </div>

                {{-- CONTEÚDO DA ABA 2: RANKING DE EQUIPES --}}
                <div x-show="activeTab === 'equipes'" style="display: none;" class="space-y-6">
                    <div class="border-l-4 border-orange-500 pl-4">
                        <h2 class="text-3xl font-bold text-slate-800">Lista de Inscritos por Equipes</h2>
                    </div>

                    <div class="bg-white rounded-lg shadow-md flex flex-col overflow-hidden">
                        <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                            <h3 class="text-lg font-bold text-slate-700">Equipes Cadastradas</h3>
                            <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full flex-shrink-0" x-text="`${listEquipes.length} equipes`"></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-fixed">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16 whitespace-nowrap">Pos</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-full min-w-[200px]">Nome da Equipe</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-green-600 uppercase tracking-wider w-36 whitespace-nowrap">Confirmados</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-yellow-600 uppercase tracking-wider w-36 whitespace-nowrap">A Confirmar</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-28 whitespace-nowrap">Total</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-12"></th>
                                    </tr>
                                </thead>
                                {{-- Múltiplos tbodys para gerenciar a expansão de cada equipe --}}
                                <template x-for="(equipe, index) in listEquipes" :key="equipe.nome">
                                    <tbody x-data="{ expanded: false }" class="border-b border-gray-100 last:border-b-0">
                                        {{-- LINHA PRINCIPAL DA EQUIPE (EFEITO ZEBRADO) --}}
                                        <tr @click="expanded = !expanded" 
                                            class="hover:bg-orange-100/50 transition-colors cursor-pointer group"
                                            :class="index % 2 !== 0 ? 'bg-slate-50' : 'bg-white'">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-500 text-center">
                                                <span class="bg-gray-100 group-hover:bg-white group-hover:shadow-sm transition-all px-3 py-1 rounded-full text-xs font-bold" x-text="`${index + 1}º`"></span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-slate-800 truncate" x-text="equipe.nome"></td>
                                            
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600 text-right" x-text="equipe.confirmados"></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-yellow-600 text-right" x-text="equipe.pendentes"></td>
                                            
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-orange-600 text-right text-lg bg-orange-50/30" x-text="equipe.quantidade"></td>
                                            
                                            <td class="px-4 py-4 whitespace-nowrap text-center text-gray-400 group-hover:text-orange-500 transition-colors">
                                                <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="expanded ? 'rotate-180' : ''"></i>
                                            </td>
                                        </tr>
                                        
                                        {{-- ÁREA EXPANDIDA (LISTA DE ATLETAS DA EQUIPE - MAIS COMPACTA) --}}
                                        <tr x-show="expanded" style="display: none;">
                                            <td colspan="6" class="p-0 bg-slate-100/30 shadow-inner">
                                                {{-- Indentação visual para dar aspecto de sub-nível --}}
                                                <div class="pl-4 sm:pl-8 pr-4 py-4 border-l-2 border-orange-300 ml-4 sm:ml-6 my-2">
                                                    <h4 class="text-xs font-bold text-slate-500 mb-3 flex items-center gap-2 uppercase tracking-wider">
                                                        <i class="fa-solid fa-user-group text-orange-400"></i> Integrantes
                                                    </h4>
                                                    
                                                    {{-- Layout Grid de 1 Coluna com padding reduzido e fontes menores --}}
                                                    <div class="space-y-1.5">
                                                        <template x-for="(inscricao, i) in equipe.inscricoes" :key="inscricao.id">
                                                            {{-- EFEITO ZEBRADO INTERNO NOS ATLETAS --}}
                                                            <div class="p-2 sm:p-2.5 rounded border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 hover:border-orange-300 hover:shadow-md transition-all"
                                                                 :class="i % 2 !== 0 ? 'bg-slate-50/80' : 'bg-white'">
                                                                
                                                                {{-- Coluna 1: Avatar Reduzido e Nome --}}
                                                                <div class="flex items-center gap-2.5 md:w-5/12">
                                                                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                                        <i class="fa-solid fa-user"></i>
                                                                    </div>
                                                                    <div class="min-w-0 flex-1">
                                                                        <p class="text-xs font-bold text-gray-700 truncate" x-text="formatName(inscricao.atleta?.user?.name) || 'Nome Indisponível'"></p>
                                                                        
                                                                        {{-- Visualização Mobile da Categoria --}}
                                                                        <p class="text-[10px] text-gray-500 truncate block md:hidden" 
                                                                           x-text="(inscricao.categoria?.percurso?.descricao ? inscricao.categoria.percurso.descricao + ' | ' : '') + (inscricao.categoria?.nome || 'Sem Categoria')">
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                                {{-- Coluna 2: Percurso + Categoria Concatenados --}}
                                                                <div class="hidden md:flex flex-col justify-center md:w-5/12">
                                                                    <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold mb-0.5">Percurso / Categoria</p>
                                                                    <p class="text-xs text-gray-600 truncate font-medium" 
                                                                       x-text="(inscricao.categoria?.percurso?.descricao ? inscricao.categoria.percurso.descricao + ' - ' : '') + (inscricao.categoria?.nome || 'Sem Categoria')">
                                                                    </p>
                                                                </div>

                                                                {{-- Coluna 3: Badge de Status Reduzido --}}
                                                                <div class="flex justify-start md:justify-end md:w-2/12">
                                                                    <span class="px-2 py-1 text-[10px] font-bold rounded-full flex items-center gap-1.5 w-fit"
                                                                            :class="inscricao.status === 'confirmada' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                                                        <i class="fa-solid text-[10px]" :class="inscricao.status === 'confirmada' ? 'fa-check-circle' : 'fa-clock'"></i>
                                                                        <span x-text="inscricao.status === 'confirmada' ? 'Confirmada' : 'A Confirmar'"></span>
                                                                    </span>
                                                                </div>

                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </template>
                            </table>
                        </div>
                    </div>

                    <div x-show="listEquipes.length === 0" class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
                        <p>Nenhuma equipa encontrada com esse nome.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection