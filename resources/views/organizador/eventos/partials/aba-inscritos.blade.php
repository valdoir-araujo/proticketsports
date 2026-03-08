{{-- Conteúdo da Aba "Inscritos" --}}
<div x-show="tab === 'inscritos'" style="display: none;">
    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <section>
            <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-lg font-medium text-gray-900">Lista de Inscritos</h2>
                    <p class="mt-1 text-sm text-gray-600">Veja e filtre todos os atletas que se inscreveram no seu evento.</p>
                </div>
                
                {{-- ========================================================== --}}
                {{-- ⬇️ CORREÇÃO 1: Removido 'w-full' daqui ⬇️ --}}
                {{-- ========================================================== --}}
                <div class="flex items-center space-x-2 flex-shrink-0 sm:w-auto">
                    
                    <form action="{{ route('organizador.eventos.togglePublicList', $evento) }}" method="POST" class="flex-grow sm:flex-grow-0">
                        @csrf
                        @if ($evento->lista_inscritos_publica)
                            {{-- A classe 'w-full' foi mantida aqui porque o DIV pai agora controla a largura --}}
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600" title="Clique para tornar a lista privada">
                                <i class="fa-solid fa-eye-slash w-4 h-4 mr-2"></i>
                                Ocultar Lista
                            </button>
                        @else
                            {{-- A classe 'w-full' foi mantida aqui porque o DIV pai agora controla a largura --}}
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700" title="Clique para tornar a lista pública">
                                <i class="fa-solid fa-eye w-4 h-4 mr-2"></i>
                                Publicar Lista
                            </button>
                        @endif
                    </form>

                    <a href="{{ route('organizador.eventos.exportarInscritos', $evento) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15m0-3-3-3m0 0-3 3m3-3v11.25" /></svg>
                        Exportar
                    </a>
                </div>
            </header>

            <form method="GET" action="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'inscritos']) }}" class="mt-6 border-t border-b py-4">
                <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
                    <div>
                        <label for="filtro_campo" class="block text-sm font-medium text-gray-700">Filtrar por</label>
                        <select name="filtro_campo" id="filtro_campo" class="mt-1 block w-full md:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="atleta" {{ request('filtro_campo') == 'atleta' ? 'selected' : '' }}>Atleta</option>
                            <option value="equipe" {{ request('filtro_campo') == 'equipe' ? 'selected' : '' }}>Equipe</option>
                            <option value="cidade" {{ request('filtro_campo') == 'cidade' ? 'selected' : '' }}>Cidade</option>
                            <option value="status" {{ request('filtro_campo') == 'status' ? 'selected' : '' }}>Status</option>
                            <option value="tipo" {{ request('filtro_campo') == 'tipo' ? 'selected' : '' }}>Tipo</option>
                        </select>
                    </div>
                    <div class="flex-grow">
                        <label for="filtro_valor" class="block text-sm font-medium text-gray-700">Valor</label>
                        <input type="text" name="filtro_valor" id="filtro_valor" value="{{ request('filtro_valor') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Digite o termo da pesquisa...">
                    </div>
                    <div class="flex items-center space-x-2">
                        {{-- ========================================================== --}}
                        {{-- ⬇️ CORREÇÃO 2: Removido 'w-full' daqui ⬇️ --}}
                        {{-- ========================================================== --}}
                        <button type="submit" class="md:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">Filtrar</button>
                        
                        {{-- ========================================================== --}}
                        {{-- ⬇️ CORREÇÃO 3: Removido 'w-full' daqui ⬇️ --}}
                        {{-- ========================================================== --}}
                        <a href="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'inscritos']) }}" class="md:w-auto text-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm font-medium">Limpar</a>
                    </div>
                </div>
            </form>

            {{-- Container da tabela com a linha de fechamento --}}
            <div class="mt-6 overflow-x-auto border-b border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atleta</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipe</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inscricoes as $inscricao)
                            <tr>
                                <td class="px-2 py-1 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inscricao->atleta->user->name ?? 'N/A' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-700">{{ $inscricao->categoria->nome ?? 'N/A' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-700">{{ $inscricao->atleta->cidade?->nome ?? 'Exterior' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-700">{{ $inscricao->equipe->nome ?? 'Individual' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    @if($inscricao->status == 'confirmada')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmada</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Aguardando Pag.</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-700">
                                    @if($inscricao->metodo_pagamento === 'Cortesia')
                                        <span class="font-semibold text-indigo-600">Cortesia</span>
                                    @else
                                        <span>Normal</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        @if($inscricao->status == 'aguardando_pagamento')
                                            <form action="{{ route('organizador.inscricoes.confirmarCortesia', $inscricao) }}" method="POST" onsubmit="return confirm('Confirmar esta inscrição como CORTESIA?');">
                                                @csrf
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs">Cortesia</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('inscricao.edit', $inscricao) }}" class="text-blue-600 hover:text-blue-900 font-semibold text-xs">Editar</a>
                                        @if($inscricao->status == 'confirmada')
                                            <form action="#" method="POST" onsubmit="return confirm('Tem a certeza que deseja CANCELAR esta inscrição?');">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-xs">Cancelar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-2 py-1 text-center text-sm text-gray-500">Nenhum inscrito encontrado com os filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $inscricoes->withQueryString()->links('pagination::tailwind', ['pageName' => 'inscritosPage']) }}
            </div>
        </section>
    </div>
</div>