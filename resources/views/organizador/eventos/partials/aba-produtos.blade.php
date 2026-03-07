{{-- Conteúdo da Aba "Produtos" --}}
<div x-show="tab === 'produtos'" style="display: none;" class="space-y-8 animate-fade-in-up">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {{-- Coluna da Esquerda: Lista de Produtos (2/3 width) --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-orange-500"></i>
                        Produtos Cadastrados
                    </h3>
                    <span class="text-xs font-semibold px-3 py-1 bg-slate-100 text-slate-500 rounded-full">
                        {{ $evento->produtosOpcionais->count() }} itens
                    </span>
                </div>

                <div class="grid gap-4">
                    @forelse ($evento->produtosOpcionais as $produto)
                        <div class="group relative bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-all duration-300 hover:border-orange-200">
                            <div class="flex items-start gap-5">
                                {{-- Imagem --}}
                                <div class="relative shrink-0 w-24 h-24 bg-slate-100 rounded-lg overflow-hidden border border-slate-100 group-hover:shadow-sm">
                                    <img src="{{ $produto->imagem_url ? asset('storage/' . $produto->imagem_url) : 'https://placehold.co/100x100/f1f5f9/94a3b8?text=Sem+Foto' }}" 
                                         alt="{{ $produto->nome }}" 
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </div>

                                {{-- Informações --}}
                                <div class="flex-grow min-w-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-lg font-bold text-slate-800 leading-tight group-hover:text-orange-600 transition-colors">
                                                {{ $produto->nome }}
                                            </h4>
                                            <p class="text-sm text-slate-500 mt-1 line-clamp-1">{{ $produto->descricao }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-lg font-black text-slate-800">
                                                R$ {{ number_format($produto->valor, 2, ',', '.') }}
                                            </span>
                                            @if($produto->ativo)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-wide">
                                                    Ativo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wide">
                                                    Inativo
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Detalhes (Badges) --}}
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <span class="inline-flex items-center text-xs font-medium px-2 py-1 bg-slate-50 text-slate-600 rounded border border-slate-100" title="Estoque Atual">
                                            <i class="fa-solid fa-cubes mr-1.5 text-slate-400"></i>
                                            Estoque: {{ $produto->limite_estoque ?? '∞' }}
                                        </span>
                                        @if($produto->requer_tamanho)
                                            <span class="inline-flex items-center text-xs font-medium px-2 py-1 bg-blue-50 text-blue-600 rounded border border-blue-100">
                                                <i class="fa-solid fa-ruler-combined mr-1.5"></i> Tamanho
                                            </span>
                                        @endif
                                        @if($produto->max_quantidade_por_inscricao)
                                            <span class="inline-flex items-center text-xs font-medium px-2 py-1 bg-slate-50 text-slate-600 rounded border border-slate-100">
                                                Max/Pessoa: {{ $produto->max_quantidade_por_inscricao }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Info Gratuidade --}}
                                    @if($produto->quantidade_gratuidade > 0)
                                        @php
                                            $saldoGratuidade = max(0, $produto->quantidade_gratuidade - $produto->gratuidades_consumidas);
                                        @endphp
                                        <div class="mt-3 bg-orange-50 border border-orange-100 rounded-lg p-2 flex items-center gap-3 text-xs text-orange-800">
                                            <div class="flex items-center font-bold shrink-0">
                                                <i class="fa-solid fa-gift mr-1.5 text-orange-500"></i> Cortesia:
                                            </div>
                                            <div class="flex gap-3 opacity-90 w-full justify-between sm:justify-start">
                                                <span>Total: <b>{{ $produto->quantidade_gratuidade }}</b></span>
                                                <span class="w-px h-3 bg-orange-200 hidden sm:block"></span>
                                                <span>Usadas: <b>{{ $produto->gratuidades_consumidas }}</b></span>
                                                <span class="w-px h-3 bg-orange-200 hidden sm:block"></span>
                                                <span class="{{ $saldoGratuidade == 0 ? 'text-red-600 font-bold' : '' }}">
                                                    Restam: <b>{{ $saldoGratuidade }}</b>
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Ações --}}
                                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 bg-white/90 p-1 rounded-lg shadow-sm backdrop-blur-sm border border-slate-100">
                                    <a href="{{ route('organizador.produtos.edit', [$evento, $produto]) }}" 
                                       class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('organizador.produtos.destroy', [$evento, $produto]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este produto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 transition-colors" title="Excluir">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 px-6 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <i class="fa-solid fa-box-open text-2xl text-slate-300"></i>
                            </div>
                            <h4 class="text-slate-600 font-bold">Nenhum produto cadastrado</h4>
                            <p class="text-slate-400 text-sm mt-1">Utilize o formulário ao lado para adicionar itens à loja.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Coluna da Direita: Formulário (1/3 width) --}}
        <div class="xl:col-span-1">
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-100 sticky top-6">
                <div class="mb-6 pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Novo Produto</h3>
                    <p class="text-xs text-slate-400">Preencha os dados para adicionar à loja.</p>
                </div>

                <form action="{{ route('organizador.produtos.store', $evento) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    
                    {{-- Nome --}}
                    <div>
                        <x-input-label for="nome_produto" value="Nome do Produto" class="text-xs uppercase text-slate-500 font-bold tracking-wider mb-1" />
                        <x-text-input id="nome_produto" name="nome" type="text" class="w-full bg-slate-50 border-slate-200 focus:border-orange-500 focus:ring-orange-500 rounded-lg" placeholder="Ex: Camiseta 2024" required :value="old('nome')" />
                        <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                    </div>

                    {{-- Preço e Estoque (Grid) --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="valor_produto" value="Preço (R$)" class="text-xs uppercase text-slate-500 font-bold tracking-wider mb-1" />
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-slate-400 text-sm font-bold">R$</span>
                                <x-text-input id="valor_produto" name="valor" type="number" step="0.01" class="w-full pl-9 bg-slate-50 border-slate-200 focus:border-orange-500 focus:ring-orange-500 rounded-lg font-bold text-slate-700" placeholder="0.00" required :value="old('valor')" />
                            </div>
                            <x-input-error :messages="$errors->get('valor')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="limite_estoque" value="Estoque" class="text-xs uppercase text-slate-500 font-bold tracking-wider mb-1" />
                            <x-text-input id="limite_estoque" name="limite_estoque" type="number" class="w-full bg-slate-50 border-slate-200 focus:border-orange-500 focus:ring-orange-500 rounded-lg" placeholder="∞" :value="old('limite_estoque')" />
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div>
                        <x-input-label for="produto_descricao" value="Descrição" class="text-xs uppercase text-slate-500 font-bold tracking-wider mb-1" />
                        <textarea id="produto_descricao" name="descricao" rows="2" class="w-full bg-slate-50 border-slate-200 rounded-lg focus:border-orange-500 focus:ring-orange-500 text-sm" placeholder="Detalhes do item...">{{ old('descricao') }}</textarea>
                    </div>

                    {{-- Imagem --}}
                    <div>
                        <x-input-label for="imagem" value="Foto do Produto" class="text-xs uppercase text-slate-500 font-bold tracking-wider mb-1" />
                        <input id="imagem" name="imagem" type="file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer border border-slate-200 rounded-lg bg-slate-50"/>
                    </div>

                    {{-- Configurações Avançadas (Toggle) --}}
                    <div x-data="{ advanced: false }" class="border-t border-slate-100 pt-4">
                        <button type="button" @click="advanced = !advanced" class="flex items-center justify-between w-full text-xs font-bold text-slate-500 hover:text-orange-600 transition-colors uppercase tracking-wider mb-3">
                            <span>Configurações Avançadas</span>
                            <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="{ 'rotate-180': advanced }"></i>
                        </button>

                        <div x-show="advanced" x-collapse style="display: none;" class="space-y-4">
                            
                            {{-- Gratuidade --}}
                            <div class="bg-orange-50 p-3 rounded-lg border border-orange-100">
                                <x-input-label for="quantidade_gratuidade" value="Gratuidades (Cortesia)" class="text-orange-800 text-[10px] uppercase font-bold mb-1" />
                                <x-text-input id="quantidade_gratuidade" name="quantidade_gratuidade" type="number" class="w-full h-8 text-sm bg-white border-orange-200 focus:border-orange-500 focus:ring-orange-500 rounded" placeholder="0" :value="old('quantidade_gratuidade', 0)" />
                                <p class="text-[10px] text-orange-600 mt-1 leading-tight">Qtd. de itens grátis para os primeiros inscritos.</p>
                            </div>

                            {{-- Limite por Inscrição --}}
                            <div>
                                <x-input-label for="max_quantidade_por_inscricao" value="Max. por Pessoa" class="text-xs text-slate-500 font-bold mb-1" />
                                <x-text-input id="max_quantidade_por_inscricao" name="max_quantidade_por_inscricao" type="number" class="w-full h-9 text-sm bg-slate-50 border-slate-200 rounded-lg" placeholder="Sem limite" :value="old('max_quantidade_por_inscricao')" />
                            </div>

                            {{-- Switches --}}
                            <div class="space-y-3 pt-2">
                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" name="requer_tamanho" value="1" class="sr-only peer" @checked(old('requer_tamanho'))>
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500"></div>
                                    </div>
                                    <span class="ml-3 text-xs font-medium text-slate-600 group-hover:text-slate-800">Requer Tamanho</span>
                                </label>

                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" name="ativo" value="1" class="sr-only peer" checked>
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                    </div>
                                    <span class="ml-3 text-xs font-medium text-slate-600 group-hover:text-slate-800">Visível na Loja</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Botão Salvar --}}
                    <button type="submit" class="w-full py-3 px-4 bg-slate-900 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg hover:shadow-orange-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Adicionar Produto</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>