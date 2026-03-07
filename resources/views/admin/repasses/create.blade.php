<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Criar Novo Lote de Repasse
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        inscricoes: {{ Js::from($inscricoes) }},
        selectedInscricoes: [],
        
        get totalAPagar() {
            return this.inscricoes
                .filter(inscricao => this.selectedInscricoes.includes(inscricao.id))
                .reduce((total, inscricao) => total + (inscricao.valor_pago - inscricao.taxa_plataforma), 0);
        },
        
        toggleAll(event) {
            if (event.target.checked) {
                this.selectedInscricoes = this.inscricoes.map(i => i.id);
            } else {
                this.selectedInscricoes = [];
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.repasses.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm sm:rounded-lg">
                    {{-- Cabeçalho com Ações e Totais --}}
                    <div class="p-6 border-b flex flex-col md:flex-row justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Inscrições Pendentes de Repasse</h3>
                            <p class="text-sm text-gray-600">Selecione as inscrições que deseja incluir neste lote de pagamento.</p>
                        </div>
                        <div class="mt-4 md:mt-0 text-right">
                            <p class="text-sm text-gray-500">Valor a Repassar</p>
                            <p class="text-3xl font-bold text-blue-700" x-text="`R$ ${totalAPagar.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`"></p>
                            <button type="submit" :disabled="selectedInscricoes.length === 0" class="mt-2 w-full md:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-check mr-2"></i>
                                Criar Lote de Repasse (<span x-text="selectedInscricoes.length"></span>)
                            </button>
                        </div>
                    </div>

                    {{-- Tabela de Inscrições --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-4 text-center"><input type="checkbox" @click="toggleAll" class="rounded"></th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Evento</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Organizador</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Atleta</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor Bruto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Taxa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor a Repassar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($inscricoes as $inscricao)
                                    <tr>
                                        <td class="p-4 text-center"><input type="checkbox" name="inscricao_ids[]" value="{{ $inscricao->id }}" x-model="selectedInscricoes" class="rounded"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inscricao->evento->nome }}</td>
                                        {{-- ========================================================== --}}
                                        {{-- ⬇️ CORREÇÃO APLICADA AQUI ⬇️ --}}
                                        {{-- ========================================================== --}}
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $inscricao->evento->organizacao->nome_fantasia }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $inscricao->atleta->user->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600">- R$ {{ number_format($inscricao->taxa_plataforma, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-800">R$ {{ number_format($inscricao->valor_pago - $inscricao->taxa_plataforma, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Nenhuma inscrição pendente de repasse.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
