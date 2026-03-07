<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Campeonato: <span class="font-normal">{{ $campeonato->nome }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <form method="POST" action="{{ route('organizador.campeonatos.update', $campeonato) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Nome e Ano -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="nome" value="Nome do Campeonato" />
                                <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $campeonato->nome)" required autofocus />
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="ano" value="Ano/Temporada" />
                                <x-text-input id="ano" name="ano" type="number" class="mt-1 block w-full" :value="old('ano', $campeonato->ano)" required />
                                <x-input-error :messages="$errors->get('ano')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div>
                            <x-input-label for="descricao" value="Descrição (Opcional)" />
                            <textarea id="descricao" name="descricao" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('descricao', $campeonato->descricao) }}</textarea>
                            <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                        </div>

                        <!-- Logo do Campeonato -->
                        <div>
                            <x-input-label for="logo" value="Logo do Campeonato (Opcional)" />
                            @if($campeonato->logo_url)
                                <img src="{{ asset('storage/' . $campeonato->logo_url) }}" alt="Logo atual" class="mt-2 mb-2 rounded-md max-h-24">
                                <p class="text-xs text-gray-500 mb-2">Logo atual. Envie um novo ficheiro para substituir.</p>
                            @endif
                            <input id="logo" name="logo" type="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Atualizar Campeonato</x-primary-button>
                            <a href="{{ route('organizador.campeonatos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
