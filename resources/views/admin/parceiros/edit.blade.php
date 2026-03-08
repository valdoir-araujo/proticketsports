<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Parceiro</h2>
    </x-slot>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#descricao',
                height: 320,
                menubar: false,
                promotion: false,
                branding: false,
                plugins: 'advlist autolink lists link charmap emoticons preview anchor searchreplace visualblocks code fullscreen insertdatetime table wordcount',
                toolbar: 'undo redo | formatselect | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link emoticons | removeformat | code',
                content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; }',
                block_formats: 'Parágrafo=p; Título 3=h3; Título 4=h4;'
            });
        });
    </script>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    <form method="POST" action="{{ route('admin.parceiros.update', $parceiro) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="nome" value="Nome do parceiro / empresa" />
                                <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $parceiro->nome)" required autofocus />
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="tipo" value="Tipo" />
                                <select id="tipo" name="tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    @foreach(\App\Models\Parceiro::TIPOS as $valor => $label)
                                        <option value="{{ $valor }}" {{ old('tipo', $parceiro->tipo) == $valor ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="ordem" value="Ordem de exibição" />
                                <x-text-input id="ordem" name="ordem" type="number" min="0" class="mt-1 block w-full" :value="old('ordem', $parceiro->ordem)" />
                                <x-input-error :messages="$errors->get('ordem')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="descricao" value="Descrição (opcional)" />
                                <textarea id="descricao" name="descricao" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ old('descricao', $parceiro->descricao) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Use o editor para listas, negrito, itálico, emojis e links.</p>
                                <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="site_url" value="Site (URL)" />
                                <x-text-input id="site_url" name="site_url" type="url" class="mt-1 block w-full" :value="old('site_url', $parceiro->site_url)" placeholder="https://..." />
                                <x-input-error :messages="$errors->get('site_url')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="instagram" value="Rede Social (Instagram)" />
                                <x-text-input id="instagram" name="instagram" type="url" class="mt-1 block w-full" :value="old('instagram', $parceiro->instagram)" placeholder="https://instagram.com/usuario" />
                                <p class="mt-1 text-xs text-gray-500">Cole o link completo do perfil</p>
                                <x-input-error :messages="$errors->get('instagram')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" value="E-mail" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $parceiro->email)" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="telefone" value="Telefone" />
                                <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $parceiro->telefone)" />
                                <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="logo" value="Logo" />
                                @if($parceiro->logo_url)
                                    <img src="{{ asset('storage/' . $parceiro->logo_url) }}" alt="{{ $parceiro->nome }}" class="mt-2 mb-2 rounded-md max-h-24 object-contain">
                                    <p class="text-xs text-gray-500 mb-2">Imagem atual. Envie um novo arquivo para substituir.</p>
                                @endif
                                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                                <p class="mt-1 text-xs text-gray-500">JPG, PNG ou WEBP. Máx. 2MB.</p>
                                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2 border-t pt-6">
                                <label class="flex items-center">
                                    <input type="hidden" name="ativo" value="0">
                                    <input type="checkbox" name="ativo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-orange-500" {{ old('ativo', $parceiro->ativo) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">Parceiro ativo (visível na página pública)</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Atualizar Parceiro</x-primary-button>
                            <a href="{{ route('admin.parceiros.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
