<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Painel Financeiro e Repasses
            </h2>
            <a href="{{ route('organizador.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar ao Painel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('sucesso'))
                <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">Sucesso!</span> {{ session('sucesso') }}
                </div>
            @endif

            {{-- 1. Dashboard de Valores --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Total Arrecadado</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">R$ {{ number_format($totalArrecadado, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Taxa da Plataforma</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">R$ {{ number_format($taxaPlataforma, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Total Repassado</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">R$ {{ number_format($totalRepassado, 2, ',', '.') }}</p>
                </div>
                <div class="bg-green-100 p-6 rounded-lg shadow-sm border border-green-200">
                    <p class="text-sm text-green-800 font-semibold">Valor a Receber</p>
                    <p class="text-3xl font-bold text-green-900 mt-1">R$ {{ number_format($valorAReceber, 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- 2. Histórico de Repasses e Formulário de Dados --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                {{-- Coluna do Histórico --}}
                <div class="lg:col-span-3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Histórico de Repasses</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Comprovativo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($repasses as $repasse)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($repasse->data_repassado)->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-800">R$ {{ number_format($repasse->valor_total_repassado, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($repasse->status === 'efetuado') bg-green-100 text-green-800 @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($repasse->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($repasse->comprovante_url)
                                                    <a href="{{ asset('storage/' . $repasse->comprovante_url) }}" target="_blank" class="text-indigo-600 hover:underline">Ver</a>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">Nenhum repasse efetuado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                         <div class="mt-4">
                            {{ $repasses->links() }}
                        </div>
                    </div>
                </div>

                {{-- Coluna do Formulário --}}
                <div class="lg:col-span-2">
                    <form method="POST" action="{{ route('organizador.financeiro.update') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                        @csrf
                        @method('PATCH')

                        <header>
                            <h3 class="text-lg font-bold text-slate-800">Seus Dados para Repasse</h3>
                            <p class="text-sm text-gray-600 mt-1">Mantenha seus dados atualizados para garantir o recebimento.</p>
                        </header>

                        {{-- Nome do Beneficiário --}}
                        <div class="border-t pt-6">
                            <x-input-label for="nome_beneficiario" value="Nome Completo do Beneficiário" />
                            <x-text-input id="nome_beneficiario" name="nome_beneficiario" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" 
                                          :value="old('nome_beneficiario', $organizacao->dadosBancarios?->nome_beneficiario)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('nome_beneficiario')" />
                        </div>

                        {{-- Seção PIX --}}
                        <div>
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Dados PIX (Recomendado)</h4>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="pix_chave_tipo" value="Tipo de Chave PIX" />
                                    <select id="pix_chave_tipo" name="pix_chave_tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        <option value="">Nenhum</option>
                                        <option value="cpf_cnpj" @selected(old('pix_chave_tipo', $organizacao->dadosBancarios?->pix_chave_tipo) == 'cpf_cnpj')>CPF/CNPJ</option>
                                        <option value="email" @selected(old('pix_chave_tipo', $organizacao->dadosBancarios?->pix_chave_tipo) == 'email')>E-mail</option>
                                        <option value="telefone" @selected(old('pix_chave_tipo', $organizacao->dadosBancarios?->pix_chave_tipo) == 'telefone')>Telefone</option>
                                        <option value="aleatoria" @selected(old('pix_chave_tipo', $organizacao->dadosBancarios?->pix_chave_tipo) == 'aleatoria')>Chave Aleatória</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="pix_chave" value="Chave PIX" />
                                    <x-text-input id="pix_chave" name="pix_chave" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('pix_chave', $organizacao->dadosBancarios?->pix_chave)" />
                                </div>
                            </div>
                        </div>

                        {{-- Seção Dados Bancários --}}
                        <div class="border-t pt-6">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Dados Bancários (Alternativo)</h4>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="banco_nome" value="Nome do Banco" />
                                        <x-text-input id="banco_nome" name="banco_nome" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('banco_nome', $organizacao->dadosBancarios?->banco_nome)" />
                                    </div>
                                    <div>
                                        <x-input-label for="banco_agencia" value="Agência" />
                                        <x-text-input id="banco_agencia" name="banco_agencia" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('banco_agencia', $organizacao->dadosBancarios?->banco_agencia)" />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="banco_conta" value="Conta (com dígito)" />
                                    <x-text-input id="banco_conta" name="banco_conta" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('banco_conta', $organizacao->dadosBancarios?->banco_conta)" />
                                </div>
                                <div>
                                   <x-input-label value="Tipo da Conta" />
                                   <div class="flex items-center space-x-4 mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="banco_tipo_conta" value="corrente" @checked(old('banco_tipo_conta', $organizacao->dadosBancarios?->banco_tipo_conta) == 'corrente') class="form-radio text-orange-600 focus:ring-orange-500">
                                            <span class="ml-2">Corrente</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="banco_tipo_conta" value="poupanca" @checked(old('banco_tipo_conta', $organizacao->dadosBancarios?->banco_tipo_conta) == 'poupanca') class="form-radio text-orange-600 focus:ring-orange-500">
                                            <span class="ml-2">Poupança</span>
                                        </label>
                                   </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botão de Ação --}}
                        <div class="border-t pt-6 flex items-center justify-end">
                            <button type="submit" class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                                Salvar Dados Financeiros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

