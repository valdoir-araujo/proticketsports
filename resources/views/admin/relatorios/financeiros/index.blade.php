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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprovativo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             @forelse ($repassesRealizados as $repasse)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $repasse->id }}</td>
                                    {{-- ========================================================== --}}
                                    {{-- ⬇️ CORREÇÃO APLICADA AQUI ⬇️ --}}
                                    {{-- ========================================================== --}}
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $repasse->organizacao->nome_fantasia }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">R$ {{ number_format($repasse->valor_total_repassado, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $repasse->data_repassado->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        @if($repasse->comprovante_url)
                                            <a href="{{ asset('storage/' . $repasse->comprovante_url) }}" target="_blank" class="text-indigo-600 hover:underline">Ver Comprovativo</a>
                                        @else
                                            <span>N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum repasse realizado ainda.</td></tr>
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
</x-app-layout>
