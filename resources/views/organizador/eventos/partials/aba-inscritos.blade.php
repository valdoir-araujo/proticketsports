{{-- Conteúdo da Aba "Inscritos" --}}
<div x-data="{ comprovanteUrl: null }">
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

            <form method="GET" action="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'inscritos']) }}" id="form-filtro-inscritos" x-ref="formFiltro" class="mt-6 border-t border-b py-4" x-data="{ filtroCampo: @json(request('filtro_campo', 'atleta')), filtroValor: @json(old('filtro_valor', request('filtro_valor', ''))) }">
                <input type="hidden" name="tab" value="inscritos">
                <input type="hidden" name="filtro_campo" :value="filtroCampo">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="flex-1">
                        <label for="filtro_valor" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fa-solid fa-search text-gray-400"></i>
                            </div>
                            <input type="text" name="filtro_valor" id="filtro_valor" x-model="filtroValor"
                                   @input.debounce.400ms="$refs.formFiltro.submit()"
                                   class="block w-full rounded-xl border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-slate-50"
                                   placeholder="Digite o nome do atleta, equipe, cidade...">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <select x-model="filtroCampo" @change="$refs.formFiltro.submit()" class="rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-white">
                            <option value="atleta">Atleta</option>
                            <option value="equipe">Equipe</option>
                            <option value="cidade">Cidade</option>
                            <option value="status">Status</option>
                            <option value="tipo">Tipo</option>
                        </select>
                        <a href="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'inscritos']) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 text-sm font-medium">Limpar</a>
                    </div>
                </div>
            </form>

            {{-- Container da tabela com a linha de fechamento --}}
            <div class="mt-6 overflow-x-auto border-b border-gray-200">
                <table class="min-w-full divide-y divide-gray-200" style="min-width: 920px;">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 240px;">Atleta</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 100px;">Categoria</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap" style="min-width: 150px;">Cidade</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 150px;">Equipe</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 100px;">Status</th>
                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 70px;">Tipo</th>
                            <th scope="col" class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 180px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inscricoes as $inscricao)
                            <tr>
                                <td class="px-2 py-1 whitespace-nowrap text-sm font-medium text-gray-900" style="min-width: 240px;">{{ $inscricao->atleta->user->name ?? 'N/A' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-700">{{ $inscricao->categoria->nome ?? 'N/A' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-700" style="min-width: 150px;">{{ $inscricao->atleta->cidade ? ($inscricao->atleta->cidade->nome . ($inscricao->atleta->cidade->estado ? '/' . $inscricao->atleta->cidade->estado->uf : '')) : 'Exterior' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-700" style="min-width: 150px;">{{ $inscricao->equipe->nome ?? 'Individual' }}</td>
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
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        {{-- Comprovante --}}
                                        @if($inscricao->comprovante_pagamento_url)
                                            <button type="button" @click="comprovanteUrl = '{{ route('organizador.inscricoes.comprovante', $inscricao) }}'" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-indigo-600 transition-colors" title="Ver comprovante de pagamento">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 cursor-not-allowed" title="Sem comprovante anexado">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </span>
                                        @endif

                                        {{-- Pagamento (somente eventos com pagamento manual) --}}
                                        @if($evento->pagamento_manual)
                                            @if($inscricao->status === 'confirmada')
                                                <form action="{{ route('organizador.inscricoes.toggleConfirmarPagamento', $inscricao) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 hover:text-amber-800 transition-colors" title="Marcar como pendente">
                                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('organizador.inscricoes.toggleConfirmarPagamento', $inscricao) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-green-600 hover:bg-green-50 hover:text-green-800 transition-colors" title="Confirmar pagamento">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        {{-- Cortesia (sempre disponível para inscrições pendentes) --}}
                                        @if($inscricao->status == 'aguardando_pagamento')
                                            <form action="{{ route('organizador.inscricoes.confirmarCortesia', $inscricao) }}" method="POST" class="inline" onsubmit="return confirm('Confirmar esta inscrição como CORTESIA?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 transition-colors" title="Confirmar como cortesia">
                                                    <i class="fa-solid fa-gift"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Editar (sempre disponível) --}}
                                        <a href="{{ route('inscricao.edit', $inscricao) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-800 transition-colors" title="Editar inscrição">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        {{-- Cancelar (somente inscrições confirmadas) --}}
                                        @if($inscricao->status == 'confirmada')
                                            <form action="#" method="POST" class="inline" onsubmit="return confirm('Tem a certeza que deseja CANCELAR esta inscrição?');">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors" title="Cancelar inscrição">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
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

            {{-- Legenda dos ícones de ação --}}
            <div class="mt-3 text-xs text-slate-500 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-1">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 text-slate-600">
                        <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                    </span>
                    <span>Ver comprovante (quando houver)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-green-50 text-green-700">
                        <i class="fa-solid fa-circle-check text-[11px]"></i>
                    </span>
                    <span>Confirmar pagamento (somente em eventos com pagamento manual)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-amber-50 text-amber-700">
                        <i class="fa-solid fa-clock-rotate-left text-[11px]"></i>
                    </span>
                    <span>Voltar inscrição para pendente</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-indigo-50 text-indigo-700">
                        <i class="fa-solid fa-gift text-[11px]"></i>
                    </span>
                    <span>Marcar como cortesia</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-700">
                        <i class="fa-solid fa-pen text-[11px]"></i>
                    </span>
                    <span>Editar dados da inscrição</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-red-50 text-red-700">
                        <i class="fa-solid fa-xmark text-[11px]"></i>
                    </span>
                    <span>Cancelar inscrição</span>
                </div>
            </div>

            <div class="mt-4">
                {{ $inscricoes->withQueryString()->links('pagination::tailwind', ['pageName' => 'inscritosPage']) }}
            </div>
        </section>
    </div>

    {{-- Modal: Comprovante de pagamento --}}
    <div x-show="comprovanteUrl" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         @keydown.escape.window="comprovanteUrl = null">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60" @click="comprovanteUrl = null" aria-hidden="true"></div>
            <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50 rounded-t-xl">
                    <h3 class="text-lg font-bold text-slate-800">Comprovante de pagamento</h3>
                    <div class="flex items-center gap-2">
                        <a :href="comprovanteUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Abrir em nova aba">
                            <i class="fa-solid fa-external-link-alt"></i> Abrir em nova aba
                        </a>
                        <button type="button" @click="comprovanteUrl = null" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" title="Fechar">
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="flex-1 min-h-0 p-4 overflow-hidden">
                    <iframe :src="comprovanteUrl" class="w-full h-full min-h-[70vh] rounded-lg border border-slate-200 bg-slate-50" title="Comprovante"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>