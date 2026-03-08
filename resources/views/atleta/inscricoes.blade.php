<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Minhas Inscrições
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900">
                    <p class="mb-6 text-sm sm:text-base">Aqui você pode ver o histórico de todas as suas inscrições e gerenciar pagamentos pendentes.</p>

                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 min-w-0 border border-gray-200 rounded-lg" style="-webkit-overflow-scrolling: touch;">
                        <table class="min-w-full divide-y divide-gray-200 min-w-[600px]">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percurso</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($inscricoes as $inscricao)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $inscricao->evento->nome }}</div>
                                            <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($inscricao->evento->data_evento)->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $inscricao->categoria->percurso->descricao ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $inscricao->categoria->nome }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($inscricao->status == 'aguardando_pagamento')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Aguardando Pag.</span>
                                            @elseif($inscricao->status == 'confirmada')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmada</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ ucfirst(str_replace('_', ' ', $inscricao->status)) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            <div class="flex items-center justify-center gap-x-2">
                                                @if($inscricao->status == 'aguardando_pagamento')
                                                    <a href="{{ route('inscricao.edit', $inscricao) }}" class="inline-flex items-center justify-center min-h-[44px] bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded-lg text-xs sm:text-sm transition" title="Editar Inscrição">
                                                        <i class="fa-solid fa-pencil mr-1"></i>
                                                        <span>Editar</span>
                                                    </a>
                                                    <a href="{{ route('pagamento.show', $inscricao) }}" class="inline-flex items-center justify-center min-h-[44px] bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg text-xs sm:text-sm transition">
                                                        Pagar
                                                    </a>
                                                @else
                                                    <a href="{{ route('inscricao.show', $inscricao) }}" class="inline-flex items-center justify-center min-h-[44px] bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg text-xs sm:text-sm transition">
                                                        Detalhes
                                                    </a>
                                                    <a href="{{ route('inscricao.edit', $inscricao) }}" class="inline-flex items-center justify-center min-h-[44px] bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded-lg text-xs sm:text-sm transition" title="Editar Inscrição">
                                                        <i class="fa-solid fa-pencil mr-1"></i>
                                                        <span>Editar</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Você ainda não realizou nenhuma inscrição.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6">
                        @if ($inscricoes->hasPages())
                            {{ $inscricoes->links() }}
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

