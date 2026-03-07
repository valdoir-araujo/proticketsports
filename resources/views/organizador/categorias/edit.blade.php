<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Categoria
            </h2>
            <a href="{{ route('organizador.categorias.index', $percurso) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar para o Percurso
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">
                    <header class="border-b pb-4 mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Editando Categoria: {{ $categoria->nome }}</h3>
                        <p class="text-sm text-gray-600 mt-1">Percurso: {{ $percurso->descricao }}</p>
                    </header>

                    {{-- O action do formulário agora inclui ambos os parâmetros: 'percurso' e 'categoria' --}}
                    <form action="{{ route('organizador.categorias.update', ['percurso' => $percurso, 'categoria' => $categoria]) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        {{-- Nome da Categoria --}}
                        <div>
                            <x-input-label for="nome" value="Nome da Categoria" />
                            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $categoria->nome)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('nome')" />
                        </div>

                        {{-- Gênero --}}
                        <div>
                            <x-input-label for="genero" value="Gênero" />
                            <select name="genero" id="genero" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="masculino" @selected(old('genero', $categoria->genero) == 'masculino')>Masculino</option>
                                <option value="feminino" @selected(old('genero', $categoria->genero) == 'feminino')>Feminino</option>
                                <option value="unissex" @selected(old('genero', $categoria->genero) == 'unissex')>Unissex</option>
                                <option value="misto" @selected(old('genero', $categoria->genero) == 'misto')>Misto</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('genero')" />
                        </div>
                        
                        {{-- Filtros por Idade --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="idade_minima" value="Idade Mínima (Opcional)" />
                                <x-text-input id="idade_minima" name="idade_minima" type="number" class="mt-1 block w-full" :value="old('idade_minima', $categoria->idade_minima)" />
                            </div>
                             <div>
                                <x-input-label for="idade_maxima" value="Idade Máxima (Opcional)" />
                                <x-text-input id="idade_maxima" name="idade_maxima" type="number" class="mt-1 block w-full" :value="old('idade_maxima', $categoria->idade_maxima)" />
                            </div>
                        </div>

                        {{-- Filtros por Ano de Nascimento --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="ano_nascimento_min" value="Ano de Nasc. Mínimo (Opcional)" />
                                <x-text-input id="ano_nascimento_min" name="ano_nascimento_min" type="number" class="mt-1 block w-full" :value="old('ano_nascimento_min', $categoria->ano_nascimento_min)" />
                            </div>
                            <div>
                                <x-input-label for="ano_nascimento_max" value="Ano de Nasc. Máximo (Opcional)" />
                                <x-text-input id="ano_nascimento_max" name="ano_nascimento_max" type="number" class="mt-1 block w-full" :value="old('ano_nascimento_max', $categoria->ano_nascimento_max)" />
                            </div>
                        </div>

                        {{-- Vagas Disponíveis --}}
                        <div>
                            <x-input-label for="vagas_disponiveis" value="Vagas Disponíveis (deixe em branco para ilimitado)" />
                            <x-text-input id="vagas_disponiveis" name="vagas_disponiveis" type="number" class="mt-1 block w-full" :value="old('vagas_disponiveis', $categoria->vagas_disponiveis)" />
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="flex items-center justify-end gap-4 border-t pt-6">
                            <a href="{{ route('organizador.categorias.index', $percurso) }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                            <x-primary-button>Salvar Alterações</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
