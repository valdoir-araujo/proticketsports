<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <i class="fa-solid fa-box-open text-orange-500"></i>
                Editar Produto: <span class="font-normal text-gray-600">{{ $produto->nome }}</span>
            </h2>
            <a href="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'produtos']) }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            <form method="POST" action="{{ route('organizador.produtos.update', [$evento, $produto]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    {{-- Coluna Esquerda: Informações Principais --}}
                    <div class="lg:col-span-2 space-y-6">
                        
                        {{-- Card: Dados Básicos --}}
                        <div class="bg-white p-6 sm:p-8 shadow-sm sm:rounded-xl border border-gray-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-6 border-b pb-2 flex items-center">
                                <i class="fa-regular fa-file-lines mr-2 text-gray-400"></i> Informações Básicas
                            </h3>
                            
                            <div class="space-y-5">
                                {{-- Nome --}}
                                <div>
                                    <x-input-label for="nome" value="Nome do Produto" />
                                    <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $produto->nome)" required placeholder="Ex: Camiseta Oficial 2024" />
                                    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                                </div>

                                {{-- Descrição --}}
                                <div>
                                    <x-input-label for="descricao" value="Descrição Detalhada" />
                                    <textarea id="descricao" name="descricao" rows="4" class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm" placeholder="Descreva o produto, material, cores...">{{ old('descricao', $produto->descricao) }}</textarea>
                                    <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    {{-- Valor --}}
                                    <div>
                                        <x-input-label for="valor" value="Valor Unitário (R$)" />
                                        <div class="relative mt-1">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm font-bold">R$</span>
                                            </div>
                                            <x-text-input id="valor" name="valor" type="number" step="0.01" class="block w-full pl-10" :value="old('valor', $produto->valor)" required placeholder="0.00" />
                                        </div>
                                        <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                                    </div>

                                    {{-- Estoque --}}
                                    <div>
                                        <x-input-label for="limite_estoque" value="Estoque Total Disponível" />
                                        <x-text-input id="limite_estoque" name="limite_estoque" type="number" class="mt-1 block w-full" :value="old('limite_estoque', $produto->limite_estoque)" placeholder="Vazio = Ilimitado" />
                                        <p class="text-xs text-gray-500 mt-1">Deixe em branco para estoque infinito.</p>
                                        <x-input-error :messages="$errors->get('limite_estoque')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Configurações --}}
                        <div class="bg-white p-6 sm:p-8 shadow-sm sm:rounded-xl border border-gray-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-6 border-b pb-2 flex items-center">
                                <i class="fa-solid fa-sliders mr-2 text-gray-400"></i> Configurações & Regras
                            </h3>

                            <div class="space-y-6">
                                {{-- Bloco de Gratuidade (Destaque) --}}
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <i class="fa-solid fa-gift text-orange-500 text-lg"></i>
                                        </div>
                                        <div class="ml-3 w-full">
                                            <x-input-label for="quantidade_gratuidade" value="Gratuidades (Brindes)" class="text-orange-800 font-bold" />
                                            <p class="text-xs text-orange-700 mb-2">Quantidade de itens que serão dados como cortesia (R$ 0,00) para os primeiros inscritos.</p>
                                            <x-text-input id="quantidade_gratuidade" name="quantidade_gratuidade" type="number" class="block w-full border-orange-300 focus:border-orange-500 focus:ring-orange-500" :value="old('quantidade_gratuidade', $produto->quantidade_gratuidade)" placeholder="0" />
                                            <x-input-error :messages="$errors->get('quantidade_gratuidade')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Limite por Inscrição --}}
                                <div>
                                    <x-input-label for="max_quantidade_por_inscricao" value="Limite Máximo por Inscrição" />
                                    <x-text-input id="max_quantidade_por_inscricao" name="max_quantidade_por_inscricao" type="number" class="mt-1 block w-full" :value="old('max_quantidade_por_inscricao', $produto->max_quantidade_por_inscricao)" placeholder="Ex: 2 (Vazio = Sem limite)" />
                                    <p class="text-xs text-gray-500 mt-1">Quantos itens desse tipo um único atleta pode comprar?</p>
                                    <x-input-error :messages="$errors->get('max_quantidade_por_inscricao')" class="mt-2" />
                                </div>

                                {{-- Toggles / Checkboxes --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <label for="requer_tamanho" class="flex items-start p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition select-none">
                                        <div class="flex h-5 items-center">
                                            <input id="requer_tamanho" name="requer_tamanho" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500" @checked(old('requer_tamanho', $produto->requer_tamanho))>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="block font-medium text-gray-700">Requer Tamanho</span>
                                            <span class="block text-gray-500 text-xs">Exige escolha (P, M, G...).</span>
                                        </div>
                                    </label>

                                    <label for="ativo" class="flex items-start p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition select-none">
                                        <div class="flex h-5 items-center">
                                            <input id="ativo" name="ativo" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500" @checked(old('ativo', $produto->ativo))>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="block font-medium text-gray-700">Produto Ativo</span>
                                            <span class="block text-gray-500 text-xs">Visível na loja e inscrição.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna Direita: Imagem e Ações --}}
                    <div class="space-y-6">
                        
                        {{-- Card: Imagem --}}
                        <div class="bg-white p-6 shadow-sm sm:rounded-xl border border-gray-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fa-regular fa-image mr-2 text-gray-400"></i> Imagem
                            </h3>
                            
                            <div class="mb-4">
                                @if($produto->imagem_url)
                                    <div class="relative group rounded-lg overflow-hidden border border-gray-200 aspect-square bg-gray-100 mb-4 shadow-sm">
                                        <img src="{{ asset('storage/' . $produto->imagem_url) }}" alt="Imagem atual" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <i class="fa-solid fa-eye text-white text-2xl mb-1"></i>
                                            <span class="text-white text-xs font-bold uppercase tracking-wider">Imagem Atual</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 flex flex-col items-center justify-center text-center aspect-square bg-gray-50 mb-4">
                                        <i class="fa-regular fa-image text-gray-400 text-4xl mb-2"></i>
                                        <span class="text-sm text-gray-500 font-medium">Sem imagem definida</span>
                                    </div>
                                @endif

                                <label class="block w-full">
                                    <span class="sr-only">Escolher nova imagem</span>
                                    <input type="file" name="imagem" id="imagem" accept="image/png, image/jpeg, image/jpg, image/webp"
                                        class="block w-full text-sm text-slate-500
                                        file:mr-4 file:py-2.5 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-bold
                                        file:bg-orange-50 file:text-orange-700
                                        hover:file:bg-orange-100
                                        cursor-pointer transition-colors
                                        "/>
                                </label>
                                <p class="text-[10px] text-gray-400 mt-2 text-center uppercase tracking-wide">JPG, PNG ou WEBP (Max 5MB)</p>
                                <x-input-error :messages="$errors->get('imagem')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Card: Ações (Sticky) --}}
                        <div class="bg-white p-6 shadow-sm sm:rounded-xl border border-gray-100 sticky top-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Publicação</h3>
                            
                            <div class="flex flex-col gap-3">
                                <x-primary-button class="w-full justify-center py-3 text-base shadow-lg shadow-gray-900/20">
                                    <i class="fa-solid fa-floppy-disk mr-2"></i> Salvar Alterações
                                </x-primary-button>
                                
                                <a href="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'produtos']) }}" class="inline-flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 hover:text-orange-600 transition ease-in-out duration-150 w-full">
                                    Cancelar
                                </a>
                            </div>
                            
                            <div class="mt-6 pt-4 border-t text-center">
                                <p class="text-xs text-gray-400">
                                    <span class="block">Criado em: {{ $produto->created_at->format('d/m/Y') }}</span>
                                    <span class="block mt-1">Atualizado: {{ $produto->updated_at->format('d/m/Y H:i') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>