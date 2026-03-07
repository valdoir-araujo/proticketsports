<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Usuário: <span class="font-normal text-indigo-600">{{ $usuario->name }}</span>
            </h2>
            <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Voltar para a Lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                    <div class="flex items-center gap-2 text-red-800 font-bold mb-2">
                        <i class="fa-solid fa-circle-exclamation"></i> Erros encontrados:
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" 
                  class="space-y-6"
                  x-data="{
                      estadoSelecionado: '{{ old('estado_id', $usuario->atleta?->estado_id) }}',
                      cidadeSelecionada: '{{ old('cidade_id', $usuario->atleta?->cidade_id) }}',
                      cidades: [],
                      async getCidades() {
                          if (!this.estadoSelecionado) { this.cidades = []; return; }
                          try {
                              const response = await fetch(`/api/estados/${this.estadoSelecionado}/cidades`);
                              this.cidades = await response.json();
                          } catch (e) { console.error('Erro ao carregar cidades'); }
                      }
                  }"
                  x-init="if (estadoSelecionado) { getCidades(); }">
                
                @csrf
                @method('PATCH')

                {{-- BLOCO 1: DADOS DA CONTA --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-indigo-500"></i> Informações da Conta
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <x-input-label for="name" value="Nome Completo" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $usuario->name)" required />
                        </div>

                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $usuario->email)" required />
                        </div>

                        <div>
                            <x-input-label for="tipo_usuario" value="Tipo de Usuário (Permissão)" />
                            <select name="tipo_usuario" id="tipo_usuario" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-slate-50">
                                <option value="atleta" {{ old('tipo_usuario', $usuario->tipo_usuario) == 'atleta' ? 'selected' : '' }}>Atleta (Padrão)</option>
                                <option value="organizador" {{ old('tipo_usuario', $usuario->tipo_usuario) == 'organizador' ? 'selected' : '' }}>Organizador</option>
                                <option value="admin" {{ old('tipo_usuario', $usuario->tipo_usuario) == 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="status" value="Status da Conta" />
                            <select name="status" id="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="ativo" {{ old('status', $usuario->status) == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="inativo" {{ old('status', $usuario->status) == 'inativo' ? 'selected' : '' }}>Inativo / Bloqueado</option>
                            </select>
                        </div>

                    </div>
                </div>

                {{-- BLOCO 2: DADOS PESSOAIS E ATLETA --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-address-card text-orange-500"></i> Dados Pessoais e Atleta
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <div>
                            <x-input-label for="documento" value="CPF" />
                            <x-text-input id="documento" class="block mt-1 w-full" type="text" name="documento" :value="old('documento', $usuario->documento)" placeholder="000.000.000-00" />
                        </div>

                        <div>
                            <x-input-label for="celular" value="Celular / WhatsApp" />
                            <x-text-input id="celular" class="block mt-1 w-full" type="text" name="celular" :value="old('celular', $usuario->celular ?? $usuario->atleta?->telefone)" />
                        </div>

                        <div>
                            <x-input-label for="data_nascimento" value="Data de Nascimento" />
                            <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date" name="data_nascimento" :value="old('data_nascimento', $usuario->atleta?->data_nascimento?->format('Y-m-d'))" />
                        </div>

                        <div>
                            <x-input-label for="sexo" value="Gênero" />
                            <select name="sexo" id="sexo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Selecione...</option>
                                <option value="masculino" @selected(old('sexo', $usuario->atleta?->sexo) == 'masculino')>Masculino</option>
                                <option value="feminino" @selected(old('sexo', $usuario->atleta?->sexo) == 'feminino')>Feminino</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="tipo_sanguineo" value="Tipo Sanguíneo" />
                            <select name="tipo_sanguineo" id="tipo_sanguineo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Selecione...</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tipo_sanguineo', $usuario->atleta?->tipo_sanguineo) == $tipo)>{{ $tipo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="equipe_id" value="Equipe Principal" />
                            <select name="equipe_id" id="equipe_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Sem equipe</option>
                                @if(isset($equipes))
                                    @foreach($equipes as $equipe)
                                        <option value="{{ $equipe->id }}" @selected(old('equipe_id', $usuario->atleta?->equipe_id) == $equipe->id)>{{ $equipe->nome }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                    </div>
                </div>

                {{-- BLOCO 3: LOCALIZAÇÃO E EMERGÊNCIA --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-teal-500"></i> Endereço e Emergência
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <x-input-label for="estado_id" value="Estado" />
                            <select name="estado_id" id="estado_id" x-model="estadoSelecionado" @change="cidadeSelecionada = ''; getCidades()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Selecione...</option>
                                @if(isset($estados))
                                    @foreach($estados as $estado)
                                        <option value="{{ $estado->id }}" @selected(old('estado_id', $usuario->atleta?->estado_id) == $estado->id)>
                                            {{ $estado->nome }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div>
                            <x-input-label for="cidade_id" value="Cidade" />
                            <select name="cidade_id" id="cidade_id" x-model="cidadeSelecionada" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-white disabled:bg-gray-100" :disabled="!estadoSelecionado">
                                <option value="">Selecione...</option>
                                <template x-for="cidade in cidades">
                                    <option :value="cidade.id" x-text="cidade.nome" :selected="cidade.id == cidadeSelecionada"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="contato_emergencia_nome" value="Nome Contato Emergência" />
                            <x-text-input id="contato_emergencia_nome" class="block mt-1 w-full" type="text" name="contato_emergencia_nome" :value="old('contato_emergencia_nome', $usuario->atleta?->contato_emergencia_nome)" />
                        </div>

                        <div>
                            <x-input-label for="contato_emergencia_telefone" value="Telefone Emergência" />
                            <x-text-input id="contato_emergencia_telefone" class="block mt-1 w-full" type="text" name="contato_emergencia_telefone" :value="old('contato_emergencia_telefone', $usuario->atleta?->contato_emergencia_telefone)" />
                        </div>

                    </div>
                </div>

                {{-- BARRA DE AÇÃO FIXA --}}
                <div class="flex items-center justify-end gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200 sticky bottom-4 shadow-lg z-10">
                    <a href="{{ route('admin.usuarios.index') }}" class="text-sm text-gray-600 hover:text-gray-900 font-semibold">Cancelar</a>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                        <i class="fa-solid fa-save mr-2"></i> Salvar Alterações
                    </x-primary-button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>