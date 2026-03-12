@extends('layouts.public')

@section('title', 'Inscrição para: ' . $evento->nome)

@push('styles')
<style>
    .has-[:checked] {
        --tw-border-opacity: 1;
        border-color: rgb(249 115 22 / var(--tw-border-opacity));
        --tw-ring-color: rgb(249 115 22 / var(--tw-border-opacity));
        --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
        --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
        box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
    }
    .product-free {
        border-color: rgb(22 163 74 / 0.5);
        background-color: rgb(240 253 244);
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<main class="container mx-auto p-4 md:p-8 max-w-4xl"> 
    
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0"><i class="fa-solid fa-circle-exclamation text-red-500"></i></div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Problemas na inscrição:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- O evento @change no form garante que qualquer alteração (radio, checkbox, select) recalcule o total --}}
    <form method="POST" action="{{ route('inscricao.store') }}" class="space-y-8" 
          id="form-inscricao"
          data-search-url="{{ url('/api/atletas/search') }}"
          @change="calculateTotal()"
          x-data="{
              categorySelected: {{ old('categoria_id') ? 'true' : 'false' }},
              categoryIsDupla: false,
              showTeamModal: false,
              isSubmitting: false,
              
              // Variáveis de Preço (Reativas)
              resumo: {
                  valorBase: 0,
                  valorProdutos: 0,
                  valorTaxa: 0,
                  valorTotal: 0,
                  temProdutos: false
              },
              taxaPercentual: {{ $taxaPercentual ?? 0 }},

              // --- Lógica de Parceiro ---
              searchParceiro: '',
              parceiroResults: [],
              parceiroSelected: null,
              isSearchingParceiro: false,
              searchError: '', 
              tipoPagamentoDupla: 'unico',

              // Equipe
              newTeamName: '',
              newTeamCoordenador: '{{ Auth::user()->atleta->id ?? '' }}', 
              newTeamDataFundacao: '',
              newTeamLogo: null,
              newTeamEstado: '',
              newTeamCidade: '',
              estados: {{ $estados->toJson() }},
              cidades: [],
              isSavingTeam: false,
              teamError: '',

              checkCategoryType() {
                  const selectedRadio = document.querySelector('input[name=categoria_id]:checked');
                  if (selectedRadio) {
                      this.categorySelected = true;
                      this.categoryIsDupla = selectedRadio.dataset.isDupla === '1';
                      if (!this.categoryIsDupla) { 
                          this.parceiroSelected = null; 
                          this.searchParceiro = ''; 
                          this.searchError = '';
                          this.parceiroResults = [];
                      }
                  }
                  this.calculateTotal();
              },

              calculateTotal() {
                  const selectedRadio = document.querySelector('input[name=categoria_id]:checked');
                  let base = selectedRadio ? parseFloat(selectedRadio.dataset.valor) || 0 : 0;

                  // Lógica de Dupla (Pagamento Único dobra o valor)
                  if (this.categoryIsDupla && this.tipoPagamentoDupla === 'unico') {
                      base = base * 2;
                  }
                  this.resumo.valorBase = base;

                  // Produtos
                  let prodTotal = 0;
                  let hasProd = false;
                  document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
                      hasProd = true;
                      const container = cb.closest('.product-item');
                      const qtdEl = container.querySelector('.product-quantity');
                      const qtd = qtdEl ? parseInt(qtdEl.value) : 1;
                      const val = parseFloat(cb.dataset.valorProduto) || 0;
                      prodTotal += qtd * val;
                  });
                  this.resumo.valorProdutos = prodTotal;
                  this.resumo.temProdutos = hasProd;

                  // Totais
                  const subTotal = base + prodTotal;
                  this.resumo.valorTaxa = subTotal * (this.taxaPercentual / 100);
                  this.resumo.valorTotal = subTotal + this.resumo.valorTaxa;
              },

              formatMoney(value) {
                  return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
              },

              // ... (Funções de busca de parceiro e equipe mantidas iguais) ...
              async buscarAtleta() {
                  this.searchError = ''; 
                  if (this.searchParceiro.length < 3) { this.parceiroResults = []; return; }
                  this.isSearchingParceiro = true;
                  try {
                      const baseUrl = document.getElementById('form-inscricao').getAttribute('data-search-url');
                      const url = `${baseUrl}?q=${encodeURIComponent(this.searchParceiro)}`;
                      const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                      if (response.ok) {
                          this.parceiroResults = await response.json();
                      } else {
                          this.parceiroResults = [];
                          this.searchError = 'Erro ao buscar.';
                      }
                  } catch (e) { 
                      this.searchError = 'Erro de conexão.';
                  } finally {
                      this.isSearchingParceiro = false;
                  }
              },

              selectParceiro(atleta) {
                  this.parceiroSelected = atleta;
                  this.parceiroResults = []; 
                  this.searchParceiro = ''; 
                  this.calculateTotal(); // Recalcula caso mude regra de pagamento
              },

              removeParceiro() {
                  this.parceiroSelected = null;
                  this.calculateTotal();
              },

              async getCidades() {
                  if (!this.newTeamEstado) { this.cidades = []; return; }
                  const response = await fetch(`/api/estados/${this.newTeamEstado}/cidades`);
                  this.cidades = await response.json();
              },

              async saveTeam() {
                  if (!this.newTeamName.trim()) { this.teamError = 'Nome obrigatório.'; return; }
                  this.isSavingTeam = true;
                  const formData = new FormData();
                  formData.append('nome', this.newTeamName);
                  formData.append('coordenador_id', this.newTeamCoordenador);
                  formData.append('data_fundacao', this.newTeamDataFundacao);
                  formData.append('estado_id', this.newTeamEstado);
                  formData.append('cidade_id', this.newTeamCidade);
                  if (this.$refs.newTeamLogo.files[0]) formData.append('logo', this.$refs.newTeamLogo.files[0]);
                  
                  try {
                        const csrf = document.querySelector('input[name=_token]').value;
                        const response = await fetch('{{ route('equipes.store.ajax') }}', { method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}, body: formData });
                        const result = await response.json();
                        if (!response.ok) throw new Error(result.message);
                        
                        const equipeSelect = document.getElementById('equipe_id');
                        equipeSelect.add(new Option(result.nome, result.id, true, true));
                        equipeSelect.value = result.id;
                        this.showTeamModal = false;
                        this.newTeamName = ''; 
                  } catch (e) { this.teamError = 'Erro ao salvar equipe.'; }
                  this.isSavingTeam = false;
              },
              
              init() { 
                  this.checkCategoryType(); 
                  this.calculateTotal(); // Calcula ao carregar a página
              }
          }"
          @submit="isSubmitting = true">
        
        @csrf
        <input type="hidden" name="evento_id" value="{{ $evento->id }}">

        {{-- Cabeçalho do Evento --}}
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 text-center border-b-4 border-orange-500 mb-6">
            <h2 class="text-sm font-semibold text-orange-500 uppercase tracking-widest">Inscrição</h2>
            {{-- Ajuste Mobile: Texto um pouco menor em telas pequenas --}}
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mt-1">{{ $evento->nome }}</h1>
        </div>
        
        {{-- Dados do Atleta Logado --}}
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-6">
            <h3 class="flex items-center font-bold text-lg text-slate-700 mb-4"><i class="fa-solid fa-user text-orange-500 mr-3"></i> Seus Dados</h3>
            <div class="p-4 bg-slate-50 rounded-md border text-slate-600 space-y-2">
                <div class="flex justify-between"><span><strong>Nome:</strong></span> <span>{{ $atleta->user->name }}</span></div>
                <div class="flex justify-between"><span><strong>CPF:</strong></span> <span>{{ $atleta->cpf }}</span></div>
            </div>
        </div>

        {{-- SELEÇÃO DE CATEGORIA --}}
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
            @if ($inscricaoExistente)
                <div class="p-4 text-sm text-green-800 bg-green-50 text-center rounded-lg">Você já está inscrito.</div>
            @else
                <div class="space-y-8">
                    <div class="form-group">
                        <label class="flex items-center text-lg font-semibold text-gray-800"><i class="fa-solid fa-list-check text-orange-500 mr-3"></i> 1. Escolha sua Categoria</label>
                        <fieldset class="mt-4 space-y-6">
                            @forelse($percursosFiltrados as $percurso)
                                @if($percurso->categorias->isNotEmpty())
                                    <div>
                                        <h4 class="text-md font-semibold text-slate-600 mb-3 border-b pb-2">{{ $percurso->descricao }}</h4>
                                        <div class="space-y-3">
                                            @foreach($percurso->categorias as $categoria)
                                                @php 
                                                    $isDupla = Str::contains($categoria->nome, ['Dupla', 'Mista', 'Duples'], true);
                                                    
                                                    // 🟢 LÓGICA DE PREÇO BLINDADA (View Priority)
                                                    // 1. Tenta encontrar um lote ESPECÍFICO para esta categoria que esteja ativo
                                                    $loteEspecifico = $categoria->lotesInscricao->filter(function($lote) {
                                                        return now()->between($lote->data_inicio, $lote->data_fim);
                                                    })->first();

                                                    if ($loteEspecifico) {
                                                        // Se encontrou específico, usa ele (Prioridade)
                                                        $valorBase = $loteEspecifico->valor;
                                                    } else {
                                                        // Se não, usa o valor padrão do controller (Geral)
                                                        $valorBase = $categoria->valor_atual ?? 0;
                                                    }

                                                    // Cálculo visual da taxa
                                                    $valorTaxaVisual = $valorBase * ($taxaPercentual / 100);
                                                @endphp
                                                
                                                <label class="flex flex-col sm:flex-row sm:items-center p-4 border-l-4 border-orange-500 bg-white border rounded-lg cursor-pointer hover:bg-orange-50 has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50 transition-colors">
                                                    
                                                    <div class="flex items-center w-full sm:w-auto">
                                                        <input type="radio" 
                                                               name="categoria_id" 
                                                               value="{{ $categoria->id }}" 
                                                               data-valor="{{ $valorBase }}" 
                                                               data-is-dupla="{{ $isDupla ? '1' : '0' }}" 
                                                               class="h-5 w-5 text-orange-600 focus:ring-orange-500 category-radio shrink-0" required
                                                               @change="checkCategoryType">
                                                        <div class="ml-4 flex-grow">
                                                            <span class="font-medium text-gray-900 block">{{ $categoria->nome }}</span>
                                                            @if($isDupla)<span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase">Dupla</span>@endif
                                                        </div>
                                                    </div>

                                                    {{-- 🟢 EXIBIÇÃO: Valor Base + Taxa Concatenados --}}
                                                    <div class="text-right mt-2 sm:mt-0 sm:ml-auto pl-9 sm:pl-0">
                                                        <span class="text-lg font-bold text-slate-800 block">
                                                            R$ {{ number_format($valorBase, 2, ',', '.') }}
                                                        </span>
                                                        @if($valorTaxaVisual > 0)
                                                            <span class="text-xs text-slate-500 block">
                                                                (+ R$ {{ number_format($valorTaxaVisual, 2, ',', '.') }} taxa)
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-green-600 block font-bold">Taxa Grátis</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center p-6 border-dashed border-2 rounded-md"><p class="text-slate-500">Nenhuma categoria disponível.</p></div>
                            @endforelse
                        </fieldset>
                    </div>

                    {{-- SEÇÃO DE PARCEIRO --}}
                    <div x-show="categoryIsDupla" x-transition x-cloak class="bg-indigo-50 p-6 rounded-lg border border-indigo-200 mt-6 relative shadow-sm">
                        <h3 class="flex items-center font-bold text-lg text-indigo-900 mb-4">
                            <i class="fa-solid fa-user-group mr-2"></i> Selecionar Parceiro
                        </h3>
                        <div class="grid grid-cols-1 gap-6">
                            {{-- Busca --}}
                            <div x-show="!parceiroSelected">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Buscar Parceiro (Nome ou CPF)</label>
                                <div class="relative">
                                    <input type="text" x-model="searchParceiro" @input.debounce.500ms="buscarAtleta()" @keydown.enter.prevent="buscarAtleta()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 pl-4 pr-10" placeholder="Digite o nome ou CPF do atleta...">
                                    <div x-show="isSearchingParceiro" class="absolute right-3 top-3 text-orange-500"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
                                    {{-- Resultados --}}
                                    <div x-show="parceiroResults.length > 0" class="absolute z-20 w-full bg-white mt-1 rounded-lg shadow-xl border border-gray-200 max-h-60 overflow-y-auto" @click.away="parceiroResults = []">
                                        <template x-for="atleta in parceiroResults" :key="atleta.id">
                                            <div @click="selectParceiro(atleta)" class="p-3 hover:bg-indigo-50 cursor-pointer flex items-center border-b last:border-0 transition-colors">
                                                <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600 mr-3" x-text="atleta.nome.charAt(0)"></div>
                                                <div><p class="font-bold text-sm text-gray-900" x-text="atleta.nome"></p><p class="text-xs text-gray-500 font-mono" x-text="atleta.cpf"></p></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="searchError" class="mt-2 text-sm text-red-600 font-bold bg-red-50 p-2 rounded border border-red-200"><i class="fa-solid fa-triangle-exclamation mr-1"></i> <span x-text="searchError"></span></div>
                                </div>
                            </div>
                            {{-- Selecionado --}}
                            <div x-show="parceiroSelected" class="bg-white p-4 rounded-lg border-l-4 border-green-500 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                                <div class="flex items-center w-full">
                                    <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl font-bold mr-4 shrink-0"><i class="fa-solid fa-user-check"></i></div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Parceiro da Dupla</p>
                                        <p class="font-black text-gray-800 text-lg leading-tight" x-text="parceiroSelected?.nome"></p>
                                        <p class="text-sm text-gray-500 font-mono" x-text="parceiroSelected?.cpf"></p>
                                        <input type="hidden" name="parceiro_id" :value="parceiroSelected?.id">
                                    </div>
                                </div>
                                <button type="button" @click="removeParceiro()" class="w-full md:w-auto text-red-500 hover:text-red-700 text-sm font-bold border border-red-200 hover:bg-red-50 px-4 py-2 rounded-lg transition-all shrink-0"><i class="fa-solid fa-xmark mr-1"></i> Trocar</button>
                            </div>
                            {{-- Pagamento --}}
                            <div x-show="parceiroSelected" class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm" x-transition>
                                <p class="block text-sm font-bold text-gray-800 mb-3"><i class="fa-solid fa-money-bill-wave text-green-600 mr-2"></i> Como deseja realizar o pagamento?</p>
                                <div class="space-y-3">
                                    <label class="flex items-start p-3 border rounded-md cursor-pointer hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 transition-all">
                                        <input type="radio" name="tipo_pagamento_dupla" value="unico" x-model="tipoPagamentoDupla" class="mt-0.5 text-indigo-600 focus:ring-indigo-500" checked>
                                        <div class="ml-3"><span class="block text-sm font-bold text-gray-900">Pagamento Único (Eu pago pelos dois)</span><span class="block text-xs text-gray-500">Será gerado um único pagamento com o valor total das duas inscrições.</span></div>
                                    </label>
                                    <label class="flex items-start p-3 border rounded-md cursor-pointer hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 transition-all">
                                        <input type="radio" name="tipo_pagamento_dupla" value="individual" x-model="tipoPagamentoDupla" class="text-indigo-600 focus:ring-indigo-500">
                                        <div class="ml-3"><span class="block text-sm font-bold text-gray-900">Pagamento Individual</span><span class="block text-xs text-gray-500">Cada atleta paga a sua própria inscrição separadamente.</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group border-t pt-8">
                        <label for="equipe_id" class="flex items-center text-lg font-semibold text-gray-800"><i class="fa-solid fa-users text-orange-500 mr-3"></i> 2. Equipe (opcional)</label>
                        <select name="equipe_id" id="equipe_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 h-10">
                            <option value="">Nenhuma equipe (Individual)</option>
                            @foreach($equipes as $equipe) <option value="{{ $equipe->id }}" {{ old('equipe_id', $atleta->equipe_id) == $equipe->id ? 'selected' : '' }}>{{ $equipe->nome }}</option> @endforeach
                        </select>
                        <div class="mt-2 text-right"><button type="button" @click="showTeamModal = true" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold py-2">Cadastrar Equipe <i class="fa-solid fa-plus-circle ml-1 text-xs"></i></button></div>
                    </div>

                    {{-- Corrida: ritmo previsto e pelotão (opcionais) --}}
                    @if($evento->isCorrida())
                    <div class="form-group border-t pt-8 bg-amber-50/50 p-6 rounded-lg border border-amber-100">
                        <p class="flex items-center text-lg font-semibold text-gray-800 mb-4"><i class="fa-solid fa-person-running text-orange-500 mr-3"></i> Dados para Corrida (opcional)</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="ritmo_previsto" class="block text-sm font-medium text-gray-700 mb-1">Ritmo previsto (min/km)</label>
                                <input type="text" id="ritmo_previsto" name="ritmo_previsto" value="{{ old('ritmo_previsto') }}" maxlength="50" placeholder="ex: 5:30 ou 6:00" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 h-10 px-3">
                            </div>
                            <div>
                                <label for="pelotao_largada" class="block text-sm font-medium text-gray-700 mb-1">Pelotão / Onda de largada</label>
                                <input type="text" id="pelotao_largada" name="pelotao_largada" value="{{ old('pelotao_largada') }}" maxlength="50" placeholder="ex: A, B, 1 ou 2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 h-10 px-3">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- CARD ITENS OPCIONAIS --}}
        @if($produtosOpcionais->isNotEmpty() && !$inscricaoExistente)
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
            <fieldset :disabled="!categorySelected" class="transition">
                <label class="flex items-center text-lg font-semibold text-gray-800"><i class="fa-solid fa-cart-plus text-orange-500 mr-3"></i> 3. Itens Opcionais</label>
                <div class="mt-4 space-y-3">
                    @foreach($produtosOpcionais as $produto)
                        @php $isFree = isset($produto->valor_original_visual); @endphp
                        {{-- Ajuste Mobile: Flex-col no mobile para separar o título/checkbox dos controles de preço/qtd --}}
                        <div x-data="{ checked: {{ old('produtos.'.$produto->id.'.id') ? 'true' : 'false' }} }" class="product-item p-4 border rounded-lg {{ $isFree ? 'product-free' : 'border-gray-200' }}">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                                <div class="flex items-start flex-grow w-full">
                                    <input type="checkbox" id="produto_{{ $produto->id }}" name="produtos[{{ $produto->id }}][id]" value="{{ $produto->id }}" data-valor-produto="{{ $produto->valor }}" x-model="checked" class="product-checkbox h-5 w-5 rounded border-gray-300 {{ $isFree ? 'text-green-600' : 'text-orange-600' }} mt-1 shrink-0">
                                    <div class="ml-3">
                                        <label for="produto_{{ $produto->id }}" class="font-medium text-gray-900 flex flex-wrap items-center">
                                            {{ str_replace(' (OFERTA: ITEM GRATUITO)', '', $produto->nome) }}
                                            @if($isFree)<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-200 text-green-800 uppercase whitespace-nowrap"><i class="fa-solid fa-gift mr-1"></i> Brinde</span>@endif
                                        </label>
                                        @if($produto->descricao)<p class="text-sm text-gray-500">{{ $produto->descricao }}</p>@endif
                                    </div>
                                </div>

                                {{-- Controles: No mobile ficam em linha (preço esq, qtd dir), no desktop empilham a direita --}}
                                <div class="w-full md:w-auto flex flex-row md:flex-col justify-between items-center md:items-end gap-2">
                                    <div class="flex flex-col items-end">
                                        @if($isFree)
                                            <div class="text-right"><span class="block text-xs text-gray-400 line-through">R$ {{ number_format($produto->valor_original_visual, 2, ',', '.') }}</span><span class="block font-bold text-green-600 text-lg">GRÁTIS</span></div>
                                        @else
                                            <span class="font-bold text-slate-800 whitespace-nowrap">+ R$ {{ number_format($produto->valor, 2, ',', '.') }}</span>
                                        @endif
                                    </div>

                                    <div x-show="checked" style="display: none;" class="flex items-center gap-2 mt-0 md:mt-2 p-2 border rounded-md bg-white/80 shadow-sm" x-transition>
                                        <div>
                                            <label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Qtd</label>
                                            <select name="produtos[{{ $produto->id }}][quantidade]" class="product-quantity text-sm rounded-md border-gray-300 py-2 px-2 h-9 w-16">
                                                @for ($i = 1; $i <= ($produto->max_quantidade_por_inscricao ?? 5); $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        @if($produto->requer_tamanho)
                                            <div>
                                                <label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Tam</label>
                                                <select name="produtos[{{ $produto->id }}][tamanho]" class="text-sm rounded-md border-gray-300 py-2 px-2 h-9">
                                                    <option value="P">P</option><option value="M" selected>M</option><option value="G">G</option><option value="GG">GG</option>
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </fieldset>
        </div>
        @endif

        {{-- RESUMO DO PEDIDO --}}
        <div x-show="resumo.valorTotal > 0 || resumo.temProdutos" x-transition x-cloak class="bg-white rounded-lg shadow-lg p-6 mt-8 border border-slate-100 mb-20 md:mb-0">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b pb-2">Resumo do Pedido</h3>
            <div class="space-y-3 text-sm text-slate-600">
                <template x-if="resumo.valorBase > 0">
                    <div class="flex justify-between">
                        <span>Inscrição <span x-show="categoryIsDupla && tipoPagamentoDupla === 'unico'">(Dupla)</span></span>
                        <span class="font-medium" x-text="formatMoney(resumo.valorBase)"></span>
                    </div>
                </template>
                <template x-if="resumo.valorProdutos > 0">
                    <div class="flex justify-between">
                        <span>Itens Adicionais</span>
                        <span class="font-medium" x-text="formatMoney(resumo.valorProdutos)"></span>
                    </div>
                </template>
                <div class="flex justify-between text-slate-500">
                    <span>Taxa de Serviço ({{ $taxaPercentual }}%)</span>
                    <span class="font-medium" x-text="formatMoney(resumo.valorTaxa)"></span>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-200">
                <div class="flex justify-between items-center">
                    <strong class="text-base text-slate-900">Total a Pagar</strong>
                    <strong class="text-xl font-bold text-green-600" x-text="formatMoney(resumo.valorTotal)"></strong>
                </div>
            </div>
        </div>

        @if (!$inscricaoExistente)
        {{-- Ajuste Mobile: Botão w-full no mobile --}}
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 flex justify-end">
            <button type="submit" 
                    class="w-full md:w-auto inline-flex items-center justify-center px-8 py-4 bg-green-600 text-white font-bold text-lg rounded-lg shadow-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-all" 
                    :disabled="!categorySelected || isSubmitting || (categoryIsDupla && !parceiroSelected)">
                <span x-show="!isSubmitting">Confirmar e Ir para Pagamento</span>
                <span x-show="isSubmitting" class="flex items-center" style="display: none;"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processando...</span>
            </button>
        </div>
        @endif
        
        {{-- MODAL DE EQUIPE (CORRIGIDO PARA MOBILE) --}}
        <div x-show="showTeamModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4" style="display: none;" x-cloak>
             {{-- Adicionado max-h e overflow para o modal ser rolavel se o teclado cobrir a tela --}}
             <div @click.away="showTeamModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
                 <h3 class="text-lg font-bold text-gray-900 mb-4 sticky top-0 bg-white pb-2 border-b z-10">Cadastrar Equipe</h3>
                 <div class="space-y-6 pt-2">
                    <div><label for="new_team_name" class="block text-sm font-medium text-gray-700">Nome da Equipe</label><input type="text" id="new_team_name" x-model="newTeamName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 h-10 px-3"></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div><label for="data_fundacao" class="block text-sm font-medium text-gray-700">Data de Fundação</label><input id="data_fundacao" x-model="newTeamDataFundacao" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm h-10 px-3"></div><div><label for="logo" class="block text-sm font-medium text-gray-700">Logo</label><input id="logo" x-ref="newTeamLogo" type="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"/></div></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="new_team_estado_id" class="block text-sm font-medium text-gray-700">Estado</label><select id="new_team_estado_id" x-model="newTeamEstado" @change="getCidades()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm h-10 bg-white"><option value="">Selecione...</option><template x-for="estado in estados" :key="estado.id"><option :value="estado.id" x-text="estado.nome"></option></template></select></div>
                        <div><label for="new_team_cidade_id" class="block text-sm font-medium text-gray-700">Cidade</label><select id="new_team_cidade_id" x-model="newTeamCidade" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm h-10 bg-white" :disabled="!newTeamEstado || cidades.length === 0"><option value="">Selecione...</option><template x-for="cidade in cidades" :key="cidade.id"><option :value="cidade.id" x-text="cidade.nome"></option></template></select></div>
                    </div>
                </div>
                 <p x-text="teamError" class="text-red-500 text-sm mt-4"></p>
                 <div class="mt-6 flex justify-end gap-x-4 border-t pt-4 sticky bottom-0 bg-white"><button @click="showTeamModal = false" type="button" class="text-sm font-semibold text-gray-700 py-2">Cancelar</button><button @click="saveTeam()" :disabled="isSavingTeam" class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400"><span x-show="!isSavingTeam">Salvar</span><span x-show="isSavingTeam">Salvando...</span></button></div>
             </div>
        </div>

    </form>
</main>
@endsection