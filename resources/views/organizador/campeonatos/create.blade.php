<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Criar Novo Campeonato
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <form method="POST" action="{{ route('organizador.campeonatos.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- 
                            ==========================================================
                            ⬇️ CORREÇÃO 1: CAMPO ESCONDIDO PARA O ID DA ORGANIZAÇÃO ⬇️
                            Isto "apanha" o 'org_id' da URL e envia-o com o formulário.
                            ==========================================================
                        --}}
                        <input type="hidden" name="organizacao_id" value="{{ request()->query('org_id') }}">

                        <!-- Nome e Ano -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="nome" value="Nome do Campeonato" />
                                <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')" required autofocus />
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="ano" value="Ano/Temporada" />
                                <x-text-input id="ano" name="ano" type="number" class="mt-1 block w-full" :value="old('ano', date('Y'))" required />
                                <x-input-error :messages="$errors->get('ano')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div>
                            <x-input-label for="descricao" value="Descrição (Opcional)" />
                            <textarea id="descricao" name="descricao" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('descricao') }}</textarea>
                            <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                        </div>

                        <!-- Logo do Campeonato -->
                        <div>
                            <x-input-label for="logo" value="Logo do Campeonato (Opcional)" />
                            
                            {{-- Estilo do input de ficheiro (mantido) --}}
                            <input id="logo" name="logo" type="file" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm file:border-0 file:mr-4 file:py-1 file:px-3 file:text-sm file:font-medium text-gray-700"/>
                            
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Salvar Campeonato</x-primary-button>
                            <a href="{{ route('organizador.dashboard', ['org_id' => request()->query('org_id')]) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>