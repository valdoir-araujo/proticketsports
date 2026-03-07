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
                        <a href="{{ route('organizador.eventos.show', $evento) }}" class="inline-flex items-center text-xs font-bold text-blue-200 hover:text-white transition-colors bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm border border-white/10 hover:bg-white/20">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Evento
                        </a>
                        <span class="text-slate-400 text-xs">•</span>
                        <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Configuração</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md flex items-center gap-3">
                        <i class="fa-solid fa-list-ol text-amber-500"></i> Numeração de Atletas
                    </h2>
                    <p class="text-blue-100 mt-1 font-medium text-lg opacity-90">{{ $evento->nome }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="relative z-20 -mt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Card Principal --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                
                {{-- Alerta Informativo --}}
                <div class="bg-blue-50 border-b border-blue-100 p-6 flex items-start gap-4">
                    <div class="bg-blue-100 p-2 rounded-full text-blue-600 shrink-0">
                        <i class="fa-solid fa-circle-info text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-blue-900 font-bold text-sm uppercase tracking-wide mb-1">Como funciona?</h4>
                        <p class="text-blue-700 text-sm leading-relaxed">
                            Esta ferramenta atribui números de peito automaticamente aos atletas inscritos.
                            <br><span class="font-bold">Atenção:</span> Ao gerar, qualquer numeração manual inserida anteriormente nestes atletas será sobrescrita.
                        </p>
                    </div>
                </div>

                <div class="p-8">
                    <form action="{{ route('organizador.eventos.numeracao.store', $evento) }}" method="POST" x-data="{ tipo: 'global_alfabetica', filtro: 'confirmada' }">
                        @csrf

                        {{-- SEÇÃO 1: MÉTODO --}}
                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                                <span class="bg-slate-900 text-white rounded-lg w-8 h-8 flex items-center justify-center text-sm font-bold shadow-md">1</span>
                                Escolha o Método de Numeração
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Opção 1: Global Alfabética -->
                                <label class="group relative flex flex-col p-6 rounded-2xl cursor-pointer border-2 transition-all duration-300" 
                                       :class="tipo === 'global_alfabetica' ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-600 shadow-md' : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm'">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-indigo-600 shadow-sm">
                                                <i class="fa-solid fa-arrow-down-a-z"></i>
                                            </div>
                                            <span class="block text-base font-bold text-slate-800">Sequencial Alfabética</span>
                                        </div>
                                        <input type="radio" name="tipo_numeracao" value="global_alfabetica" x-model="tipo" class="text-indigo-600 focus:ring-indigo-500 h-5 w-5 border-gray-300">
                                    </div>
                                    <p class="text-sm text-slate-500 pl-1">Ordena todos os atletas de A a Z e numera sequencialmente (1, 2, 3...). Ignora separação de categorias.</p>
                                </label>

                                <!-- Opção 2: Global por Inscrição -->
                                <label class="group relative flex flex-col p-6 rounded-2xl cursor-pointer border-2 transition-all duration-300" 
                                       :class="tipo === 'global_inscricao' ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-600 shadow-md' : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm'">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-indigo-600 shadow-sm">
                                                <i class="fa-regular fa-clock"></i>
                                            </div>
                                            <span class="block text-base font-bold text-slate-800">Ordem de Inscrição</span>
                                        </div>
                                        <input type="radio" name="tipo_numeracao" value="global_inscricao" x-model="tipo" class="text-indigo-600 focus:ring-indigo-500 h-5 w-5 border-gray-300">
                                    </div>
                                    <p class="text-sm text-slate-500 pl-1">Numera pela data de cadastro. Quem se inscreveu primeiro recebe o número 1, e assim por diante.</p>
                                </label>

                                <!-- Opção 3: Por Categoria -->
                                <label class="group relative flex flex-col p-6 rounded-2xl cursor-pointer border-2 transition-all duration-300" 
                                       :class="tipo === 'por_categoria' ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-600 shadow-md' : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm'">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-indigo-600 shadow-sm">
                                                <i class="fa-solid fa-layer-group"></i>
                                            </div>
                                            <span class="block text-base font-bold text-slate-800">Faixas por Categoria</span>
                                        </div>
                                        <input type="radio" name="tipo_numeracao" value="por_categoria" x-model="tipo" class="text-indigo-600 focus:ring-indigo-500 h-5 w-5 border-gray-300">
                                    </div>
                                    <p class="text-sm text-slate-500 pl-1">Defina um número inicial para cada categoria. Ex: Elite (100-199), Master (200-299).</p>
                                </label>
                            </div>
                        </div>

                        {{-- SEÇÃO 2: FILTRO --}}
                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                                <span class="bg-slate-900 text-white rounded-lg w-8 h-8 flex items-center justify-center text-sm font-bold shadow-md">2</span>
                                Filtrar Inscrições
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                                       :class="filtro === 'confirmada' ? 'bg-green-50 border-green-500 shadow-sm' : 'bg-white hover:bg-slate-50 border-slate-200'">
                                    <input type="radio" name="status_filtro" value="confirmada" x-model="filtro" class="text-green-600 focus:ring-green-500 h-5 w-5 border-gray-300">
                                    <div class="ml-4">
                                        <span class="block text-sm font-bold text-slate-800 flex items-center gap-2">
                                            <i class="fa-solid fa-check-circle text-green-500"></i> Somente Confirmadas
                                        </span>
                                        <span class="block text-xs text-slate-500 mt-1">Numera apenas quem já pagou ou foi confirmado. (Recomendado)</span>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                                       :class="filtro === 'todos' ? 'bg-amber-50 border-amber-500 shadow-sm' : 'bg-white hover:bg-slate-50 border-slate-200'">
                                    <input type="radio" name="status_filtro" value="todos" x-model="filtro" class="text-amber-600 focus:ring-amber-500 h-5 w-5 border-gray-300">
                                    <div class="ml-4">
                                        <span class="block text-sm font-bold text-slate-800 flex items-center gap-2">
                                            <i class="fa-solid fa-users text-amber-500"></i> Todas (Confirmadas + Pendentes)
                                        </span>
                                        <span class="block text-xs text-slate-500 mt-1">Numera todos os inscritos, incluindo quem ainda não pagou.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- SEÇÃO 3: CONFIGURAÇÃO --}}
                        
                        {{-- Configuração Global --}}
                        <div x-show="tipo !== 'por_categoria'" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="bg-slate-50 p-8 rounded-2xl border border-slate-200">
                            
                            <h4 class="font-bold text-slate-800 mb-6 flex items-center">
                                <i class="fa-solid fa-gears mr-2 text-indigo-500"></i> Configuração Sequencial
                            </h4>
                            
                            <div class="max-w-xs">
                                <x-input-label for="numero_inicial_global" value="Iniciar numeração em:" class="text-slate-700 font-bold" />
                                <div class="mt-2 relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">#</span>
                                    </div>
                                    <input type="number" name="numero_inicial_global" id="numero_inicial_global" 
                                           class="block w-full rounded-lg border-slate-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold text-slate-800" 
                                           value="1" min="1">
                                </div>
                                <p class="text-xs text-slate-500 mt-2">O primeiro atleta receberá este número.</p>
                            </div>
                        </div>

                        {{-- Configuração por Categoria --}}
                        <div x-show="tipo === 'por_categoria'" style="display: none;">
                            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                                <span class="bg-slate-900 text-white rounded-lg w-8 h-8 flex items-center justify-center text-sm font-bold shadow-md">3</span>
                                Defina as Faixas de Numeração
                            </h3>
                            
                            <div class="space-y-8">
                                @foreach($evento->percursos as $percurso)
                                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                        {{-- Cabeçalho do Percurso --}}
                                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-4">
                                            <div class="bg-white text-indigo-600 rounded-xl p-2.5 w-10 h-10 flex items-center justify-center border border-slate-200 shadow-sm">
                                                <i class="fa-solid fa-route"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-extrabold text-slate-800 text-lg">{{ $percurso->descricao }}</h4>
                                                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider bg-slate-200/50 px-2 py-0.5 rounded inline-block mt-0.5">
                                                    {{ $percurso->distancia_km }} KM
                                                </p>
                                            </div>
                                        </div>
                                        
                                        {{-- Lista de Categorias --}}
                                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($percurso->categorias->sortBy('nome') as $categoria)
                                                @php
                                                    $generoClass = match(strtolower($categoria->genero)) {
                                                        'masculino' => 'bg-blue-50/50 border-blue-100',
                                                        'feminino' => 'bg-pink-50/50 border-pink-100',
                                                        'mista' => 'bg-purple-50/50 border-purple-100',
                                                        default => 'bg-gray-50 border-gray-100'
                                                    };
                                                    $iconColor = match(strtolower($categoria->genero)) {
                                                        'masculino' => 'text-blue-500',
                                                        'feminino' => 'text-pink-500',
                                                        'mista' => 'text-purple-500',
                                                        default => 'text-gray-500'
                                                    };
                                                    $confirmados = $categoria->inscricoes()->where('status', 'confirmada')->count();
                                                    $total = $categoria->inscricoes()->count();
                                                @endphp

                                                <div class="flex items-center gap-3 p-3 rounded-xl border {{ $generoClass }}">
                                                    <div class="flex-grow min-w-0">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <i class="fa-solid fa-tag {{ $iconColor }}"></i>
                                                            <label class="block text-sm font-bold text-slate-800 truncate" title="{{ $categoria->nome }}">{{ $categoria->nome }}</label>
                                                        </div>
                                                        
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-slate-500 border border-slate-100 shadow-sm">
                                                            <span x-show="filtro === 'confirmada'">{{ $confirmados }} Confirmados</span>
                                                            <span x-show="filtro === 'todos'" style="display: none;">{{ $total }} Inscritos</span>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="w-24 shrink-0">
                                                        <label class="block text-[9px] mb-1 uppercase font-bold text-slate-400 text-center tracking-wide">Início Nº</label>
                                                        <input type="number" 
                                                               name="faixas[{{ $categoria->id }}]" 
                                                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold text-center text-slate-800" 
                                                               placeholder="Ex: 100"
                                                               min="1">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                @if($evento->percursos->isEmpty())
                                    <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50">
                                        <i class="fa-solid fa-route text-4xl text-slate-300 mb-3"></i>
                                        <p class="text-slate-500 font-medium">Nenhum percurso ou categoria cadastrado para este evento.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- BOTÕES DE AÇÃO --}}
                        <div class="mt-12 pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                            <a href="{{ route('organizador.eventos.show', $evento) }}" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50 hover:text-slate-800 transition-colors">
                                Cancelar
                            </a>
                            
                            <button type="submit" 
                                    onclick="return confirm('ATENÇÃO: Isso irá redefinir a numeração dos atletas selecionados. Deseja continuar?')" 
                                    class="inline-flex items-center px-8 py-3 bg-amber-500 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-amber-600 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200 shadow-lg shadow-amber-500/30 hover:-translate-y-0.5">
                                <i class="fa-solid fa-check-to-slot mr-2"></i> Gerar Numeração
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>