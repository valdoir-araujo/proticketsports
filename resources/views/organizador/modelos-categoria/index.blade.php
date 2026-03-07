<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Biblioteca de Categorias
        </h2>
        <p class="text-sm text-gray-600 mt-1">Crie e gerencie os modelos de categoria.</p>
    </x-slot>

    {{-- X-DATA: Gerencia o estado da Modal e dos Accordions --}}
    <div class="py-12" x-data="{ 
        open: null, 
        editModal: {
            isOpen: false,
            id: null,
            percurso_modelo_id: '',
            nome: '',
            // 'codigo' REMOVIDO DAQUI
            genero: '',
            idade_min: '',
            idade_max: '',
        },
        openEdit(categoria) {
            this.editModal.id = categoria.id;
            this.editModal.percurso_modelo_id = categoria.percurso_modelo_id;
            this.editModal.nome = categoria.nome;
            // 'codigo' REMOVIDO DAQUI
            this.editModal.genero = categoria.genero;
            this.editModal.idade_min = categoria.idade_min;
            this.editModal.idade_max = categoria.idade_max;
            this.editModal.isOpen = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                {{-- ESQUERDA: LISTA DE CATEGORIAS --}}
                <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Categorias Cadastradas</h3>
                    
                    <div class="space-y-2">
                        @forelse($percursoModelosComCategorias as $percursoModelo)
                            <div class="border rounded-md">
                                {{-- Cabeçalho do Percurso (Accordion) --}}
                                <div @click="open = (open === {{ $percursoModelo->id }} ? null : {{ $percursoModelo->id }})" 
                                     class="flex justify-between items-center p-3 cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                    <h4 class="font-bold text-gray-800 text-indigo-700">
                                        <i class="fa-solid fa-route mr-2"></i> {{ $percursoModelo->descricao }}
                                    </h4>
                                    <i class="fa-solid fa-plus text-indigo-600 transition-transform duration-300" 
                                       :class="{ 'rotate-45': open === {{ $percursoModelo->id }} }"></i>
                                </div>

                                {{-- Lista de Categorias do Percurso --}}
                                <div x-show="open === {{ $percursoModelo->id }}" x-transition class="p-3 space-y-3 border-t">
                                    @forelse($percursoModelo->categoriaModelos as $categoriaModelo)
                                        <div class="p-3 border rounded-md hover:border-indigo-300 transition-colors">
                                            <div class="flex justify-between items-start">
                                                
                                                {{-- Dados da Categoria --}}
                                                <div>
                                                    <p class="font-bold text-gray-800">{{ $categoriaModelo->nome }}</p>
                                                    {{-- Exibe o código apenas como informação visual (badge cinza) --}}
                                                    <span class="text-[10px] text-gray-500 font-mono bg-gray-100 px-2 py-0.5 rounded mt-1 inline-block" title="Código Gerado Automaticamente">
                                                        {{ $categoriaModelo->codigo }}
                                                    </span>
                                                    <div class="text-xs text-gray-600 mt-2 flex items-center space-x-3">
                                                        <span><i class="fa-solid fa-venus-mars text-gray-400"></i> {{ $categoriaModelo->genero }}</span>
                                                        <span><i class="fa-solid fa-cake-candles text-gray-400"></i> {{ $categoriaModelo->idade_min }} - {{ $categoriaModelo->idade_max }} anos</span>
                                                    </div>
                                                </div>
                                                
                                                {{-- 
                                                    CORREÇÃO: Botões lado a lado 
                                                    Usamos 'flex' e 'gap-3' para alinhar horizontalmente
                                                --}}
                                                <div class="flex items-center gap-3">
                                                    {{-- Botão Editar --}}
                                                    <button type="button" 
                                                            @click="openEdit({{ $categoriaModelo }})"
                                                            class="text-sm text-blue-600 hover:text-blue-800 font-semibold"
                                                            title="Editar Categoria">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>

                                                    {{-- Botão Excluir --}}
                                                    <form action="{{ route('organizador.modelos-categoria.destroy', $categoriaModelo->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta categoria?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold" title="Excluir Categoria">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500 text-center py-2">Nenhuma categoria cadastrada neste percurso.</p>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500">Nenhum percurso cadastrado.</p>
                        @endforelse
                    </div>
                </div>

                {{-- DIREITA: FORMULÁRIO DE CRIAÇÃO --}}
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm h-fit">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Nova Categoria</h3>
                    <form method="POST" action="{{ route('organizador.modelos-categoria.store') }}" class="space-y-4">
                        @csrf
                        
                        <div>
                            <x-input-label for="new_percurso" value="Percurso" />
                            <select name="percurso_modelo_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="" disabled selected>Selecione...</option>
                                @foreach($percursoModelosParaFormulario as $modelo)
                                    <option value="{{ $modelo->id }}">{{ $modelo->descricao }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="new_nome" value="Nome (Ex: Elite)" />
                            <x-text-input name="nome" type="text" class="mt-1 block w-full" placeholder="Nome da Categoria" required />
                        </div>

                        {{-- CAMPO CÓDIGO REMOVIDO DAQUI --}}

                        <div>
                            <x-input-label for="new_genero" value="Gênero" />
                            <select name="genero" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="Unissex">Unissex</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="new_min" value="Idade Min" />
                                <x-text-input name="idade_min" type="number" class="mt-1 block w-full" value="0" required />
                            </div>
                            <div>
                                <x-input-label for="new_max" value="Idade Max" />
                                <x-text-input name="idade_max" type="number" class="mt-1 block w-full" value="99" required />
                            </div>
                        </div>

                        <div class="pt-2">
                            <x-primary-button class="w-full justify-center">Criar Categoria</x-primary-button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        {{-- MODAL DE EDIÇÃO --}}
        <div x-show="editModal.isOpen" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- Overlay Escuro --}}
                <div x-show="editModal.isOpen" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="editModal.isOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Painel da Modal --}}
                <div x-show="editModal.isOpen"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-pen text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Editar Categoria
                                </h3>
                                
                                <form method="POST" :action="'{{ url('organizador/modelos-categoria') }}/' + editModal.id" class="mt-4 space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <x-input-label value="Percurso" />
                                        <select name="percurso_modelo_id" x-model="editModal.percurso_modelo_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            @foreach($percursoModelosParaFormulario as $modelo)
                                                <option value="{{ $modelo->id }}">{{ $modelo->descricao }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <x-input-label value="Nome" />
                                        <x-text-input name="nome" x-model="editModal.nome" class="mt-1 block w-full" />
                                    </div>

                                    {{-- CAMPO CÓDIGO REMOVIDO DAQUI TAMBÉM --}}

                                    <div>
                                        <x-input-label value="Gênero" />
                                        <select name="genero" x-model="editModal.genero" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                            <option value="Unissex">Unissex</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label value="Idade Min" />
                                            <x-text-input name="idade_min" type="number" x-model="editModal.idade_min" class="mt-1 block w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Idade Max" />
                                            <x-text-input name="idade_max" type="number" x-model="editModal.idade_max" class="mt-1 block w-full" />
                                        </div>
                                    </div>

                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                            Salvar Alterações
                                        </button>
                                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm" @click="editModal.isOpen = false">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>