{{-- Aba: Lotes de Inscrição Gerais --}}
<div x-data="{
    showEditModal: false,
    editingLote: {},
    openEditModal(lote) {
        this.editingLote = { ...lote };
        this.showEditModal = true;
    }
}">
    {{-- Cabeçalho da seção --}}
    <div class="flex items-center gap-3 text-slate-700 mb-6">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
            <i class="fa-solid fa-layer-group text-xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">Lotes de Inscrição Gerais</h3>
            <p class="text-sm text-slate-500">Defina períodos com valores diferentes (ex: 1º lote, lote promocional). A taxa do evento será aplicada sobre cada valor.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Lista de lotes cadastrados --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide">
                    <i class="fa-solid fa-list mr-2 text-indigo-500"></i> Lotes cadastrados
                    <span class="text-slate-400 font-normal normal-case ml-1">({{ $evento->lotesInscricaoGeral->count() }})</span>
                </h4>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($evento->lotesInscricaoGeral->sortBy('data_inicio') as $lote)
                    <div class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-800">
                                {{ $lote->nome }}
                            </p>
                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-sm font-bold bg-green-100 text-green-800 border border-green-200">
                                    R$ {{ number_format($lote->valor, 2, ',', '.') }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    + {{ number_format($evento->taxa_aplicada, 1, ',', '.') }}% taxa
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-2 flex items-center gap-1">
                                <i class="fa-regular fa-calendar"></i>
                                {{ $lote->data_inicio->format('d/m/Y H:i') }} → {{ $lote->data_fim->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="openEditModal({{ Js::from([
                                'id' => $lote->id,
                                'nome' => $lote->nome,
                                'valor' => (float) $lote->valor,
                                'data_inicio' => $lote->data_inicio->format('Y-m-d\TH:i'),
                                'data_fim' => $lote->data_fim->format('Y-m-d\TH:i'),
                            ]) }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 text-sm font-semibold transition">
                                <i class="fa-solid fa-pen-to-square text-xs"></i> Editar
                            </button>
                            <form action="{{ route('organizador.lotes-gerais.destroy', [$evento, $lote]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este lote?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-red-50 hover:border-red-200 hover:text-red-700 text-sm font-semibold transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i> Remover
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4 text-slate-300">
                            <i class="fa-solid fa-layer-group text-3xl"></i>
                        </div>
                        <p class="text-slate-600 font-medium">Nenhum lote geral cadastrado</p>
                        <p class="text-sm text-slate-500 mt-1">Adicione o primeiro lote no formulário ao lado.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Formulário: Adicionar novo lote --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-indigo-50/50">
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide">
                    <i class="fa-solid fa-plus mr-2 text-indigo-600"></i> Novo lote geral
                </h4>
            </div>
            <form action="{{ route('organizador.lotes-gerais.store', $evento) }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <label for="nome_lote_geral" class="block text-sm font-bold text-slate-700 mb-1.5">Nome do lote</label>
                    <input id="nome_lote_geral" name="nome" type="text" value="{{ old('nome') }}" required placeholder="Ex: 1º Lote, Lote Promocional"
                        class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                    @error('nome')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="valor_lote_geral" class="block text-sm font-bold text-slate-700 mb-1.5">Valor (R$)</label>
                    <input id="valor_lote_geral" name="valor" type="number" step="0.01" min="0" value="{{ old('valor') }}" required placeholder="0,00"
                        class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                    @error('valor')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="data_inicio_lote_geral" class="block text-sm font-bold text-slate-700 mb-1.5">Data e hora de início</label>
                    <input id="data_inicio_lote_geral" name="data_inicio" type="datetime-local" value="{{ old('data_inicio') }}" required
                        class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                    @error('data_inicio')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="data_fim_lote_geral" class="block text-sm font-bold text-slate-700 mb-1.5">Data e hora de fim</label>
                    <input id="data_fim_lote_geral" name="data_fim" type="datetime-local" value="{{ old('data_fim') }}" required
                        class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                    @error('data_fim')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all">
                    <i class="fa-solid fa-plus"></i> Adicionar lote geral
                </button>
            </form>
        </div>
    </div>

    {{-- Modal: Editar lote --}}
    <div x-show="showEditModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showEditModal = false">
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-indigo-600"></i> Editar lote geral
                </h3>
            </div>
            <form x-show="editingLote.id"
                  :action="'{{ route('organizador.lotes-gerais.update', [$evento, 'LOTE_ID_PLACEHOLDER']) }}'.replace('LOTE_ID_PLACEHOLDER', editingLote.id)"
                  method="POST" class="p-6 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="edit_nome_lote" class="block text-sm font-bold text-slate-700 mb-1.5">Nome do lote</label>
                    <input id="edit_nome_lote" name="nome" type="text" required x-model="editingLote.nome"
                        class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                </div>
                <div>
                    <label for="edit_valor_lote" class="block text-sm font-bold text-slate-700 mb-1.5">Valor (R$)</label>
                    <input id="edit_valor_lote" name="valor" type="number" step="0.01" min="0" required x-model="editingLote.valor"
                        class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_data_inicio_lote" class="block text-sm font-bold text-slate-700 mb-1.5">Início</label>
                        <input id="edit_data_inicio_lote" name="data_inicio" type="datetime-local" required x-model="editingLote.data_inicio"
                            class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                    </div>
                    <div>
                        <label for="edit_data_fim_lote" class="block text-sm font-bold text-slate-700 mb-1.5">Fim</label>
                        <input id="edit_data_fim_lote" name="data_fim" type="datetime-local" required x-model="editingLote.data_fim"
                            class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition">
                        <i class="fa-solid fa-check"></i> Salvar alterações
                    </button>
                    <button type="button" @click="showEditModal = false" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
