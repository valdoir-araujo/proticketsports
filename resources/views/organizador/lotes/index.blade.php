<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gerir Lotes de Preço: <span class="font-normal">{{ $categoria->nome }}</span>
        </h2>
        <p class="text-sm text-gray-600 mt-1">
            Percurso: {{ $categoria->percurso->descricao }} | Evento: {{ $categoria->percurso->evento->nome }}
        </p>
    </x-slot>

    {{-- Conteúdo Principal --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Botão para Voltar --}}
            <div class="flex justify-start">
                <a href="{{ route('organizador.categorias.index', $categoria->percurso) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    &larr; Voltar para Gestão de Categorias
                </a>
            </div>
            {{-- Bloco para exibir a mensagem de sucesso --}}
            @if (session('sucesso'))
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50">
                    {{ session('sucesso') }}
                </div>
            @endif
            {{-- Card para Listar Lotes --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            Lotes de Preço Existentes
                        </h2>
                    </header>
                    <div class="mt-6 space-y-4">
                        @forelse($categoria->lotesInscricao as $lote)
                            <div class="p-4 border rounded-md">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $lote->descricao }}</p>
                                        <p class="text-sm text-gray-600">
                                            Valor: <span class="font-semibold">R$ {{ number_format($lote->valor, 2, ',', '.') }}</span> |
                                            Vigência: {{ \Carbon\Carbon::parse($lote->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($lote->data_fim)->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    {{-- Futuramente, aqui teremos botões de Editar/Excluir --}}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Nenhum lote de preço cadastrado para esta categoria ainda.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Card para Adicionar Novo Lote --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                Adicionar Novo Lote de Preço
                            </h2>
                        </header>

                        <form method="POST" action="{{ route('organizador.lotes.store', $categoria) }}" class="mt-6 space-y-6">
                            @csrf
                            <div>
                                <x-input-label for="descricao" value="Descrição do Lote (Ex: 1º Lote, Lote Promocional)" />
                                <x-text-input id="descricao" name="descricao" type="text" class="mt-1 block w-full" required />
                            </div>

                            <div>
                                <x-input-label for="valor" value="Valor (R$)" />
                                <x-text-input id="valor" name="valor" type="number" step="0.01" class="mt-1 block w-full" required />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="data_inicio" value="Data de Início" />
                                    <x-text-input id="data_inicio" name="data_inicio" type="date" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="data_fim" value="Data de Fim" />
                                    <x-text-input id="data_fim" name="data_fim" type="date" class="mt-1 block w-full" required />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>Salvar Lote</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
