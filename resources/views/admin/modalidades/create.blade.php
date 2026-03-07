<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nova Modalidade
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('admin.modalidades.store') }}">
                        @csrf
                        
                        {{-- Nome da Modalidade --}}
                        <div>
                            <x-input-label for="nome" value="Nome da Modalidade" />
                            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')" required autofocus />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>Salvar Modalidade</x-primary-button>
                            <a href="{{ route('admin.modalidades.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>