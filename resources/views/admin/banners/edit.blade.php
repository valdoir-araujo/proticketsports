<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Banner
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Título -->
                        <div>
                            <x-input-label for="titulo" value="Título do Banner" />
                            <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo', $banner->titulo)" required autofocus />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                        </div>

                        <!-- Subtítulo -->
                        <div>
                            <x-input-label for="subtitulo" value="Subtítulo (Opcional)" />
                            <x-text-input id="subtitulo" name="subtitulo" type="text" class="mt-1 block w-full" :value="old('subtitulo', $banner->subtitulo)" />
                            <x-input-error :messages="$errors->get('subtitulo')" class="mt-2" />
                        </div>

                        <!-- Link -->
                        <div>
                            <x-input-label for="link_url" value="Link de Destino (Opcional)" />
                            <x-text-input id="link_url" name="link_url" type="url" class="mt-1 block w-full" :value="old('link_url', $banner->link_url)" placeholder="https://..." />
                            <x-input-error :messages="$errors->get('link_url')" class="mt-2" />
                        </div>

                        <!-- Imagem -->
                        <div>
                            <x-input-label for="imagem" value="Imagem do Banner" />
                            @if($banner->imagem_url)
                                <img src="{{ asset('storage/' . $banner->imagem_url) }}" alt="Imagem atual" class="mt-2 mb-2 rounded-md max-h-40">
                                <p class="text-xs text-gray-500 mb-2">Imagem atual. Envie um novo ficheiro para substituir.</p>
                            @endif
                            <input id="imagem" name="imagem" type="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                            <p class="mt-1 text-xs text-gray-500">Recomendado: 1400x600 pixels. Formatos: JPG, PNG, WEBP. Máx: 2MB.</p>
                            <x-input-error :messages="$errors->get('imagem')" class="mt-2" />
                        </div>

                        <!-- Status (Ativo/Inativo) -->
                        <div class="border-t pt-6">
                             <label for="ativo" class="flex items-center">
                                <input type="hidden" name="ativo" value="0">
                                <input id="ativo" name="ativo" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                    @if(old('ativo', $banner->ativo)) checked @endif>
                                <span class="ml-2 text-sm text-gray-700">Manter este banner ativo e visível no site?</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Atualizar Banner</x-primary-button>
                            <a href="{{ route('admin.banners.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
