<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Adicionar Novo Banner
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Título -->
                        <div>
                            <x-input-label for="titulo" value="Título do Banner" />
                            <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo')" required autofocus />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                        </div>

                        <!-- Subtítulo -->
                        <div>
                            <x-input-label for="subtitulo" value="Subtítulo (Opcional)" />
                            <x-text-input id="subtitulo" name="subtitulo" type="text" class="mt-1 block w-full" :value="old('subtitulo')" />
                            <x-input-error :messages="$errors->get('subtitulo')" class="mt-2" />
                        </div>

                        <!-- Link -->
                        <div>
                            <x-input-label for="link_url" value="Link de Destino (Opcional)" />
                            <x-text-input id="link_url" name="link_url" type="url" class="mt-1 block w-full" :value="old('link_url')" placeholder="https://..." />
                            <x-input-error :messages="$errors->get('link_url')" class="mt-2" />
                        </div>

                        <!-- Imagem com Otimização Automática -->
                        <div>
                            <x-input-label for="imagem" value="Imagem do Banner" />
                            <input id="imagem" name="imagem" type="file" accept="image/*" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"/>
                            <p class="mt-1 text-xs text-gray-500">Recomendado: 1920x600 pixels. Imagens grandes serão otimizadas e reduzidas automaticamente antes do envio.</p>
                            <x-input-error :messages="$errors->get('imagem')" class="mt-2" />
                        </div>

                        <!-- Status (Ativo/Inativo) -->
                        <div class="border-t pt-6">
                             <label for="ativo" class="flex items-center">
                                <input type="hidden" name="ativo" value="0">
                                <input id="ativo" name="ativo" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Manter este banner ativo e visível no site?</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Salvar Banner</x-primary-button>
                            <a href="{{ route('admin.banners.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script de Redimensionamento de Imagem no Cliente --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Função para comprimir imagem
            const handleImageUpload = (inputElement, maxWidth, quality) => {
                if (!inputElement.files || !inputElement.files[0]) return;

                const file = inputElement.files[0];
                
                // Verifica se é imagem
                if (!file.type.match(/image.*/)) return;

                const reader = new FileReader();
                reader.readAsDataURL(file);
                
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        // Redimensiona mantendo proporção se for maior que o máximo
                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // Converte para Blob (JPEG com qualidade definida) e substitui no input
                        canvas.toBlob((blob) => {
                            const newFile = new File([blob], file.name, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });

                            // Substitui o arquivo no input
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(newFile);
                            inputElement.files = dataTransfer.files;
                            
                            console.log(`Imagem otimizada: de ${(file.size/1024).toFixed(2)}KB para ${(newFile.size/1024).toFixed(2)}KB`);
                            
                        }, 'image/jpeg', quality);
                    }
                }
            };

            const bannerInput = document.getElementById('imagem');
            if(bannerInput) {
                bannerInput.addEventListener('change', function() {
                    // Otimiza para Full HD (1920px) com 80% de qualidade
                    handleImageUpload(this, 1920, 0.8);
                });
            }
        });
    </script>
</x-app-layout>