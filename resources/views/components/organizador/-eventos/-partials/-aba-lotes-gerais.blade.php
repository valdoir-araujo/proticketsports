<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    {{-- Coluna da Lista --}}
    <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Lotes de Inscrição Gerais Cadastrados</h3>
        <div class="space-y-3">
            @forelse ($evento->lotesInscricaoGeral as $lote)
                <div class="border rounded-md p-3 flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $lote->nome }} - <span class="text-green-600">R$ {{ number_format($lote->valor, 2, ',', '.') }}</span></p>
                        <p class="text-sm text-gray-500">
                            De {{ $lote->data_inicio->format('d/m/Y H:i') }} até {{ $lote->data_fim->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        {{-- Formulário de Remoção --}}
                        <form action="{{ route('organizador.eventos.lotes-gerais.destroy', ['evento' => $evento, 'lotes_gerai' => $lote]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este lote?');">
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

    {{-- Coluna do Formulário --}}
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Novo Lote Geral</h3>
        <form action="{{ route('organizador.eventos.lotes-gerais.store', $evento) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="nome_lote_geral" value="Nome do Lote (Ex: 1º Lote)" />
                <x-text-input id="nome_lote_geral" name="nome" type="text" class="mt-1 block w-full" required />
            </div>
            <div>
                <x-input-label for="valor_lote_geral" value="Valor (R$)" />
                <x-text-input id="valor_lote_geral" name="valor" type="number" step="0.01" class="mt-1 block w-full" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="data_inicio_lote_geral" value="Data de Início" />
                    <x-text-input id="data_inicio_lote_geral" name="data_inicio" type="datetime-local" class="mt-1 block w-full" required />
                </div>
                <div>
                    <x-input-label for="data_fim_lote_geral" value="Data de Fim" />
                    <x-text-input id="data_fim_lote_geral" name="data_fim" type="datetime-local" class="mt-1 block w-full" required />
                </div>
            </div>
            <div>
                <x-primary-button>Adicionar Lote Geral</x-primary-button>
            </div>
        </form>
    </div>
</div>