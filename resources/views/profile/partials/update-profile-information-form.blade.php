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

        {{-- CARD 1: INFORMAÇÕES DA CONTA & FOTO --}}
        <div class="border-b border-slate-100 bg-slate-50/50">
            <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                <header class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600 shrink-0">
                        <i class="fa-solid fa-id-card text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Informações da Conta</h2>
                        <p class="text-sm text-slate-500">Dados de login e identificação visual.</p>
                    </div>
                </header>

                @if (session('sucesso') || session('status') === 'profile-updated')
                    <div class="mb-6 p-4 text-sm text-green-700 bg-green-50 rounded-xl border border-green-200 flex items-center gap-2" role="alert">
                        <i class="fa-solid fa-circle-check shrink-0"></i>
                        <span class="font-medium">Sucesso!</span>
                        <span>{{ session('sucesso') ?? 'Perfil atualizado.' }}</span>
                    </div>
                @endif
                @if (session('info'))
                    <div class="mb-6 p-4 text-sm text-orange-800 bg-orange-50 rounded-xl border border-orange-200 flex items-center gap-2" role="alert">
                        <i class="fa-solid fa-circle-info shrink-0"></i>
                        <span class="font-medium">Informação:</span>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <div class="flex flex-col md:flex-row items-start gap-6 sm:gap-8"
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

                    {{-- Lado Esquerdo: Foto (label para mobile: toque = abre seletor nativo) --}}
                    @if(auth()->user()->isAtleta() && $user->atleta)
                        <div class="flex-shrink-0 w-full md:w-auto flex flex-col items-center gap-4">
                            <label for="foto" class="relative group cursor-pointer block touch-manipulation" aria-label="Escolher foto de perfil">
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden shadow-inner ring-2 ring-slate-200/80 ring-offset-2 ring-offset-slate-50">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover" alt="">
                                    </template>
                                    <template x-if="!photoPreview">
                                        @if ($user->atleta->profile_photo_url)
                                            <img src="{{ $user->atleta->profile_photo_url }}" alt="Foto do perfil" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-camera text-3xl text-slate-300" aria-hidden="true"></i>
                                        @endif
                                    </template>
                                    @if($user->atleta->strava_profile_photo_url)
                                        <span class="absolute bottom-0 left-0 w-8 h-8 rounded-full bg-[#FC4C02] flex items-center justify-center text-white shadow-md border-2 border-white" title="Foto do Strava">
                                            <i class="fa-brands fa-strava text-sm" aria-hidden="true"></i>
                                        </span>
                                    @endif
                                </div>
                                <span class="absolute -bottom-1 -right-1 bg-white rounded-xl p-2 shadow-md border border-slate-200 text-slate-500 group-hover:text-orange-600 group-hover:bg-orange-50 transition-colors pointer-events-none">
                                    <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>
                                </span>
                            </label>

                            <input type="file" name="foto" id="foto" class="sr-only" accept="image/*" x-ref="photoInput" @change="onFileChange($event)">
                            <x-input-error :messages="$errors->get('foto')" />
                        </div>
                    @endif

                    {{-- Lado Direito: Campos --}}
                    <div class="flex-grow w-full grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
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
                        <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
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
            <div class="border-t border-slate-100 overflow-hidden"
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
                
                <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 bg-white">
                    <header class="flex items-center gap-3 mb-6 sm:mb-8 pb-4 border-b border-slate-100">
                        <div class="w-9 h-9 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600 shrink-0">
                            <i class="fa-solid fa-person-running text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Perfil do Atleta</h2>
                            <p class="text-sm text-slate-500">Dados utilizados para inscrições em eventos.</p>
                        </div>
                    </header>

                    {{-- Strava: logo abaixo do título Perfil do Atleta --}}
                    <div class="mb-6 sm:mb-8 space-y-4">
                        <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shrink-0 border border-orange-100 shadow-sm">
                                    <i class="fa-brands fa-strava text-2xl text-[#FC4C02]" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-800">Strava</h3>
                                    <p class="text-sm text-slate-500">Conecte sua conta para sincronizar atividades automaticamente.</p>
                                </div>
                            </div>
                            <div class="shrink-0">
                                @if ($user->atleta->strava_id)
                                    <div class="flex items-center gap-3 bg-green-50 px-4 py-2.5 rounded-xl border border-green-200">
                                        <div class="flex items-center gap-2 text-green-700 font-semibold text-sm">
                                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                            Conectado
                                        </div>
                                        <div class="h-4 w-px bg-green-200"></div>
                                        <a href="{{ route('strava.disconnect') }}" class="text-xs font-bold text-red-500 hover:text-red-700 transition">Desconectar</a>
                                    </div>
                                @else
                                    <a href="{{ route('strava.connect') }}" class="inline-flex items-center justify-center gap-2 min-h-[44px] px-5 py-2.5 bg-[#FC4C02] hover:bg-[#e34402] text-white font-bold rounded-xl shadow-sm hover:shadow transition touch-manipulation">
                                        Conectar Strava
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if(isset($strava_redirect_uri) && isset($strava_callback_domain) && !$user->atleta->strava_id && $user->isAdmin())
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-900">
                            <p class="font-semibold mb-1"><i class="fa-solid fa-info-circle mr-1"></i> Se aparecer &quot;redirect_uri invalid&quot; no Strava:</p>
                            <p class="mb-1">No painel do Strava (<a href="https://www.strava.com/settings/api" target="_blank" rel="noopener" class="underline">Settings → My API Application</a>), em <strong>Authorization Callback Domain</strong> use <strong>exatamente</strong>:</p>
                            <code class="block bg-white px-2 py-1 rounded border border-amber-200 break-all">{{ $strava_callback_domain }}</code>
                            <p class="mt-2 text-xs">URL de callback que este site usa: <code class="bg-white px-1 rounded break-all">{{ $strava_redirect_uri }}</code></p>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-6 sm:space-y-8">
                        {{-- Seção: Dados Pessoais --}}
                        <div>
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 sm:mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-slate-500"><i class="fa-solid fa-user-tag text-[10px]"></i></span>
                                Dados Pessoais
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
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
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 sm:mb-4 flex items-center gap-2 border-t border-slate-100 pt-5 sm:pt-6">
                                <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-slate-500"><i class="fa-solid fa-map-location-dot text-[10px]"></i></span>
                                Contato & Localização
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
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
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 sm:mb-4 flex items-center gap-2 border-t border-slate-100 pt-5 sm:pt-6">
                                <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-slate-500"><i class="fa-solid fa-users-gear text-[10px]"></i></span>
                                Afiliação
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
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
                        <div class="bg-red-50/80 p-4 sm:p-6 rounded-2xl border border-red-100">
                            <h3 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-red-100 flex items-center justify-center text-red-600"><i class="fa-solid fa-kit-medical text-[10px]"></i></span>
                                Contato de Emergência
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
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

        {{-- Barra de ação: fixa no mobile, estática no desktop; padding alinhado ao conteúdo --}}
        <div class="sticky bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-slate-200 z-40 md:bg-slate-50/80 md:border-t md:border-slate-100 md:rounded-b-2xl px-4 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-2 min-h-[44px] px-6 py-3 bg-orange-600 hover:bg-orange-500 text-white font-bold rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition touch-manipulation">
                    <i class="fa-solid fa-check"></i> Salvar alterações
                </button>
            </div>
        </div>
    </form>
</section>