{{-- Envolvemos todo o componente em um x-data para controlar o estado do modal --}}
<div x-data="{
    showEditModal: false,
    editingLote: {},
    openEditModal(lote) {
        // Clonamos o objeto para evitar alterações reativas indesejadas
        this.editingLote = JSON.parse(JSON.stringify(lote));
        
        // Ajustamos o formato da data para o input datetime-local (YYYY-MM-DDTHH:mm)
        this.editingLote.data_inicio = this.editingLote.data_inicio.slice(0, 16);
        this.editingLote.data_fim = this.editingLote.data_fim.slice(0, 16);

        this.showEditModal = true;
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Coluna da Lista --}}
        <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Lotes de Inscrição Gerais Cadastrados</h3>
            <div class="space-y-3">
                @forelse ($evento->lotesInscricaoGeral->sortBy('data_inicio') as $lote)
                    <div class="border rounded-md p-3 flex justify-between items-center">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $lote->nome }} - 
                                <span class="text-green-600">R$ {{ number_format($lote->valor, 2, ',', '.') }}</span>
                                
                                {{-- 🟢 AJUSTE AQUI: Exibindo a taxa concatenada --}}
                                <span class="text-xs text-gray-500 font-normal ml-1">
                                    (+ {{ number_format($evento->taxa_aplicada, 1, ',', '.') }}% taxa)
                                </span>
                            </p>
                            <p class="text-sm text-gray-500">
                                De {{ $lote->data_inicio->format('d/m/Y H:i') }} até {{ $lote->data_fim->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            {{-- BOTÃO DE EDITAR --}}
                            <button @click.prevent="openEditModal({{ Js::from($lote) }})" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                Editar
                            </button>

                            <form action="{{ route('organizador.lotes-gerais.destroy', [$evento, $lote]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este lote?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold">Remover</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Nenhum lote geral cadastrado para este evento.</p>
                @endforelse
            </div>
        </div>

        {{-- Coluna do Formulário de Adição (Mantida igual) --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Novo Lote Geral</h3>
            <form action="{{ route('organizador.lotes-gerais.store', $evento) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="nome_lote_geral" value="Nome do Lote (Ex: 1º Lote, Lote Promocional)" />
                    <x-text-input id="nome_lote_geral" name="nome" type="text" class="mt-1 block w-full" required value="{{ old('nome') }}" />
                </div>
                <div>
                    <x-input-label for="valor_lote_geral" value="Valor (R$)" />
                    <x-text-input id="valor_lote_geral" name="valor" type="number" step="0.01" class="mt-1 block w-full" required value="{{ old('valor') }}" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="data_inicio_lote_geral" value="Data de Início" />
                        <x-text-input id="data_inicio_lote_geral" name="data_inicio" type="datetime-local" class="mt-1 block w-full" required value="{{ old('data_inicio') }}" />
                    </div>
                    <div>
                        <x-input-label for="data_fim_lote_geral" value="Data de Fim" />
                        <x-text-input id="data_fim_lote_geral" name="data_fim" type="datetime-local" class="mt-1 block w-full" required value="{{ old('data_fim') }}" />
                    </div>
                </div>
                <div>
                    <x-primary-button>Adicionar Lote Geral</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE EDIÇÃO (Mantido igual) --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="showEditModal = false" x-cloak>
        <div @click.away="showEditModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Lote Geral</h3>
            
            <form x-show="editingLote.id" 
                  :action="'{{ route('organizador.lotes-gerais.update', [$evento, 'LOTE_ID_PLACEHOLDER']) }}'.replace('LOTE_ID_PLACEHOLDER', editingLote.id)" 
                  method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="edit_nome_lote" value="Nome do Lote" />
                    <x-text-input id="edit_nome_lote" name="nome" type="text" class="mt-1 block w-full" required x-model="editingLote.nome" />
                </div>
                <div>
                    <x-input-label for="edit_valor_lote" value="Valor (R$)" />
                    <x-text-input id="edit_valor_lote" name="valor" type="number" step="0.01" class="mt-1 block w-full" required x-model="editingLote.valor" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="edit_data_inicio_lote" value="Data de Início" />
                        <x-text-input id="edit_data_inicio_lote" name="data_inicio" type="datetime-local" class="mt-1 block w-full" required x-model="editingLote.data_inicio" />
                    </div>
                    <div>
                        <x-input-label for="edit_data_fim_lote" value="Data de Fim" />
                        <x-text-input id="edit_data_fim_lote" name="data_fim" type="datetime-local" class="mt-1 block w-full" required x-model="editingLote.data_fim" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-x-4 border-t pt-4">
                    <button @click.prevent="showEditModal = false" type="button" class="text-sm font-semibold text-gray-700">Cancelar</button>
                    <x-primary-button>Salvar Alterações</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>