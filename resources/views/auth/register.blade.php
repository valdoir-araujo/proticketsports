@extends('layouts.public')

@section('title', 'Criar Conta - Proticketsports')

@section('content')
{{-- FUNDO VIBRANTE COM DEGRADÊ ESPORTIVO --}}
<div class="min-h-screen bg-gradient-to-br from-orange-600 via-orange-500 to-red-600 py-12 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center relative">

    {{-- Padrão de fundo sutil para textura (opcional) --}}
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>

    {{-- CABEÇALHO DA PÁGINA --}}
    <div class="text-center mb-10 relative z-10">
        <h2 class="text-4xl font-black text-white tracking-tight drop-shadow-md">
            Vamos começar! 🚴‍♂️
        </h2>
        <p class="mt-2 text-orange-100 text-lg font-medium">
            Complete os blocos abaixo para criar seu perfil de atleta.
        </p>
    </div>

    <div class="w-full max-w-5xl relative z-10">
        
        {{-- ALERTA DE ERROS --}}
        @if ($errors->any())
            <div class="mb-8 p-4 bg-white/90 backdrop-blur rounded-xl border-l-8 border-red-500 shadow-xl animate-pulse">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">Ops! Precisamos corrigir algumas coisas:</h3>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" 
              x-data="{
                  estados: {{ $estados->toJson() }},
                  cidades: [],
                  estadoSelecionado: '{{ old('estado_id') }}',
                  cidadeSelecionada: '{{ old('cidade_id') }}',
                  async getCidades() {
                      if (!this.estadoSelecionado) { this.cidades = []; return; }
                      try {
                          const response = await fetch(`/api/estados/${this.estadoSelecionado}/cidades`);
                          if (!response.ok) throw new Error('Erro na rede');
                          const data = await response.json();
                          this.cidades = data;
                          
                          if (this.cidades.findIndex(c => c.id == this.cidadeSelecionada) === -1) {
                              this.cidadeSelecionada = '';
                          }
                      } catch (error) {
                          console.error('Erro ao buscar cidades:', error);
                      }
                  }
              }"
              x-init="if (estadoSelecionado) { await getCidades() }"
              class="space-y-6"> {{-- Espaçamento vertical entre os blocos --}}
            @csrf

            {{-- BLOCO 1: DADOS DO ATLETA --}}
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all hover:scale-[1.005]">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fa-solid fa-user text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Sobre o Atleta</h3>
                        <p class="text-xs text-slate-500">Suas informações pessoais básicas</p>
                    </div>
                </div>
                
                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nome --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nome Completo</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: João da Silva"
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-0 transition-colors">
                    </div>

                    {{-- CPF --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">CPF</label>
                        <input type="text" name="documento" value="{{ old('documento') }}" required placeholder="000.000.000-00"
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-0 transition-colors">
                    </div>

                    {{-- Nascimento --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" value="{{ old('data_nascimento') }}" required
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-0 transition-colors">
                    </div>

                    {{-- Telefone --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">WhatsApp / Celular</label>
                        <input type="text" name="telefone" value="{{ old('telefone') }}" required placeholder="(00) 00000-0000"
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-0 transition-colors">
                    </div>

                    {{-- Gênero --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Gênero</label>
                        <select name="sexo" required class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-0 transition-colors">
                            <option value="" disabled selected>Selecione...</option>
                            <option value="masculino" @selected(old('sexo') == 'masculino')>Masculino</option>
                            <option value="feminino" @selected(old('sexo') == 'feminino')>Feminino</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- BLOCO 2: LOCALIZAÇÃO --}}
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all hover:scale-[1.005]">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Onde você está?</h3>
                        <p class="text-xs text-slate-500">Para encontrarmos eventos perto de você</p>
                    </div>
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Estado</label>
                        <select name="estado_id" x-model="estadoSelecionado" @change="getCidades()" required
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 focus:border-green-500 focus:bg-white focus:ring-0 transition-colors">
                            <option value="">Selecione o Estado...</option>
                            <template x-for="estado in estados" :key="estado.id">
                                <option :value="estado.id" x-text="estado.nome" :selected="estado.id == estadoSelecionado"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Cidade</label>
                        <select name="cidade_id" x-model="cidadeSelecionada" :disabled="!estadoSelecionado || cidades.length === 0" required
                            class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 focus:border-green-500 focus:bg-white focus:ring-0 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Selecione a Cidade...</option>
                            <template x-for="cidade in cidades" :key="cidade.id">
                                <option :value="cidade.id" x-text="cidade.nome" :selected="cidade.id == cidadeSelecionada"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            {{-- BLOCO 3: SEGURANÇA E ACESSO --}}
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all hover:scale-[1.005]">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Segurança</h3>
                        <p class="text-xs text-slate-500">Defina seu acesso à plataforma</p>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Seu melhor E-mail</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-4 top-4 text-slate-400"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="exemplo@email.com"
                                class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 pl-11 pr-4 py-3 font-medium text-slate-700 focus:border-purple-500 focus:bg-white focus:ring-0 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Senha</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-4 text-slate-400"></i>
                                <input type="password" name="password" required placeholder="Mínimo 8 caracteres"
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 pl-11 pr-4 py-3 font-medium text-slate-700 focus:border-purple-500 focus:bg-white focus:ring-0 transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Confirmar Senha</label>
                            <div class="relative">
                                <i class="fa-solid fa-check-double absolute left-4 top-4 text-slate-400"></i>
                                <input type="password" name="password_confirmation" required placeholder="Repita a senha"
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 pl-11 pr-4 py-3 font-medium text-slate-700 focus:border-purple-500 focus:bg-white focus:ring-0 transition-colors">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOCO 4: FINALIZAÇÃO --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-4 pb-12">
                
                {{-- Termos --}}
                <div class="flex items-center bg-white/10 backdrop-blur-sm p-3 rounded-lg border border-white/20">
                    <input id="terms" name="terms" type="checkbox" required class="h-5 w-5 rounded border-2 border-white text-orange-600 focus:ring-offset-0 focus:ring-0 cursor-pointer">
                    <label for="terms" class="ml-3 text-sm font-medium text-white cursor-pointer select-none">
                        Concordo com os <a href="#" class="underline hover:text-orange-200">Termos de Uso</a>
                    </label>
                </div>

                {{-- Botões --}}
                <div class="flex flex-col-reverse md:flex-row items-center gap-4 w-full md:w-auto">
                    <a href="{{ route('login') }}" class="px-6 py-3 text-white text-sm font-bold hover:bg-white/10 rounded-xl transition-all">
                        Já tenho conta
                    </a>

                    <button type="submit" class="w-full md:w-auto bg-slate-900 hover:bg-slate-800 text-white font-black text-lg py-4 px-10 rounded-xl shadow-2xl shadow-slate-900/40 transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 group">
                        CRIAR MINHA CONTA
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection