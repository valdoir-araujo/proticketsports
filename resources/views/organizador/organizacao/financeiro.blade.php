<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dados para Repasse
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">

                    <header class="border-b pb-4 mb-6">
                        <h3 class="text-lg font-bold text-slate-800">
                            Informações Financeiras da Organização
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Preencha os dados abaixo para receber os repasses dos valores arrecadados nos seus eventos.
                        </p>
                    </header>
                    
                    @if(session('sucesso'))
                        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                            <span class="font-medium">Sucesso!</span> {{ session('sucesso') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('organizador.organizacao.financeiro.update') }}" class="space-y-8">
                        @csrf
                        @method('PATCH')

                        {{-- Seção PIX --}}
                        <div>
                            <h4 class="text-md font-semibold text-gray-800 mb-4 border-b pb-2">Dados PIX (Recomendado)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="pix_chave_tipo" value="Tipo de Chave PIX" />
                                    <select id="pix_chave_tipo" name="pix_chave_tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        <option value="">Nenhum</option>
                                        <option value="cpf_cnpj" @selected(old('pix_chave_tipo', $organizacao->pix_chave_tipo) == 'cpf_cnpj')>CPF/CNPJ</option>
                                        <option value="email" @selected(old('pix_chave_tipo', $organizacao->pix_chave_tipo) == 'email')>E-mail</option>
                                        <option value="telefone" @selected(old('pix_chave_tipo', $organizacao->pix_chave_tipo) == 'telefone')>Telefone</option>
                                        <option value="aleatoria" @selected(old('pix_chave_tipo', $organizacao->pix_chave_tipo) == 'aleatoria')>Chave Aleatória</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="pix_chave" value="Chave PIX" />
                                    <x-text-input id="pix_chave" name="pix_chave" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('pix_chave', $organizacao->pix_chave)" />
                                </div>
                            </div>
                        </div>
                        
                        {{-- Seção Dados Bancários --}}
                        <div>
                            <h4 class="text-md font-semibold text-gray-800 mb-4 border-b pb-2">Dados Bancários (Alternativo)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="banco_nome" value="Nome do Banco" />
                                    <x-text-input id="banco_nome" name="banco_nome" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('banco_nome', $organizacao->banco_nome)" />
                                </div>
                                <div>
                                    <x-input-label for="banco_agencia" value="Agência" />
                                    <x-text-input id="banco_agencia" name="banco_agencia" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('banco_agencia', $organizacao->banco_agencia)" />
                                </div>
                                <div>
                                    <x-input-label for="banco_conta" value="Conta (com dígito)" />
                                    <x-text-input id="banco_conta" name="banco_conta" type="text" class="mt-1 block w-full focus:border-orange-500 focus:ring-orange-500" :value="old('banco_conta', $organizacao->banco_conta)" />
                                </div>
                            </div>
                            <div class="mt-6">
                                 <x-input-label value="Tipo da Conta" />
                                 <div class="flex items-center space-x-4 mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="banco_tipo_conta" value="corrente" @checked(old('banco_tipo_conta', $organizacao->banco_tipo_conta) == 'corrente') class="form-radio text-orange-600 focus:ring-orange-500">
                                        <span class="ml-2">Corrente</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="banco_tipo_conta" value="poupanca" @checked(old('banco_tipo_conta', $organizacao->banco_tipo_conta) == 'poupanca') class="form-radio text-orange-600 focus:ring-orange-500">
                                        <span class="ml-2">Poupança</span>
                                    </label>
                                 </div>
                            </div>
                        </div>

                        {{-- Botão de Ação --}}
                        <div class="border-t pt-6 flex items-center justify-end">
                            <button type="submit" class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                                Salvar Informações Financeiras
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

