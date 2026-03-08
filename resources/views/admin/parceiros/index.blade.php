<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Parceiros
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    &larr; Voltar ao Painel
                </a>
                <a href="{{ route('admin.parceiros.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-semibold transition">
                    <i class="fa-solid fa-plus mr-2"></i> Novo Parceiro
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('sucesso'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('sucesso') }}</span>
                        </div>
                    @endif
                    @if(session('erro'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('erro') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-3 px-4 text-left">Logo</th>
                                    <th class="py-3 px-4 text-left">Nome</th>
                                    <th class="py-3 px-4 text-left">Tipo</th>
                                    <th class="py-3 px-4 text-left">Ordem</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                    <th class="py-3 px-4 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parceiros as $parceiro)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">
                                            @if($parceiro->logo_url)
                                                <img src="{{ asset('storage/' . $parceiro->logo_url) }}" alt="{{ $parceiro->nome }}" class="h-12 w-20 object-contain rounded">
                                            @else
                                                <span class="text-gray-400 text-sm">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-medium">{{ $parceiro->nome }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600">{{ $parceiro->tipo_label }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $parceiro->ordem }}</td>
                                        <td class="py-3 px-4">
                                            @if($parceiro->ativo)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-2 whitespace-nowrap">
                                                <a href="{{ route('admin.parceiros.edit', $parceiro) }}" class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-md hover:bg-blue-600">Editar</a>
                                                <form action="{{ route('admin.parceiros.destroy', $parceiro) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este parceiro?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-md hover:bg-red-600">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-500">
                                            Nenhum parceiro cadastrado. <a href="{{ route('admin.parceiros.create') }}" class="text-blue-600 hover:underline">Cadastrar primeiro parceiro</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
