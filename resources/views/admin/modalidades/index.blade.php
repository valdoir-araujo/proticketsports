<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gerenciar Modalidades
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensagens de feedback --}}
            @if(session('sucesso'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('sucesso') }}</p>
                </div>
            @endif
            @if(session('erro'))
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('erro') }}</p>
                </div>
            @endif
            
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {{-- Coluna da Esquerda: Lista de Modalidades --}}
                <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Modalidades Cadastradas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="relative px-4 py-2"><span class="sr-only">Ações</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($modalidades as $modalidade)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $modalidade->nome }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                            <a href="{{ route('admin.modalidades.edit', $modalidade) }}" class="text-blue-600 hover:text-blue-800">Editar</a>
                                            <form action="{{ route('admin.modalidades.destroy', $modalidade) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-3 text-center text-sm text-gray-500">Nenhuma modalidade cadastrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Coluna da Direita: Formulário para Adicionar --}}
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Nova Modalidade</h3>
                    <form action="{{ route('admin.modalidades.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="nome" value="Nome da Modalidade" />
                            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')" required />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>
                        <div>
                            <x-primary-button>Adicionar Modalidade</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>