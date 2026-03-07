<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Configurações da Plataforma
            </h2>

            {{-- BOTÃO VOLTAR ADICIONADO AQUI --}}
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar ao Painel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('sucesso'))
                        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50">
                            {{ session('sucesso') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.configuracoes.store') }}">
                        @csrf
                        <h3 class="text-lg font-medium text-gray-900">Financeiro</h3>
                        <p class="text-sm text-gray-600 mb-4">Defina as taxas globais da plataforma.</p>
                        
                        <div>
                            <x-input-label for="taxa_plataforma" value="Taxa da Plataforma (%)" />
                            <x-text-input id="taxa_plataforma" class="block mt-1 w-full md:w-1/3" 
                                          type="number" step="0.01" name="taxa_plataforma" 
                                          :value="old('taxa_plataforma', $taxaPlataforma)" required />
                            <x-input-error :messages="$errors->get('taxa_plataforma')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Insira o valor percentual da taxa. Ex: 10 para 10%, 7.5 para 7.5%.</p>
                        </div>

                        <div class="flex items-center mt-6">
                            <x-primary-button>
                                Salvar Configurações
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>