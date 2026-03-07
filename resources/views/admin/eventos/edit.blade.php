<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Administrar Evento: {{ $evento->nome }}
            </h2>
            <a href="{{ route('admin.eventos.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('admin.eventos.update', $evento) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <h3 class="text-lg font-medium text-gray-900 mb-6 border-b pb-2">Configurações Financeiras e Status</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Coluna 1: Status --}}
                            <div>
                                <x-input-label for="status" value="Status do Evento" />
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="rascunho" {{ $evento->status == 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                                    <option value="publicado" {{ $evento->status == 'publicado' ? 'selected' : '' }}>Publicado</option>
                                    <option value="cancelado" {{ $evento->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Define se o evento aparece no site.</p>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            {{-- Coluna 2: Taxa de Serviço (Arquivo Parcial) --}}
                            @include('admin.eventos.partials.taxa-input')

                        </div>

                        <div class="mt-8 flex justify-end">
                            <x-primary-button class="bg-slate-800 hover:bg-slate-700">
                                Salvar Alterações
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>