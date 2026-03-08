@extends('layouts.public')

@section('title', 'Criar Conta - Proticketsports')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white py-12 sm:py-16 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden z-0 pointer-events-none">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-orange-500/5 to-transparent"></div>
        <div class="absolute top-[-10%] right-[-5%] w-[40%] h-[40%] bg-orange-400/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[35%] h-[35%] bg-slate-400/10 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-4xl mx-auto relative z-10">
        {{-- Cabeçalho --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Criar conta
            </h1>
            <p class="mt-2 text-slate-600 text-sm sm:text-base">
                Preencha seus dados para se inscrever em eventos.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm font-semibold text-red-800">Corrija os campos abaixo:</p>
                <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $cepOld = old('cep', '');
            $cepFormatted = $cepOld ? preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', preg_replace('/\D/', '', $cepOld)) : '';
        @endphp

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
              x-data="{
                  estados: {{ $estados->toJson() }},
                  cidades: [],
                  estadoSelecionado: '{{ old('estado_id') }}',
                  cidadeSelecionada: '{{ old('cidade_id') }}',
                  documentValue: '{{ old('documento', '') }}',
                  souEstrangeiro: {{ old('estrangeiro') ? 'true' : 'false' }},
                  cepValue: {{ json_encode($cepFormatted) }},
                  logradouro: '{{ old('logradouro', '') }}',
                  numero: '{{ old('numero', '') }}',
                  complemento: '{{ old('complemento', '') }}',
                  bairro: '{{ old('bairro', '') }}',
                  cepLoading: false,
                  cepError: '',
                  fotoPreview: null,
                  get estadoCidadeObrigatorios() { return !this.souEstrangeiro && this.documentValue !== null && String(this.documentValue).trim() !== ''; },
                  async getCidades() {
                      if (!this.estadoSelecionado) { this.cidades = []; return; }
                      try {
                          const r = await fetch(`/api/estados/${this.estadoSelecionado}/cidades`);
                          if (!r.ok) return;
                          const data = await r.json();
                          this.cidades = data;
                          if (this.cidades.findIndex(c => c.id == this.cidadeSelecionada) === -1) this.cidadeSelecionada = '';
                      } catch (e) { this.cidades = []; }
                  },
                  async buscarCep() {
                      const cep = String(this.cepValue).replace(/\D/g, '');
                      if (cep.length !== 8) { this.cepError = 'Informe um CEP com 8 dígitos.'; return; }
                      this.cepError = '';
                      this.cepLoading = true;
                      try {
                          const r = await fetch(`/api/cep?cep=${cep}`);
                          const data = await r.json();
                          if (data.erro) { this.cepError = data.mensagem || 'CEP não encontrado.'; return; }
                          this.logradouro = data.logradouro || '';
                          this.bairro = data.bairro || '';
                          this.complemento = data.complemento || '';
                          this.cepValue = data.cep || cep.replace(/^(\d{5})(\d{3})$/, '$1-$2');
                          if (data.estado_id) this.estadoSelecionado = String(data.estado_id);
                          if (data.cidades && data.cidades.length) { this.cidades = data.cidades; this.cidadeSelecionada = data.cidade_id ? String(data.cidade_id) : ''; }
                          else this.getCidades();
                      } catch (e) { this.cepError = 'Não foi possível buscar o CEP.'; }
                      this.cepLoading = false;
                  }
              }"
              x-init="if (estadoSelecionado) getCidades()"
              class="space-y-6">
            @csrf

            {{-- BLOCO 1: PERFIL --}}
            <div class="relative bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-orange-600" aria-hidden="true"></div>
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                        <i class="fa-solid fa-user text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Dados pessoais</h3>
                        <p class="text-xs text-slate-500">Informações básicas e foto de perfil</p>
                    </div>
                </div>
                <div class="p-6 sm:p-8 space-y-6">
                    {{-- Foto de perfil --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Foto de perfil</label>
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="w-24 h-24 rounded-full border-2 border-dashed border-slate-300 flex items-center justify-center bg-slate-50 overflow-hidden shrink-0">
                                <template x-if="fotoPreview">
                                    <img :src="fotoPreview" alt="Preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!fotoPreview">
                                    <i class="fa-solid fa-camera text-2xl text-slate-400"></i>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg"
                                    class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-orange-50 file:text-orange-600 file:font-semibold file:cursor-pointer hover:file:bg-orange-100"
                                    @change="fotoPreview = null; const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => fotoPreview = e.target.result; r.readAsDataURL(f); }">
                                <p class="mt-1 text-xs text-slate-500">Opcional. JPG ou PNG, máx. 1 MB.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Estrangeiro --}}
                    <div class="flex justify-end">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-orange-600 cursor-pointer transition-colors select-none">
                            <input type="checkbox" name="estrangeiro" value="1" id="estrangeiro" x-model="souEstrangeiro"
                                class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500 cursor-pointer">
                            <span>Sou atleta estrangeiro</span>
                        </label>
                    </div>

                    {{-- Nome --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nome completo</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Como no documento"
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                    </div>

                    {{-- CPF / documento --}}
                    <div>
                        <label for="documento" class="block text-sm font-semibold text-slate-700 mb-1.5">CPF</label>
                        <input type="text" name="documento" id="documento" x-model="documentValue" value="{{ old('documento') }}"
                            :placeholder="souEstrangeiro ? 'Não necessário' : '000.000.000-00'"
                            :disabled="souEstrangeiro"
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all disabled:opacity-60 disabled:bg-slate-100">
                        <p class="mt-1 text-xs text-slate-500" x-show="souEstrangeiro">Login será pelo e-mail.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="data_nascimento" class="block text-sm font-semibold text-slate-700 mb-1.5">Data de nascimento</label>
                            <input type="date" name="data_nascimento" id="data_nascimento" value="{{ old('data_nascimento') }}" required
                                class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                        </div>
                        <div>
                            <label for="sexo" class="block text-sm font-semibold text-slate-700 mb-1.5">Gênero</label>
                            <select name="sexo" id="sexo" required class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                                <option value="" disabled selected>Selecione</option>
                                <option value="masculino" @selected(old('sexo') == 'masculino')>Masculino</option>
                                <option value="feminino" @selected(old('sexo') == 'feminino')>Feminino</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="telefone" class="block text-sm font-semibold text-slate-700 mb-1.5">WhatsApp / celular</label>
                        <input type="text" name="telefone" id="telefone" value="{{ old('telefone') }}" required placeholder="(00) 00000-0000"
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                    </div>
                </div>
            </div>

            {{-- BLOCO 2: ENDEREÇO (oculto se estrangeiro) --}}
            <div x-show="!souEstrangeiro" x-transition class="relative bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-green-600" aria-hidden="true"></div>
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Endereço</h3>
                        <p class="text-xs text-slate-500">CEP, rua, número, bairro, cidade e estado</p>
                    </div>
                </div>
                <div class="p-6 sm:p-8 space-y-6">
                    <p class="text-xs text-slate-500 -mt-2">Informe o CEP e clique em Buscar para preencher automaticamente.</p>
                    <div class="flex flex-wrap gap-2 items-end">
                            <div class="flex-1 min-w-[140px]">
                                <label for="cep" class="block text-sm font-semibold text-slate-700 mb-1.5">CEP</label>
                                <input type="text" name="cep" id="cep" x-model="cepValue"
                                    @input="cepValue = $event.target.value.replace(/\D/g,'').replace(/^(\d{5})(\d{3})$/, '$1-$2')"
                                    placeholder="00000-000" maxlength="9"
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                            </div>
                            <button type="button" @click="buscarCep()" :disabled="cepLoading"
                                class="px-5 py-3 rounded-xl font-semibold text-sm bg-orange-500 text-white hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span x-show="!cepLoading">Buscar</span>
                                <span x-show="cepLoading" class="inline-flex items-center gap-1"><i class="fa-solid fa-spinner fa-spin"></i> Buscando...</span>
                            </button>
                        </div>
                        <p class="text-sm text-red-600 font-medium" x-show="cepError" x-text="cepError"></p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="logradouro" class="block text-sm font-semibold text-slate-700 mb-1.5">Logradouro</label>
                                <input type="text" name="logradouro" id="logradouro" x-model="logradouro" placeholder="Rua, avenida..."
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                            </div>
                            <div>
                                <label for="numero" class="block text-sm font-semibold text-slate-700 mb-1.5">Número</label>
                                <input type="text" name="numero" id="numero" x-model="numero" placeholder="Nº"
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                            </div>
                            <div>
                                <label for="complemento" class="block text-sm font-semibold text-slate-700 mb-1.5">Complemento</label>
                                <input type="text" name="complemento" id="complemento" x-model="complemento" placeholder="Apto, bloco..."
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="bairro" class="block text-sm font-semibold text-slate-700 mb-1.5">Bairro</label>
                                <input type="text" name="bairro" id="bairro" x-model="bairro" placeholder="Bairro"
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                            </div>
                        </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="estado_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                            <select name="estado_id" id="estado_id" x-model="estadoSelecionado" @change="getCidades()" :required="estadoCidadeObrigatorios"
                                class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                                <option value="">Selecione</option>
                                <template x-for="estado in estados" :key="estado.id">
                                    <option :value="estado.id" x-text="estado.nome"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label for="cidade_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Cidade</label>
                            <select name="cidade_id" id="cidade_id" x-model="cidadeSelecionada" :disabled="!estadoSelecionado || cidades.length === 0" :required="estadoCidadeObrigatorios"
                                class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all disabled:opacity-50">
                                <option value="">Selecione</option>
                                <template x-for="cidade in cidades" :key="cidade.id">
                                    <option :value="cidade.id" x-text="cidade.nome"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOCO 3: ACESSO --}}
            <div class="relative bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-purple-600" aria-hidden="true"></div>
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                        <i class="fa-solid fa-lock text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Acesso</h3>
                        <p class="text-xs text-slate-500">E-mail e senha para entrar na plataforma</p>
                    </div>
                </div>
                <div class="p-6 sm:p-8 space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">E-mail</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="seu@email.com"
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Senha</label>
                            <input type="password" name="password" id="password" required placeholder="Mín. 5 caracteres"
                                class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirmar senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Repita a senha"
                                class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/20 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOCO 4: FINALIZAR --}}
            <div class="relative bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-orange-600" aria-hidden="true"></div>
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="flex items-start gap-3">
                        <input id="terms" name="terms" type="checkbox" required class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500 cursor-pointer">
                        <label for="terms" class="text-sm text-slate-600 cursor-pointer select-none">
                            Concordo com os <a href="{{ route('politica.privacidade') }}" target="_blank" rel="noopener" class="text-orange-600 hover:underline">Termos e Política de Privacidade</a>.
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-base font-bold text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 shadow-lg shadow-orange-500/25 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check text-white/90"></i>
                            Criar conta
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors">
                    Já tenho conta — Entrar
                </a>
            </p>
        </form>
    </div>
</div>
@endsection