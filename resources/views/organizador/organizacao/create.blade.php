<x-app-layout>
    {{-- CABEÇALHO HERO --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10">
                    <div class="flex items-center gap-3 mb-2 text-orange-200 text-sm font-medium">
                        <a href="{{ route('organizador.index') }}" class="hover:text-white transition-colors flex items-center">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
                        </a>
                        <span class="opacity-50">/</span>
                        <span class="text-white">Nova Organização</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Criar Organização
                    </h1>
                    <p class="text-slate-300 mt-2 text-lg font-light opacity-90">
                        Preencha os dados abaixo para começar a gerenciar seus eventos.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 -mt-20 pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-8">

                    @if ($errors->any())
                        <div class="mb-8 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 shadow-sm">
                            <p class="font-bold flex items-center mb-2"><i class="fa-solid fa-circle-exclamation mr-2"></i> Corrija os erros abaixo:</p>
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('organizador.organizacao.store') }}" enctype="multipart/form-data" class="space-y-8"
                          x-data="{
                              estadoSelecionado: '',
                              cidadeSelecionada: '',
                              cidades: [],
                              async getCidades() {
                                  if (!this.estadoSelecionado) { this.cidades = []; return; }
                                  const response = await fetch(`/api/estados/${this.estadoSelecionado}/cidades`);
                                  this.cidades = await response.json();
                              }
                          }">
                        @csrf

                        {{-- SEÇÃO 1: DADOS BÁSICOS --}}
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm"><i class="fa-solid fa-building"></i></span>
                                Dados da Organização
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nome (Razão Social) --}}
                                <div>
                                    <x-input-label for="nome" value="Razão Social / Nome Completo *" class="text-slate-700 font-bold" />
                                    <x-text-input id="nome" class="block mt-1 w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="nome" :value="old('nome')" required autofocus placeholder="Ex: Eventos Esportivos LTDA" />
                                </div>

                                {{-- Nome Fantasia --}}
                                <div>
                                    <x-input-label for="nome_fantasia" value="Nome Fantasia (Público) *" class="text-slate-700 font-bold" />
                                    <x-text-input id="nome_fantasia" class="block mt-1 w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="nome_fantasia" :value="old('nome_fantasia')" required placeholder="Ex: Corrida Show" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                {{-- Documento --}}
                                <div>
                                    <x-input-label for="documento" value="CNPJ ou CPF *" class="text-slate-700 font-bold" />
                                    <x-text-input id="documento" class="block mt-1 w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="documento" :value="old('documento')" required />
                                </div>
                                
                                {{-- Logo --}}
                                <div>
                                    <x-input-label for="logo" value="Logotipo" class="text-slate-700 font-bold" />
                                    <input id="logo" name="logo" type="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer transition"/>
                                    <p class="text-xs text-slate-400 mt-1">PNG ou JPG (Max 2MB)</p>
                                </div>
                            </div>
                        </div>

                        {{-- SEÇÃO 2: CONTATO --}}
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm"><i class="fa-solid fa-address-book"></i></span>
                                Contato
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Email --}}
                                <div>
                                    <x-input-label for="email" value="E-mail Comercial *" class="text-slate-700 font-bold" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-envelope"></i></div>
                                        <x-text-input id="email" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="email" name="email" :value="old('email')" required />
                                    </div>
                                </div>

                                {{-- Telefone/Celular --}}
                                <div>
                                    <x-input-label for="celular" value="WhatsApp / Celular" class="text-slate-700 font-bold" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i class="fa-brands fa-whatsapp"></i></div>
                                        <x-text-input id="celular" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" type="text" name="celular" :value="old('celular')" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SEÇÃO 3: ENDEREÇO --}}
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-6 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm"><i class="fa-solid fa-map-location-dot"></i></span>
                                Localização
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="estado_id" value="Estado *" class="text-slate-700 font-bold" />
                                    <select name="estado_id" id="estado_id" x-model="estadoSelecionado" @change="getCidades()" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="cidade_id" value="Cidade *" class="text-slate-700 font-bold" />
                                    <select name="cidade_id" id="cidade_id" x-model="cidadeSelecionada" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm bg-white disabled:bg-slate-50 disabled:text-slate-400" :disabled="!estadoSelecionado" required>
                                        <option value="">Selecione...</option>
                                        <template x-for="cidade in cidades">
                                            <option :value="cidade.id" x-text="cidade.nome"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                            <a href="{{ route('organizador.index') }}" class="px-6 py-3 bg-white border border-slate-300 rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-orange-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all shadow-lg shadow-orange-500/30 hover:-translate-y-0.5">
                                <i class="fa-solid fa-check mr-2"></i> Criar Organização
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>