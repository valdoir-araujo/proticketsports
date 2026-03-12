<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestão de Repasses
            </h2>
            <div class="flex items-center space-x-2">
                 <a href="{{ route('admin.repasses.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs font-semibold uppercase tracking-widest transition">
                    <i class="fa-solid fa-plus mr-2"></i>Novo Lote de Repasse
                </a>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    &larr; Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- Inclui os cartões de resumo a partir de um ficheiro parcial para manter o código limpo --}}
            @include('admin.relatorios.financeiros.partials.cards-resumo')

            {{-- Resumo por organizador --}}
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Resumo por organizador</h3>
                <p class="text-sm text-gray-500 mb-4">Total de inscrições confirmadas por evento, valor da taxa plataforma, valor já repassado e valor a pagar.</p>

                {{-- Mobile: cards --}}
                <div class="md:hidden space-y-4">
                    @forelse ($resumoPorOrganizadorEvento as $resumo)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="font-semibold text-gray-900 mb-1">{{ $resumo->organizacao->nome_fantasia }}</div>
                            <div class="text-sm text-indigo-600 font-medium mb-3">{{ $resumo->evento->nome }}</div>
                            <dl class="grid grid-cols-1 gap-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Total inscrições</dt>
                                    <dd class="font-medium text-gray-900">{{ $resumo->total_inscricoes }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Total bruto (inscrições)</dt>
                                    <dd class="font-semibold text-gray-900">R$ {{ number_format($resumo->valor_total, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Valor taxa (plataforma)</dt>
                                    <dd class="font-semibold text-gray-900">R$ {{ number_format($resumo->valor_taxa, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Valor já pago (repassado)</dt>
                                    <dd class="font-semibold text-green-700">R$ {{ number_format($resumo->valor_pago, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Valor a pagar</dt>
                                    <dd class="font-bold text-blue-700">R$ {{ number_format($resumo->valor_a_pagar, 2, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-4">Nenhum evento com inscrições confirmadas.</p>
                    @endforelse
                </div>

                {{-- Desktop: tabela --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organizador</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Inscrições</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total bruto</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor taxa</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor pago</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor a pagar</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($resumoPorOrganizadorEvento as $resumo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $resumo->organizacao->nome_fantasia }}</td>
                                    <td class="px-6 py-4 text-sm text-indigo-600 font-medium">{{ $resumo->evento->nome }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 text-right">{{ $resumo->total_inscricoes }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">R$ {{ number_format($resumo->valor_total, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">R$ {{ number_format($resumo->valor_taxa, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-green-700 text-right">R$ {{ number_format($resumo->valor_pago, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-blue-700 text-right">R$ {{ number_format($resumo->valor_a_pagar, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum evento com inscrições confirmadas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SEÇÃO DE AÇÃO: Lotes de Repasse Pendentes --}}
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Lotes de Repasse Pendentes</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organizador</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor a Repassar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data de Criação</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($repassesPendentes as $repasse)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $repasse->id }}</td>
                                    {{-- ========================================================== --}}
                                    {{-- ⬇️ CORREÇÃO APLICADA AQUI ⬇️ --}}
                                    {{-- ========================================================== --}}
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $repasse->organizacao->nome_fantasia }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-blue-700">R$ {{ number_format($repasse->valor_total_repassado, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $repasse->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('admin.repasses.show', $repasse) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">Confirmar Pagamento</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum lote de repasse pendente.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- SEÇÃO DE HISTÓRICO: Lotes de Repasses Realizados --}}
            <div class="bg-white p-6 rounded-lg shadow-sm">
                 <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Histórico de Repasses Realizados</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                         <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organizador</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Repassado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data do Pagamento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprovativo</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             @forelse ($repassesRealizados as $repasse)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $repasse->id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $repasse->organizacao->nome_fantasia }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">R$ {{ number_format($repasse->valor_total_repassado, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $repasse->data_repassado->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4">
                                        @if($repasse->status === 'Realizado')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Realizado</span>
                                        @elseif($repasse->status === 'Estornado')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">Estornado</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $repasse->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        @if($repasse->comprovante_url)
                                            <button type="button" data-comprovante-url="{{ asset('storage/' . $repasse->comprovante_url) }}" class="modal-comprovante-trigger text-indigo-600 hover:underline cursor-pointer bg-transparent border-0 p-0 font-inherit">
                                                Ver Comprovativo
                                            </button>
                                        @else
                                            <span>N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @if($repasse->status === 'Realizado')
                                            <form action="{{ route('admin.repasses.estornar', $repasse) }}" method="POST" class="inline" onsubmit="return confirm('Estornar este repasse? As inscrições voltarão a ficar pendentes para um novo lote.');">
                                                @csrf
                                                <button type="submit" class="text-amber-600 hover:text-amber-800 font-semibold">Estornar</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhum repasse realizado ainda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="mt-4">
                    {{ $repassesRealizados->links() }}
                </div>
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
