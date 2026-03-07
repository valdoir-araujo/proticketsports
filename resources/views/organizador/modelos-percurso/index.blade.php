<x-app-layout>
    {{-- Header "Hero" Moderno --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-24 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10">
                    <div class="flex items-center gap-2 mb-2 text-blue-200 text-sm font-medium">
                        <a href="{{ route('organizador.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                        <i class="fa-solid fa-chevron-right text-xs opacity-50"></i>
                        <span class="text-white">Configurações</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Biblioteca de Percursos
                    </h2>
                    <p class="text-blue-100 mt-2 text-lg font-light">
                        Crie modelos padrão (Ex: Pro, Sport, Kids) para agilizar o cadastro das categorias nos seus eventos.
                    </p>
                </div>
                
                <div class="hidden md:block">
                    <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 shadow-lg">
                        <i class="fa-solid fa-route text-3xl text-orange-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 -mt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Feedback Messages --}}
            @if(session('sucesso'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center shadow-sm animate-fade-in">
                    <i class="fa-solid fa-circle-check mr-3 text-lg"></i>
                    {{ session('sucesso') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 shadow-sm animate-fade-in">
                    <p class="font-bold flex items-center mb-2"><i class="fa-solid fa-circle-exclamation mr-2"></i> Atenção:</p>
                    <ul class="list-disc list-inside ml-2 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- COLUNA DA ESQUERDA: LISTA DE MODELOS --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-list-ul text-indigo-500"></i> Modelos Cadastrados
                            </h3>
                            <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs font-bold text-slate-500">
                                {{ count($percursoModelos) }} Total
                            </span>
                        </div>

                        <div class="p-6">
                            @forelse($percursoModelos as $modelo)
                                <div class="group flex items-center justify-between p-4 mb-3 bg-white rounded-xl border border-slate-200 hover:border-indigo-300 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            {{ substr($modelo->descricao, 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800 text-lg group-hover:text-indigo-700 transition-colors">{{ $modelo->descricao }}</h4>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Código</span>
                                                <span class="text-xs font-mono text-slate-600">{{ $modelo->codigo }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        {{-- Botão Editar (Simulado/Desabilitado conforme original) --}}
                                        <button disabled class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-300 border border-transparent hover:bg-slate-50 cursor-not-allowed" title="Edição em breve">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        
                                        <form action="{{ route('organizador.modelos-percurso.destroy', $modelo->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover o modelo {{ $modelo->descricao }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-9 h-9 rounded-lg flex items-center justify-center text-red-400 hover:text-white hover:bg-red-500 border border-slate-100 hover:border-red-500 transition-all shadow-sm" title="Remover">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 px-6 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                                    <div class="w-16 h-16 mx-auto bg-white rounded-full flex items-center justify-center shadow-sm mb-4 text-slate-300">
                                        <i class="fa-solid fa-route text-2xl"></i>
                                    </div>
                                    <h4 class="text-slate-600 font-bold mb-1">Nenhum modelo encontrado</h4>
                                    <p class="text-sm text-slate-400 mb-6">Cadastre modelos de percurso para padronizar seus eventos.</p>
                                    <p class="text-xs text-orange-500 bg-orange-50 inline-block px-3 py-1 rounded-full border border-orange-100">
                                        <i class="fa-solid fa-lightbulb mr-1"></i> Dica: Use nomes genéricos como "Pró", "Sport", "Amador".
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- COLUNA DA DIREITA: FORMULÁRIO --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 sticky top-6">
                        <div class="p-6 bg-gradient-to-br from-indigo-600 to-blue-700 rounded-t-2xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="fa-solid fa-plus text-6xl text-white"></i>
                            </div>
                            <h3 class="text-lg font-bold text-white relative z-10 flex items-center gap-2">
                                <i class="fa-solid fa-circle-plus"></i> Novo Modelo
                            </h3>
                            <p class="text-indigo-100 text-xs mt-1 relative z-10">Adicione um novo padrão à sua biblioteca.</p>
                        </div>

                        <div class="p-6">
                            <form method="POST" action="{{ route('organizador.modelos-percurso.store') }}" class="space-y-5">
                                @csrf
                                
                                <div>
                                    <x-input-label for="descricao" value="Descrição" class="text-slate-700 font-bold" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa-solid fa-tag text-slate-400 text-sm"></i>
                                        </div>
                                        <x-text-input id="descricao" name="descricao" type="text" class="pl-9 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" :value="old('descricao')" required placeholder="Ex: Percurso Pro" />
                                    </div>
                                    <x-input-error :messages="$errors->get('descricao')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="codigo" value="Código Fixo (Slug)" class="text-slate-700 font-bold" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa-solid fa-barcode text-slate-400 text-sm"></i>
                                        </div>
                                        <x-text-input id="codigo" name="codigo" type="text" class="pl-9 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg uppercase" :value="old('codigo')" required placeholder="Ex: PRO" />
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-1.5 leading-snug">
                                        Usado internamente para agrupar rankings. Evite espaços e caracteres especiais.
                                    </p>
                                    <x-input-error :messages="$errors->get('codigo')" class="mt-1" />
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-orange-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-orange-500/30 hover:-translate-y-0.5">
                                        <i class="fa-solid fa-save mr-2"></i> Salvar Modelo
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="px-6 pb-6 pt-2">
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex gap-3 items-start">
                                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                                <p class="text-xs text-blue-700">
                                    Estes modelos serão visíveis <strong>apenas para a sua organização</strong> e poderão ser reutilizados em qualquer campeonato futuro.
                                </p>
                            </div>
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