<x-app-layout>
    {{-- CABEÇALHO HERO (Padrão Moderno) --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10 text-center md:text-left">
                    <div class="flex items-center gap-3 mb-2 justify-center md:justify-start text-orange-200 text-sm font-medium">
                        <a href="{{ route('organizador.index') }}" class="hover:text-white transition-colors flex items-center">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Minhas Organizações
                        </a>
                        <span class="opacity-50">/</span>
                        <span class="text-white">Editar</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Editar Organização
                    </h1>
                    <p class="text-slate-300 mt-2 text-lg font-light opacity-90">
                        Atualize os dados da <span class="font-bold text-white">{{ $organizacao->nome_fantasia ?? $organizacao->nome }}</span>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL (Sobreposto) --}}
    <div class="relative z-20 -mt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-6 md:p-10">
                    
                    {{-- Mensagens de Erro --}}
                    @if ($errors->any())
                        <div class="mb-8 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 shadow-sm">
                            <p class="font-bold flex items-center mb-2"><i class="fa-solid fa-circle-exclamation mr-2"></i> Erros encontrados:</p>
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('organizador.organizacao.update', $organizacao->id) }}" enctype="multipart/form-data"
                          x-data="{
                              estadoSelecionado: '{{ old('estado_id', $organizacao->cidade->estado_id ?? '') }}',
                              cidadeSelecionada: '{{ old('cidade_id', $organizacao->cidade_id ?? '') }}',
                              cidades: [],
                              async getCidades() {
                                  if (!this.estadoSelecionado) { this.cidades = []; return; }
                                  try {
                                      const response = await fetch(`/api/estados/${this.estadoSelecionado}/cidades`);
                                      this.cidades = await response.json();
                                  } catch (e) { console.error('Erro ao carregar cidades'); }
                              }
                          }"
                          x-init="if(estadoSelecionado) { getCidades(); }">
                        @csrf
                        @method('PUT')

                        {{-- SEÇÃO 1: IDENTIFICAÇÃO --}}
                        <div class="space-y-6 mb-10">
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-id-card"></i>
                                </span>
                                Identificação
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="nome" value="Razão Social / Nome Completo" class="text-slate-700 font-bold" />
                                    <x-text-input id="nome" class="block mt-1 w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="nome" :value="old('nome', $organizacao->nome)" required autofocus />
                                </div>

                                <div>
                                    <x-input-label for="nome_fantasia" value="Nome Fantasia (Como aparece no site)" class="text-slate-700 font-bold" />
                                    <x-text-input id="nome_fantasia" class="block mt-1 w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="nome_fantasia" :value="old('nome_fantasia', $organizacao->nome_fantasia)" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="documento" value="CNPJ / CPF" class="text-slate-700 font-bold" />
                                    <x-text-input id="documento" class="block mt-1 w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="documento" :value="old('documento', $organizacao->documento)" />
                                </div>

                                <div>
                                    <x-input-label for="email" value="E-mail de Contato" class="text-slate-700 font-bold" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-envelope"></i>
                                        </div>
                                        <x-text-input id="email" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="email" name="email" :value="old('email', $organizacao->email)" />
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label for="celular" value="Celular / WhatsApp" class="text-slate-700 font-bold" />
                                <div class="relative mt-1 w-full md:w-1/2">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-brands fa-whatsapp text-green-500"></i>
                                    </div>
                                    <x-text-input id="celular" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="celular" :value="old('celular', $organizacao->telefone)" />
                                </div>
                            </div>
                        </div>

                        {{-- SEÇÃO 2: ENDEREÇO --}}
                        <div class="space-y-6 mb-10">
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </span>
                                Localização
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="estado_id" value="Estado" class="text-slate-700 font-bold" />
                                    <select name="estado_id" id="estado_id" x-model="estadoSelecionado" @change="cidadeSelecionada = ''; getCidades()" class="border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm block mt-1 w-full">
                                        <option value="">Selecione...</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="cidade_id" value="Cidade" class="text-slate-700 font-bold" />
                                    <select name="cidade_id" id="cidade_id" x-model="cidadeSelecionada" class="border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm block mt-1 w-full bg-white disabled:bg-slate-50 disabled:text-slate-400" :disabled="!estadoSelecionado">
                                        <option value="">Selecione...</option>
                                        <template x-for="cidade in cidades">
                                            <option :value="cidade.id" x-text="cidade.nome" :selected="cidade.id == cidadeSelecionada"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- SEÇÃO 3: LOGO --}}
                        <div class="space-y-6 mb-10">
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-image"></i>
                                </span>
                                Logotipo
                            </h3>

                            <div class="flex flex-col sm:flex-row items-start gap-6 bg-slate-50 p-6 rounded-xl border border-slate-100">
                                <div class="w-32 h-32 rounded-2xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shadow-sm flex-shrink-0">
                                    @if($organizacao->logo_url)
                                        <img src="{{ asset('storage/' . $organizacao->logo_url) }}" alt="Logo Atual" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex flex-col items-center justify-center text-slate-300">
                                            <i class="fa-regular fa-image text-3xl mb-1"></i>
                                            <span class="text-[10px] font-bold uppercase">Sem Logo</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <x-input-label for="logo" value="Alterar Logo" class="text-slate-700 font-bold" />
                                    <input id="logo" name="logo" type="file" class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition-colors cursor-pointer"/>
                                    <p class="text-xs text-slate-500 mt-2">Formatos recomendados: JPG, PNG. Tamanho ideal: 500x500px.</p>
                                </div>
                            </div>
                        </div>

                        {{-- BOTÕES DE AÇÃO --}}
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                            <a href="{{ route('organizador.index') }}" class="px-6 py-3 bg-white border border-slate-300 rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-orange-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all shadow-lg shadow-orange-500/30 hover:-translate-y-0.5">
                                <i class="fa-solid fa-save mr-2"></i> Salvar Alterações
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>