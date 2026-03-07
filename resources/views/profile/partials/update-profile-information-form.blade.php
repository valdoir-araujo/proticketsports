<section>
    {{-- Carrega CSS e JS do Cropper.js --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-8" enctype="multipart/form-data">
        @csrf
        @method('patch')

        {{-- 
            CARD 1: INFORMAÇÕES DA CONTA & FOTO 
        --}}
        <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                <header class="mb-6">
                    <h2 class="text-xl font-bold text-slate-800">Informações da Conta</h2>
                    <p class="mt-1 text-sm text-slate-500">Dados de login e identificação visual.</p>
                </header>

                @if (session('sucesso') || session('status') === 'profile-updated')
                    <div class="mb-6 p-4 text-sm text-green-700 bg-green-50 rounded-xl border border-green-200 flex items-center" role="alert">
                        <i class="fa-solid fa-circle-check mr-2"></i>
                        <span class="font-medium mr-1">Sucesso!</span> {{ session('sucesso') ?? 'Perfil atualizado.' }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="mb-6 p-4 text-sm text-orange-800 bg-orange-50 rounded-xl border border-orange-200 flex items-center" role="alert">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        <span class="font-medium mr-1">Informação:</span> {{ session('info') }}
                    </div>
                @endif

                <div class="flex flex-col md:flex-row items-start gap-8"
                     x-data="{ 
                        photoPreview: null,
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
                                        if (this.cropper) { this.cropper.destroy(); }
                                        this.cropper = new Cropper(this.$refs.cropperImage, {
                                            aspectRatio: 1, viewMode: 1, autoCropArea: 1, background: false,
                                        });
                                    });
                                };
                                reader.readAsDataURL(file);
                            }
                        },
                        cropImage() {
                            if (!this.cropper) return;
                            const canvas = this.cropper.getCroppedCanvas({ width: 500, height: 500 });
                            this.photoPreview = canvas.toDataURL();
                            canvas.toBlob((blob) => {
                                const file = new File([blob], 'avatar_cropped.jpg', { type: 'image/jpeg' });
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                this.$refs.photoInput.files = dataTransfer.files;
                            }, 'image/jpeg', 0.85);
                            this.showCropperModal = false;
                            this.cropper.destroy(); this.cropper = null;
                        },
                        cancelCrop() {
                            this.showCropperModal = false;
                            if (this.cropper) { this.cropper.destroy(); }
                            this.$refs.photoInput.value = '';
                        }
                     }">

                    {{-- Lado Esquerdo: Foto --}}
                    @if(auth()->user()->isAtleta() && $user->atleta)
                        <div class="flex-shrink-0 w-full md:w-auto flex flex-col items-center gap-4">
                            <div class="relative group cursor-pointer" @click="$refs.photoInput.click()">
                                <div class="w-32 h-32 rounded-full bg-slate-50 flex items-center justify-center overflow-hidden shadow-md border-4 border-white ring-1 ring-slate-100">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!photoPreview">
                                        @if ($user->atleta->foto_url)
                                            <img src="{{ asset('storage/' . $user->atleta->foto_url) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-camera text-3xl text-slate-300"></i>
                                        @endif
                                    </template>
                                </div>
                                <div class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-sm border border-slate-200 text-slate-500 group-hover:text-orange-600 transition-colors">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </div>
                            </div>

                            <input type="file" name="foto" id="foto" class="hidden" accept="image/*" x-ref="photoInput" @change="onFileChange($event)">
                            <x-input-error :messages="$errors->get('foto')" />
                        </div>
                    @endif

                    {{-- Lado Direito: Campos --}}
                    <div class="flex-grow w-full grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" value="Nome Completo" class="font-bold text-slate-700" />
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-user text-slate-400"></i></div>
                                <x-text-input id="name" name="name" type="text" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" :value="old('name', $user->name)" required autocomplete="name" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="email" value="E-mail de Login" class="font-bold text-slate-700" />
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-envelope text-slate-400"></i></div>
                                <x-text-input id="email" name="email" type="email" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" :value="old('email', $user->email)" required autocomplete="username" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                    </div>

                    {{-- Modal de Recorte --}}
                    <div x-show="showCropperModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>
                        <div class="flex min-h-full items-center justify-center p-4">
                            <div class="relative transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all sm:w-full sm:max-w-lg">
                                <div class="bg-white px-6 py-6">
                                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-crop-simple text-orange-500"></i> Ajustar Foto</h3>
                                    <div class="relative w-full h-[350px] bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                                        <img x-ref="cropperImage" class="block max-w-full" style="display: none;">
                                    </div>
                                    <p class="text-xs text-slate-500 mt-3 text-center">Arraste e zoom para ajustar o enquadramento.</p>
                                </div>
                                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3">
                                    <button type="button" @click="cropImage()" class="inline-flex w-full justify-center rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-orange-500 sm:w-auto transition-all hover:-translate-y-0.5">Salvar Foto</button>
                                    <button type="button" @click="cancelCrop()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
            CARD 2: DADOS DE ATLETA
        --}}
        @if(auth()->user()->isAtleta() && $user->atleta)
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100 overflow-hidden"
                 x-data="{
                     estadoSelecionado: '{{ old('estado_id', $user->atleta?->estado_id) }}',
                     cidadeSelecionada: '{{ old('cidade_id', $user->atleta?->cidade_id) }}',
                     cidades: {{ $cidades->toJson() }},
                     async getCidades() {
                         if (!this.estadoSelecionado) { this.cidades = []; return; }
                         const response = await fetch(`/api/estados/${this.estadoSelecionado}/cidades`);
                         this.cidades = await response.json();
                     }
                 }"
                 x-init="if (estadoSelecionado && cidades.length === 0) { getCidades(); }">
                
                <div class="p-6 sm:p-8">
                    <header class="mb-8 border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-bold text-slate-800">Perfil do Atleta</h2>
                        <p class="mt-1 text-sm text-slate-500">Dados utilizados para inscrições automáticas em eventos.</p>
                    </header>

                    <div class="space-y-8">
                        {{-- Seção: Dados Pessoais --}}
                        <div>
                            <h3 class="text-xs font-bold text-orange-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-user-tag"></i> Dados Pessoais
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div class="opacity-70"> {{-- CPF Readonly --}}
                                    <x-input-label for="documento" value="CPF" class="text-slate-600" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-id-card text-slate-400"></i></div>
                                        <x-text-input id="documento" type="text" class="block w-full pl-10 bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed" :value="$user->documento" disabled readonly />
                                    </div>
                                </div>

                                <div class="opacity-70"> 
                                    {{-- 
                                       DATA DE NASCIMENTO: AGORA BLOQUEADO 
                                       - Removido o 'name' para não enviar no POST
                                       - Adicionado 'disabled' para bloquear funcionalmente
                                       - Adicionado 'readonly' para bloquear input
                                       - Estilizado como desabilitado
                                    --}}
                                    <x-input-label for="data_nascimento" value="Data de Nascimento" class="text-slate-600" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-cake-candles text-slate-400"></i></div>
                                        <x-text-input 
                                            id="data_nascimento" 
                                            {{-- name="data_nascimento" REMOVIDO para segurança no envio --}}
                                            type="date" 
                                            class="block w-full pl-10 bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed focus:border-slate-200 focus:ring-0" 
                                            :value="old('data_nascimento', $user->atleta?->data_nascimento?->format('Y-m-d'))" 
                                            disabled 
                                            readonly
                                        />
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Para alterar, contate o administrador.</p>
                                </div>

                                <div>
                                    <x-input-label for="sexo" value="Gênero" class="text-slate-700" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-venus-mars text-slate-400"></i></div>
                                        <select name="sexo" id="sexo" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                            <option value="">Selecione...</option>
                                            <option value="masculino" @selected(old('sexo', $user->atleta?->sexo) == 'masculino')>Masculino</option>
                                            <option value="feminino" @selected(old('sexo', $user->atleta?->sexo) == 'feminino')>Feminino</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="tipo_sanguineo" value="Tipo Sanguíneo" class="text-slate-700" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-droplet text-red-400"></i></div>
                                        <select name="tipo_sanguineo" id="tipo_sanguineo" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                            <option value="">Não informar</option>
                                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                                <option value="{{ $tipo }}" @selected(old('tipo_sanguineo', $user->atleta?->tipo_sanguineo) == $tipo)>{{ $tipo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Contato e Localização --}}
                        <div>
                            <h3 class="text-xs font-bold text-orange-500 uppercase tracking-wider mb-4 flex items-center gap-2 border-t border-slate-100 pt-6">
                                <i class="fa-solid fa-map-location-dot"></i> Contato & Localização
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="telefone" value="Celular / WhatsApp" class="text-slate-700" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-brands fa-whatsapp text-green-500"></i></div>
                                        <x-text-input id="telefone" name="telefone" type="text" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" :value="old('telefone', $user->atleta?->telefone)" placeholder="(00) 00000-0000" />
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('telefone')" />
                                </div>
                                
                                <div>
                                    <x-input-label for="estado_id" value="Estado" class="text-slate-700" />
                                    <select name="estado_id" id="estado_id" x-model="estadoSelecionado" @change="cidadeSelecionada = ''; getCidades()" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                        <option value="">Selecione...</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}" @selected(old('estado_id', $user->atleta?->estado_id) == $estado->id)>{{ $estado->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="cidade_id" value="Cidade" class="text-slate-700" />
                                    <select name="cidade_id" id="cidade_id" x-model="cidadeSelecionada" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm bg-white disabled:bg-slate-50" :disabled="!estadoSelecionado">
                                        <option value="">Selecione...</option>
                                        <template x-for="cidade in cidades">
                                            <option :value="cidade.id" x-text="cidade.nome" :selected="cidade.id == cidadeSelecionada"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Equipe --}}
                        <div>
                            <h3 class="text-xs font-bold text-orange-500 uppercase tracking-wider mb-4 flex items-center gap-2 border-t border-slate-100 pt-6">
                                <i class="fa-solid fa-users-gear"></i> Afiliação
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="equipe_id" value="Sua Equipe Principal" class="text-slate-700" />
                                    <div class="flex gap-2 mt-1">
                                        <select name="equipe_id" id="equipe_id" class="block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                            <option value="">Nenhuma equipe (Individual)</option>
                                            @foreach($equipes as $equipe)
                                                <option value="{{ $equipe->id }}" @selected(old('equipe_id', $user->atleta?->equipe_id) == $equipe->id)>{{ $equipe->nome }}</option>
                                            @endforeach
                                        </select>
                                        <a href="{{ route('equipes.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 hover:text-orange-600 font-bold text-xs uppercase flex items-center transition" title="Gerenciar Equipes">
                                            <i class="fa-solid fa-gear"></i>
                                        </a>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('equipe_id')" />
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Emergência --}}
                        <div class="bg-red-50 p-6 rounded-xl border border-red-100">
                            <h3 class="text-xs font-bold text-red-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-kit-medical"></i> Contato de Emergência
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div>
                                    <x-input-label for="contato_emergencia_nome" value="Nome do Contato" class="text-red-900" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-user-shield text-red-300"></i></div>
                                        <x-text-input id="contato_emergencia_nome" name="contato_emergencia_nome" type="text" class="block w-full pl-10 border-red-200 focus:border-red-500 focus:ring-red-500 rounded-lg" :value="old('contato_emergencia_nome', $user->atleta?->contato_emergencia_nome)" />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="contato_emergencia_telefone" value="Telefone do Contato" class="text-red-900" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-phone-volume text-red-300"></i></div>
                                        <x-text-input id="contato_emergencia_telefone" name="contato_emergencia_telefone" type="text" class="block w-full pl-10 border-red-200 focus:border-red-500 focus:ring-red-500 rounded-lg" :value="old('contato_emergencia_telefone', $user->atleta?->contato_emergencia_telefone)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- 
            CARD 3: INTEGRAÇÕES 
        --}}
        @if(auth()->user()->isAtleta() && $user->atleta)
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100 mt-8 overflow-hidden">
                <div class="p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center shrink-0 border border-orange-100">
                            <img src="https://seeklogo.com/images/S/strava-logo-C404F1D344-seeklogo.com.png" alt="Strava" class="w-8 h-8 opacity-90">
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Strava</h3>
                            <p class="text-sm text-slate-500">Conecte sua conta para sincronizar atividades automaticamente.</p>
                        </div>
                    </div>

                    <div>
                        @if ($user->atleta->strava_id)
                            <div class="flex items-center gap-4 bg-green-50 px-4 py-2 rounded-xl border border-green-200">
                                <div class="flex items-center gap-2 text-green-700 font-bold text-sm">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    Conectado
                                </div>
                                <div class="h-4 w-px bg-green-200"></div>
                                <a href="{{ route('strava.disconnect') }}" class="text-xs font-bold text-red-500 hover:text-red-700 transition">Desconectar</a>
                            </div>
                        @else
                            <a href="{{ route('strava.connect') }}" class="inline-flex items-center px-6 py-3 bg-[#FC4C02] hover:bg-[#e34402] text-white font-bold rounded-xl shadow-md transition transform hover:-translate-y-0.5">
                                Conectar Strava
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- BARRA DE AÇÃO FLUTUANTE --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 shadow-lg z-40 md:static md:bg-transparent md:border-0 md:shadow-none md:p-0 md:mt-8">
            <div class="max-w-7xl mx-auto flex justify-end">
                <button type="submit" class="inline-flex items-center px-8 py-3 bg-slate-800 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-slate-700 focus:bg-slate-700 active:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-slate-500/30">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Salvar Alterações
                </button>
            </div>
        </div>
    </form>
</section>