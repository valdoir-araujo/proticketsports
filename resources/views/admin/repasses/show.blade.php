<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Confirmar Repasse do Lote #{{ $repasse->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-8">

            {{-- Coluna da Esquerda: Detalhes e Inscrições --}}
            <div class="lg:col-span-3 space-y-8">
                {{-- ... (O seu card de Detalhes do Lote permanece igual) ... --}}
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Inscrições Incluídas no Lote</h3>
                    <ul class="divide-y max-h-96 overflow-y-auto">
                        @foreach($repasse->inscricoes as $inscricao)
                            <li class="py-2 text-sm">
                                <span class="font-semibold">{{ $inscricao->atleta->user->name }}</span> - {{ $inscricao->evento->nome }} (R$ {{ number_format($inscricao->valor_pago - $inscricao->taxa_plataforma, 2, ',', '.') }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Coluna da Direita: Formulários de Ação --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Formulário de Confirmação de Repasse --}}
                <form action="{{ route('admin.repasses.update', $repasse) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-sm space-y-4 sticky top-6">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-lg font-bold text-gray-800">Confirmar Pagamento</h3>
                    <div>
                        <x-input-label for="data_repassado" value="Data do Pagamento" />
                        <x-text-input id="data_repassado" name="data_repassado" type="date" class="mt-1 block w-full" value="{{ now()->format('Y-m-d') }}" required />
                    </div>
                     <div>
                        <x-input-label for="comprovante" value="Anexar Comprovativo" />
                        <input id="comprovante" name="comprovante" type="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100" required/>
                    </div>
                    <div>
                        <x-input-label for="observacoes" value="Observações (Opcional)" />
                        <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div class="border-t pt-4">
                        <x-primary-button class="w-full justify-center text-base">
                            <i class="fa-solid fa-circle-check mr-2"></i>
                            Confirmar Repasse de R$ {{ number_format($repasse->valor_total_repassado, 2, ',', '.') }}
                        </x-primary-button>
                    </div>
                </form>

                {{-- NOVO FORMULÁRIO PARA CANCELAR O LOTE --}}
                <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                    <h3 class="text-lg font-bold text-red-800">Zona de Perigo</h3>
                    <p class="text-sm text-red-700 mt-1">Cancelar este lote irá libertar todas as inscrições associadas para que possam ser incluídas num novo lote. Esta ação não pode ser desfeita.</p>
                    <form action="{{ route('admin.repasses.destroy', $repasse) }}" method="POST" onsubmit="return confirm('Tem a certeza que deseja cancelar este lote de repasse? As inscrições serão libertadas.');" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white font-semibold text-sm rounded-md hover:bg-red-700">
                           <i class="fa-solid fa-trash-alt mr-2"></i> Cancelar Lote #{{$repasse->id}}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

