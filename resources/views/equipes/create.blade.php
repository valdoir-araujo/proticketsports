@extends('layouts.public')

@section('title', 'Cadastrar equipe - Proticketsports')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white py-10 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('equipes.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 mb-6">
            <i class="fa-solid fa-arrow-left"></i> Voltar às equipes
        </a>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-5">
                <h1 class="text-xl sm:text-2xl font-black text-white">Cadastrar nova equipe</h1>
                <p class="text-orange-100 text-sm mt-1">Preencha os dados da equipe para utilizá-la em inscrições.</p>
            </div>

            <div class="p-6 sm:p-8">
                @if($errors->any())
                    <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('equipes.store') }}" enctype="multipart/form-data" class="space-y-6"
                      x-data="{
                          estados: {{ $estados->toJson() }},
                          cidades: [],
                          estadoSelecionado: '{{ old('estado_id') }}',
                          cidadeSelecionada: '{{ old('cidade_id') }}',
                          getCidades() {
                              if (!this.estadoSelecionado) { this.cidades = []; return; }
                              fetch('/api/estados/' + this.estadoSelecionado + '/cidades')
                                  .then(r => r.json())
                                  .then(data => {
                                      this.cidades = data;
                                      if (!this.cidades.find(c => c.id == this.cidadeSelecionada)) this.cidadeSelecionada = '';
                                  });
                          }
                      }"
                      x-init="if (estadoSelecionado) getCidades();">
                    @csrf

                    <div>
                        <label for="nome" class="block text-sm font-bold text-slate-700 mb-1">Nome da equipe</label>
                        <input id="nome" name="nome" type="text" value="{{ old('nome') }}" required autofocus
                            class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                    </div>

                    <div>
                        <label for="coordenador_id" class="block text-sm font-bold text-slate-700 mb-1">Coordenador da equipe</label>
                        <select id="coordenador_id" name="coordenador_id" required
                            class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                            <option value="">Selecione um atleta...</option>
                            @foreach($atletas as $atleta)
                                <option value="{{ $atleta->id }}" {{ old('coordenador_id') == $atleta->id ? 'selected' : '' }}>{{ $atleta->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="data_fundacao" class="block text-sm font-bold text-slate-700 mb-1">Data de fundação (opcional)</label>
                            <input id="data_fundacao" name="data_fundacao" type="date" value="{{ old('data_fundacao') }}"
                                class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                        </div>
                        <div>
                            <label for="logo" class="block text-sm font-bold text-slate-700 mb-1">Logo da equipe (opcional)</label>
                            <input id="logo" name="logo" type="file" accept="image/*"
                                class="w-full rounded-lg border-2 border-slate-300 border-dashed bg-white py-2 px-3 text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:font-semibold file:bg-orange-500 file:text-white hover:file:bg-orange-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="estado_id" class="block text-sm font-bold text-slate-700 mb-1">Estado</label>
                            <select id="estado_id" name="estado_id" x-model="estadoSelecionado" @change="getCidades()" required
                                class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                                <option value="">Selecione um estado...</option>
                                <template x-for="estado in estados" :key="estado.id">
                                    <option :value="estado.id" x-text="estado.nome"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label for="cidade_id" class="block text-sm font-bold text-slate-700 mb-1">Cidade</label>
                            <select id="cidade_id" name="cidade_id" x-model="cidadeSelecionada" required
                                class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 disabled:opacity-60"
                                :disabled="!estadoSelecionado || cidades.length === 0">
                                <option value="">Selecione uma cidade...</option>
                                <template x-for="cidade in cidades" :key="cidade.id">
                                    <option :value="cidade.id" x-text="cidade.nome"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-orange-500 text-white font-bold rounded-lg hover:bg-orange-600 shadow-lg">
                            <i class="fa-solid fa-check mr-1"></i> Cadastrar equipe
                        </button>
                        <a href="{{ route('equipes.index') }}" class="px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
