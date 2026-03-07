<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Relatórios Financeiros
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar ao Painel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Seção de Visão Geral (Totais da Plataforma) --}}
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Visão Geral da Plataforma</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                                <i class="fa-solid fa-dollar-sign fa-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Faturamento Bruto Total</p>
                                <p class="text-2xl font-bold text-gray-800">R$ {{ number_format($faturamentoBrutoTotal, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <div class="bg-green-100 text-green-600 p-3 rounded-full mr-4">
                                <i class="fa-solid fa-hand-holding-dollar fa-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Receita da Plataforma (Taxas)</p>
                                <p class="text-2xl font-bold text-green-700">R$ {{ number_format($receitaPlataformaTotal, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <div class="bg-orange-100 text-orange-600 p-3 rounded-full mr-4">
                                <i class="fa-solid fa-right-left fa-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total a Repassar</p>
                                <p class="text-2xl font-bold text-gray-800">R$ {{ number_format($totalRepassado, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seção de Desempenho Recente --}}
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Desempenho Recente (Últimos 30 dias)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <p class="text-sm text-gray-500">Faturamento (30d)</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">R$ {{ number_format($faturamentoUltimos30Dias, 2, ',', '.') }}</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <p class="text-sm text-gray-500">Receita (30d)</p>
                        <p class="text-2xl font-bold text-green-700 mt-1">R$ {{ number_format($receitaUltimos30Dias, 2, ',', '.') }}</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <p class="text-sm text-gray-500">Total de Transações</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalTransacoes }}</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <p class="text-sm text-gray-500">Ticket Médio</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>