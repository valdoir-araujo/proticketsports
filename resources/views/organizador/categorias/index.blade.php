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
                        <a href="{{ route('organizador.eventos.show', ['evento' => $percurso->evento, 'tab' => 'percursos']) }}" class="inline-flex items-center text-xs font-bold text-blue-200 hover:text-white transition-colors bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm border border-white/10 hover:bg-white/20">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Evento
                        </a>
                        <span class="text-slate-400 text-xs">•</span>
                        <span class="text-xs text-orange-400 font-bold uppercase tracking-wider">Percurso</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md flex items-center gap-3">
                        <i class="fa-solid fa-layer-group text-orange-500"></i> Categorias: <span class="font-light text-slate-200">{{ $percurso->descricao }}</span>
                    </h2>
                    <p class="text-blue-100 mt-1 font-medium text-lg opacity-90">
                        {{ $percurso->evento->nome }}
                    </p>
                </div>

                {{-- Link para Biblioteca --}}
                <div class="z-10">
                    <a href="{{ route('organizador.modelos-categoria.index') }}" target="_blank" class="inline-flex items-center px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-sm font-bold backdrop-blur-md border border-white/10 transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-book-open mr-2"></i> Gerir Biblioteca de Categorias
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="relative z-20 -mt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Mensagens de Feedback --}}
            @if (session('sucesso'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center shadow-sm animate-fade-in">
                    <i class="fa-solid fa-circle-check mr-3 text-lg"></i>
                    {{ session('sucesso') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 shadow-sm animate-fade-in">
                    <p class="font-bold flex items-center mb-2"><i class="fa-solid fa-circle-exclamation mr-2"></i> Erro:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

                {{-- COLUNA DA ESQUERDA: LISTA DE CATEGORIAS (Lista Simples Ordenada) --}}
                <div class="lg:col-span-3 space-y-6">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-list-ul text-indigo-500"></i> Categorias Habilitadas
                            </h3>
                            <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs font-bold text-slate-500">
                                {{ $percurso->categorias->count() }} Total
                            </span>
                        </div>

                        <div class="p-6 space-y-3">
                            @php
                                // CORREÇÃO: Ordena por ID Crescente (ASC)
                                $categoriasOrdenadas = $percurso->categorias->sortBy('id');
                            @endphp

                            @forelse($categoriasOrdenadas as $categoria)
                                @php
                                    $generoClass = match(strtolower($categoria->genero)) {
                                        'masculino' => 'bg-blue-50 border-blue-100 text-blue-700',
                                        'feminino' => 'bg-pink-50 border-pink-100 text-pink-700',
                                        'mista' => 'bg-purple-50 border-purple-100 text-purple-700',
                                        default => 'bg-gray-50 border-gray-100 text-gray-700'
                                    };
                                    $iconClass = match(strtolower($categoria->genero)) {
                                        'masculino' => 'fa-mars text-blue-500',
                                        'feminino' => 'fa-venus text-pink-500',
                                        'mista' => 'fa-users text-purple-500',
                                        default => 'fa-user text-gray-500'
                                    };
                                @endphp

                                <div class="group flex items-center justify-between p-4 rounded-xl border hover:border-indigo-300 hover:shadow-md transition-all duration-200 bg-white">
                                    <div class="flex items-center gap-4">
                                        {{-- Ícone Gênero --}}
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl shadow-sm {{ $generoClass }}">
                                            <i class="fa-solid {{ $iconClass }}"></i>
                                        </div>
                                        
                                        <div class="flex flex-col">
                                            {{-- ID e Nome da Categoria --}}
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-xs font-mono text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100" title="ID da Categoria">
                                                    #{{ $categoria->id }}
                                                </span>
                                                <h4 class="font-extrabold text-slate-800 text-lg leading-tight group-hover:text-indigo-700 transition-colors">
                                                    {{ $categoria->nome }}
                                                </h4>
                                            </div>

                                            {{-- Detalhes: Gênero e Idade --}}
                                            <div class="flex items-center gap-3 mt-1.5 text-xs font-medium text-slate-500">
                                                <span class="flex items-center gap-1.5 text-slate-700">
                                                    <i class="fa-solid fa-venus-mars opacity-50"></i>
                                                    @if(strtolower($categoria->genero) == 'masculino')
                                                        Masculina
                                                    @elseif(strtolower($categoria->genero) == 'feminino')
                                                        Feminina
                                                    @else
                                                        {{ ucfirst($categoria->genero) }}
                                                    @endif
                                                </span>

                                                <span class="text-slate-300">|</span>

                                                <span class="flex items-center gap-1">
                                                    <i class="fa-solid fa-cake-candles opacity-50"></i> 
                                                    {{ $categoria->idade_min ?? $categoria->idade_minima ?? 0 }} a {{ $categoria->idade_max ?? $categoria->idade_maxima ?? 99 }} anos
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('organizador.lotes.index', $categoria) }}" 
                                           class="inline-flex items-center justify-center px-4 py-2 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 rounded-lg text-sm font-bold transition-colors border border-green-200"
                                           title="Gerenciar Preços e Lotes">
                                            <i class="fa-solid fa-tags mr-2"></i> Preços
                                        </a>
                                        
                                        <form method="POST" action="{{ route('organizador.categorias.destroy', ['percurso' => $percurso, 'categoria' => $categoria]) }}" onsubmit="return confirm('Tem a certeza que deseja excluir esta categoria?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 transition-all" title="Excluir">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 px-6 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                                    <div class="w-16 h-16 mx-auto bg-white rounded-full flex items-center justify-center shadow-sm mb-4 text-slate-300">
                                        <i class="fa-solid fa-layer-group text-2xl"></i>
                                    </div>
                                    <h4 class="text-slate-600 font-bold mb-1">Nenhuma categoria vinculada</h4>
                                    <p class="text-sm text-slate-400 mb-0">Utilize o painel ao lado para adicionar categorias da biblioteca.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- COLUNA DA DIREITA: ADICIONAR (Sticky) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 sticky top-6 overflow-hidden">
                        <div class="p-6 bg-gradient-to-br from-indigo-600 to-blue-700 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="fa-solid fa-plus text-6xl text-white"></i>
                            </div>
                            <h3 class="text-lg font-bold text-white relative z-10 flex items-center gap-2">
                                <i class="fa-solid fa-circle-plus"></i> Adicionar da Biblioteca
                            </h3>
                            <p class="text-indigo-100 text-xs mt-1 relative z-10">Selecione os modelos para incluir neste percurso.</p>
                        </div>

                        <div class="p-6">
                            @if($categoriaModelosDisponiveis->isEmpty())
                                <div class="text-center py-8">
                                    <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3 text-green-500">
                                        <i class="fa-solid fa-check text-xl"></i>
                                    </div>
                                    <p class="text-slate-600 font-bold text-sm">Tudo pronto!</p>
                                    <p class="text-slate-400 text-xs mt-1">Todas as categorias disponíveis já foram adicionadas.</p>
                                    <a href="{{ route('organizador.modelos-categoria.index') }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 mt-4 inline-block hover:underline">
                                        Criar nova na biblioteca &rarr;
                                    </a>
                                </div>
                            @else
                                <form method="POST" action="{{ route('organizador.categorias.store', $percurso) }}" class="space-y-4">
                                    @csrf
                                    
                                    <div>
                                        <label for="categoria_modelos" class="block text-sm font-bold text-slate-700 mb-2">
                                            Selecione as Categorias
                                        </label>
                                        <div class="relative">
                                            <select name="categoria_modelos[]" id="categoria_modelos" multiple class="block w-full h-64 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-slate-50">
                                                @foreach($categoriaModelosDisponiveis as $modelo)
                                                    <option value="{{ $modelo->id }}" class="p-2 border-b border-slate-200 hover:bg-indigo-50 cursor-pointer">
                                                        {{ $modelo->nome }} ({{$modelo->genero}} | {{$modelo->idade_min}}-{{$modelo->idade_max}} anos)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="absolute bottom-2 right-4 text-[10px] text-slate-400 font-medium bg-white px-2 py-1 rounded shadow-sm border border-slate-100 pointer-events-none">
                                                Segure CTRL/CMD para selecionar várias
                                            </div>
                                        </div>
                                        <x-input-error :messages="$errors->get('categoria_modelos')" class="mt-2" />
                                    </div>
                                    
                                    <div class="pt-2">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-orange-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-orange-500/30 hover:-translate-y-0.5">
                                            <i class="fa-solid fa-plus mr-2"></i> Adicionar Selecionadas
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>