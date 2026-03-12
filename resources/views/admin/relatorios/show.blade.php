<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Repasse para: {{ $evento->nome }}
            </h2>
            <a href="{{ route('admin.relatorios.financeiros.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-8">

            {{-- Coluna da Esquerda: Informações e Histórico --}}
            <div class="lg:col-span-3 space-y-8">
                {{-- Resumo Financeiro --}}
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3">Resumo Financeiro do Evento</h3>
                    <dl class="space-y-4">
                        <div class="flex justify-between"><dt class="text-gray-500">Faturamento Bruto</dt><dd class="font-semibold">R$ {{ number_format($faturamentoBruto, 2, ',', '.') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Receita da Plataforma</dt><dd class="font-semibold text-green-600">R$ {{ number_format($receitaPlataforma, 2, ',', '.') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Total Já Repassado</dt><dd class="font-semibold text-orange-600">R$ {{ number_format($totalRepassado, 2, ',', '.') }}</dd></div>
                        <div class="flex justify-between text-xl font-bold border-t pt-4"><dt class="text-blue-700">VALOR A REPASSAR</dt><dd class="text-blue-700">R$ {{ number_format($valorARepassar, 2, ',', '.') }}</dd></div>
                    </dl>
                </div>

                {{-- Histórico de Repasses --}}
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Histórico de Repasses deste Evento</h3>
                    <ul class="divide-y">
                        @forelse($evento->repasses as $repasse)
                            <li class="py-3">
                                <p class="font-semibold">R$ {{ number_format($repasse->valor_total_repassado, 2, ',', '.') }} <span class="text-gray-500 font-normal">em {{ $repasse->data_repassado->format('d/m/Y') }}</span></p>
                                @if($repasse->comprovante_url)
                                    <button type="button" data-comprovante-url="{{ asset('storage/' . $repasse->comprovante_url) }}" class="modal-comprovante-trigger text-sm text-indigo-600 hover:underline cursor-pointer bg-transparent border-0 p-0 font-inherit">Ver Comprovativo</button>
                                @endif
                            </li>
                        @empty
                            <li class="py-3 text-center text-gray-500">Nenhum repasse efetuado para este evento.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Coluna da Direita: Dados e Formulário de Ação --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Dados Bancários do Organizador --}}
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Dados para Repasse</h3>
                    @if($dadosBancarios)
                        <dl class="space-y-2 text-sm">
                            <div><dt class="font-semibold">Beneficiário:</dt><dd class="text-gray-700">{{ $dadosBancarios->nome_beneficiario }}</dd></div>
                            @if($dadosBancarios->pix_chave)
                                <div><dt class="font-semibold">PIX ({{ ucfirst($dadosBancarios->pix_chave_tipo) }}):</dt><dd class="text-gray-700">{{ $dadosBancarios->pix_chave }}</dd></div>
                            @endif
                            @if($dadosBancarios->banco_nome)
                                <div><dt class="font-semibold">Banco:</dt><dd class="text-gray-700">{{ $dadosBancarios->banco_nome }} | Ag: {{ $dadosBancarios->banco_agencia }} | CC: {{ $dadosBancarios->banco_conta }} ({{ ucfirst($dadosBancarios->banco_tipo_conta) }})</dd></div>
                            @endif
                        </dl>
                    @else
                        <p class="text-center text-red-600 font-semibold">O organizador ainda não cadastrou os dados para repasse.</p>
                    @endif
                </div>

                {{-- Formulário de Confirmação de Repasse --}}
                <form action="{{ route('admin.relatorios.financeiros.store', $evento) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-sm space-y-4">
                    @csrf
                    <h3 class="text-lg font-bold text-gray-800">Confirmar Novo Repasse</h3>
                    <div>
                        <x-input-label for="valor_repassado" value="Valor a Repassar (R$)" />
                        <x-text-input id="valor_repassado" name="valor_repassado" type="number" step="0.01" class="mt-1 block w-full" :value="number_format($valorARepassar, 2, '.', '')" required />
                    </div>
                    <div>
                        <x-input-label for="data_repassado" value="Data do Repasse" />
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
                        <x-primary-button class="w-full justify-center">Confirmar e Salvar Repasse</x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- Modal do comprovante --}}
    <div id="modal-comprovante" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog" aria-label="Comprovante de repasse">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" id="modal-comprovante-backdrop"></div>
        <div class="fixed inset-4 md:inset-8 lg:inset-12 flex flex-col items-center justify-center">
            <div class="relative w-full h-full max-w-4xl max-h-[90vh] bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50 shrink-0">
                    <h3 class="text-lg font-semibold text-gray-900">Comprovante</h3>
                    <button type="button" id="modal-comprovante-close" class="p-2 rounded-lg text-gray-500 hover:bg-gray-200 hover:text-gray-700 transition-colors" aria-label="Fechar">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <div class="flex-1 min-h-0 p-4 overflow-auto">
                    <iframe id="modal-comprovante-iframe" src="" class="w-full h-full min-h-[70vh] rounded-lg border border-gray-200" title="Comprovante"></iframe>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var modal = document.getElementById('modal-comprovante');
        var iframe = document.getElementById('modal-comprovante-iframe');
        var backdrop = document.getElementById('modal-comprovante-backdrop');
        var closeBtn = document.getElementById('modal-comprovante-close');
        function open(url) {
            iframe.src = url;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function close() {
            modal.classList.add('hidden');
            iframe.src = '';
            document.body.style.overflow = '';
        }
        document.querySelectorAll('.modal-comprovante-trigger').forEach(function(btn) {
            btn.addEventListener('click', function() { open(this.getAttribute('data-comprovante-url')); });
        });
        closeBtn.addEventListener('click', close);
        backdrop.addEventListener('click', close);
        modal.addEventListener('keydown', function(e) { if (e.key === 'Escape') close(); });
    })();
    </script>
</x-app-layout>