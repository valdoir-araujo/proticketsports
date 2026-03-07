<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestão de Campeonatos
            </h2>        
            <div class="flex items-center space-x-2">
                <a href="{{ route('organizador.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    &larr; Voltar
                </a>
                <a href="{{ route('organizador.campeonatos.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm font-semibold transition">
                    <i class="fa-solid fa-plus mr-2"></i>Novo Campeonato
                </a>
            </div>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('sucesso'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p class="font-bold">Sucesso!</p>
                            <p>{{ session('sucesso') }}</p>
                        </div>
                    @endif
                    
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p class="font-bold">Ocorreu um erro</p>
                            <ul class="mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-3 px-4 text-left">Nome do Campeonato</th>
                                    <th class="py-3 px-4 text-left">Ano</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                    <th class="py-3 px-4 text-left">Nº de Etapas</th>
                                    <th class="py-3 px-4 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($campeonatos as $campeonato)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-4 font-medium">{{ $campeonato->nome }}</td>
                                        <td class="py-3 px-4">{{ $campeonato->ano }}</td>
                                        <td class="py-3 px-4">
                                            @if($campeonato->status == 'ativo')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                            @elseif($campeonato->status == 'cancelado')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelado</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($campeonato->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">{{ $campeonato->eventos_count }}</td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-2 whitespace-nowrap">
                                                <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="inline-flex items-center px-3 py-1 bg-gray-600 text-white text-xs font-semibold rounded-md hover:bg-gray-700 transition-colors" title="Gerenciar Etapas e Detalhes">
                                                    Gerenciar
                                                </a>
                                                
                                                <a href="{{ route('organizador.campeonatos.regras.index', $campeonato) }}" class="inline-flex items-center px-3 py-1 bg-teal-500 text-white text-xs font-semibold rounded-md hover:bg-teal-600 transition-colors" title="Definir Regras de Pontuação">
                                                    <i class="fa-solid fa-list-ol mr-1"></i> Pontuação
                                                </a>

                                                <a href="{{ route('organizador.campeonatos.edit', $campeonato) }}" class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-md hover:bg-blue-600 transition-colors" title="Editar">
                                                    Editar
                                                </a>
                                                
                                                <form action="{{ route('organizador.campeonatos.destroy', $campeonato) }}" method="POST" onsubmit="return confirm('Atenção: Excluir um campeonato é uma ação permanente e não pode ser desfeita. Confirma?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-md hover:bg-red-600 transition-colors" title="Excluir">
                                                        Excluir
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">Nenhum campeonato cadastrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $campeonatos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>