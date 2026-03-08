<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo Contato</h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <div class="py-12" x-data="{
        showCropperModal: false,
        cropper: null,
        onFileChange(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$refs.cropperImage.src = e.target.result;
                    this.showCropperModal = true;
                    this.$nextTick(() => {
                        if (this.cropper) this.cropper.destroy();
                        this.cropper = new Cropper(this.$refs.cropperImage, { aspectRatio: 1, viewMode: 1, autoCropArea: 0.8, background: false });
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        cropImage() {
            if (!this.cropper) return;
            const canvas = this.cropper.getCroppedCanvas({ width: 400, height: 400 });
            canvas.toBlob((blob) => {
                const file = new File([blob], 'contato.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.fotoInput.files = dt.files;
            }, 'image/jpeg', 0.9);
            this.showCropperModal = false;
            this.cropper.destroy();
            this.cropper = null;
        },
        cancelCrop() {
            this.showCropperModal = false;
            if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
            this.$refs.fotoInput.value = '';
        }
    }">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    <form method="POST" action="{{ route('admin.contatos.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div>
                            <x-input-label for="area" value="Área / Tipo de contato" />
                            <x-text-input id="area" name="area" type="text" class="mt-1 block w-full" :value="old('area')" placeholder="Ex: Contato Comercial, Suporte Técnico" required autofocus />
                            <x-input-error :messages="$errors->get('area')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nome" value="Nome do responsável" />
                            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')" required />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="foto" value="Foto (opcional)" />
                            <input x-ref="fotoInput" id="foto" name="foto" type="file" accept="image/*" @change="onFileChange($event)" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                            <p class="mt-1 text-xs text-gray-500">Selecione a imagem e ajuste o recorte (quadrado, centralizado). Será redimensionada para 400×400 px.</p>
                            <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                        </div>

                        <div x-show="showCropperModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                            <div class="fixed inset-0 bg-black/70"></div>
                            <div class="flex min-h-full items-center justify-center p-4">
                                <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fa-solid fa-crop-simple text-orange-500 mr-2"></i>Ajustar foto</h3>
                                    <div class="w-full h-[320px] bg-gray-100 rounded-lg overflow-hidden">
                                        <img x-ref="cropperImage" class="block max-w-full" style="display: none;">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 text-center">Arraste e use o zoom para centralizar o rosto. A área será recortada em quadrado.</p>
                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" @click="cancelCrop()" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                                        <button type="button" @click="cropImage()" class="px-4 py-2 text-sm font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-500">Aplicar recorte</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="telefone" value="Telefone / WhatsApp (opcional)" />
                                <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone')" placeholder="(00) 00000-0000" />
                                <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" value="E-mail (opcional)" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" placeholder="email@exemplo.com" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="icone" value="Ícone (Font Awesome)" />
                                <x-text-input id="icone" name="icone" type="text" class="mt-1 block w-full" :value="old('icone', 'fa-solid fa-user')" placeholder="fa-solid fa-briefcase" />
                                <p class="mt-1 text-xs text-gray-500">Ex: fa-solid fa-briefcase, fa-solid fa-headset</p>
                                <x-input-error :messages="$errors->get('icone')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="cor" value="Cor do card" />
                                <select id="cor" name="cor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    @foreach(\App\Models\Contato::CORES as $valor => $label)
                                        <option value="{{ $valor }}" {{ old('cor', 'orange') == $valor ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('cor')" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="ordem" value="Ordem de exibição" />
                                <x-text-input id="ordem" name="ordem" type="number" min="0" class="mt-1 block w-full" :value="old('ordem', 0)" />
                                <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro.</p>
                                <x-input-error :messages="$errors->get('ordem')" class="mt-2" />
                            </div>
                            <div class="flex items-center pt-8">
                                <label class="inline-flex items-center">
                                    <input type="hidden" name="ativo" value="0">
                                    <input type="checkbox" name="ativo" value="1" {{ old('ativo', true) ? 'checked' : '' }} class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500">
                                    <span class="ml-2 text-sm text-gray-700">Exibir na página de contato</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <a href="{{ route('admin.contatos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-sm transition">Cadastrar contato</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
