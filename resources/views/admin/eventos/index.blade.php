<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gerenciamento de Eventos
            </h2>
            
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
                        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                            {{ session('sucesso') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Todos os Eventos da Plataforma</h3>

                    {{-- Tabela de Eventos --}}
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organização</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    {{-- NOVA COLUNA: Taxa --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa Adm</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($eventos as $evento)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $evento->nome }}</div>
                                            <div class="text-sm text-gray-500">{{ $evento->cidade->nome ?? 'N/A' }} - {{ $evento->cidade->estado->uf ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $evento->organizacao->nome_fantasia ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $evento->data_evento->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($evento->status)
                                                @case('publicado')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Publicado</span>
                                                    @break
                                                @case('rascunho')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Rascunho</span>
                                                    @break
                                                @case('cancelado')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelado</span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($evento->status) }}</span>
                                            @endswitch
                                        </td>
                                        
                                        {{-- COLUNA DA TAXA (Mostra se é padrão ou personalizada) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                            @if($evento->taxaservico !== null)
                                                <span class="text-blue-600">{{ $evento->taxaservico }}%</span>
                                            @else
                                                <span class="text-gray-400">Padrão (10%)</span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- BOTÃO EDITAR (Leva para a nova tela) --}}
                                            <a href="{{ route('admin.eventos.edit', $evento) }}" class="inline-flex items-center px-3 py-1 bg-indigo-50 border border-indigo-200 rounded text-indigo-700 hover:bg-indigo-100 transition-colors font-bold">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Nenhum evento encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Links de Paginação --}}
                    <div class="mt-4">
                        {{ $eventos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>