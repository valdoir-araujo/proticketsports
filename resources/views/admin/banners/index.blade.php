<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestão de Banners do Carrossel
            </h2>

            <div class="flex items-center space-x-2">
                 <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    &larr; Voltar ao Painel
                </a>
                <a href="{{ route('admin.banners.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-semibold transition">
                    <i class="fa-solid fa-plus mr-2"></i> Adicionar Novo Banner
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
                    @if(session('erro')) {{-- Adicionado para feedback de erro --}}
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('erro') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-3 px-4 text-left">Imagem</th>
                                    <th class="py-3 px-4 text-left">Título</th>
                                    <th class="py-3 px-4 text-left">Link</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                    <th class="py-3 px-4 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($banners as $banner)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">
                                            <img src="{{ asset('storage/' . $banner->imagem_url) }}" alt="{{ $banner->titulo }}" class="h-16 w-32 object-cover rounded-md">
                                        </td>
                                        <td class="py-3 px-4 font-medium">{{ $banner->titulo }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600">
                                            <a href="{{ $banner->link_url }}" target="_blank" class="text-blue-600 hover:underline">{{ $banner->link_url }}</a>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($banner->ativo)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-2 whitespace-nowrap">
                                                
                                                {{-- CORREÇÃO 1: Link de Edição --}}
                                                <a href="{{ route('admin.banners.edit', $banner->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-md hover:bg-blue-600 transition-colors">Editar</a>
                                                
                                                {{-- CORREÇÃO 2: Action do Formulário de Exclusão --}}
                                                <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Tem a certeza que deseja excluir este banner?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-md hover:bg-red-600 transition-colors">Excluir</button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">Nenhum banner cadastrado.</td>
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