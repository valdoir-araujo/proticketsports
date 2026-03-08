<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Inscrição
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">

                    {{-- Cabeçalho do Formulário --}}
                    <header class="text-center border-b pb-6">
                        <h2 class="text-2xl font-bold text-slate-800">Alterar Inscrição</h2>
                        <p class="text-md text-gray-600 mt-2">Evento: {{ $inscricao->evento->nome }}</p>
                    </header>

                    <div class="mt-6">
                        {{-- Exibição de Erros de Validação --}}
                        @if ($errors->any())
                            <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                                <span class="font-medium">Ocorreram alguns erros:</span>
                                <ul class="mt-1.5 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Aviso Importante --}}
                        <div class="mb-6 p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50" role="alert">
                            <i class="fa-solid fa-circle-exclamation mr-2"></i>
                            <span class="font-medium">Atenção:</span> Ao alterar a categoria, o valor da sua inscrição será atualizado para o preço do lote atual da nova categoria, e qualquer cupom de desconto será removido.
                        </div>

                        {{-- Formulário de Edição com Lógica Alpine.js --}}
                        <form method="POST" action="{{ route('inscricao.update', $inscricao) }}" class="space-y-6"
                              x-data="{
                                  percursos: {{ $percursosFiltrados->values()->toJson() }},
                                  percursoSelecionado: '{{ old('percurso_id', $inscricao->categoria->percurso_id) }}',
                                  categoriaSelecionada: '',
                                  categoriasDoPercurso: [],
                                  
                                  // --- NOVAS VARIÁVEIS PARA O MODAL DE EQUIPE ---
                                  showTeamModal: false,
                                  newTeamName: '',
                                  teamFormError: '',
                                  teamFormSuccess: '',
                                  isSavingTeam: false,

                                  updateCategorias() {
                                      if (!this.percursoSelecionado) {
                                          this.categoriasDoPercurso = [];
                                          return;
                                      }
                                      const percursoEncontrado = this.percursos.find(p => p.id == this.percursoSelecionado);
                                      this.categoriasDoPercurso = percursoEncontrado ? percursoEncontrado.categorias : [];
                                  },
                                  
                                  // --- NOVA FUNÇÃO PARA SALVAR A EQUIPE ---
                                  async saveNewTeam() {
                                      this.isSavingTeam = true;
                                      this.teamFormError = '';
                                      this.teamFormSuccess = '';

                                      if (!this.newTeamName.trim()) {
                                          this.teamFormError = 'O nome da equipe não pode ser vazio.';
                                          this.isSavingTeam = false;
                                          return;
                                      }

                                      try {
                                          const response = await fetch('{{ route('equipes.store.ajax') }}', {
                                              method: 'POST',
                                              headers: {
                                                  'Content-Type': 'application/json',
                                                  'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                                              },
                                              body: JSON.stringify({ nome: this.newTeamName })
                                          });

                                          const data = await response.json();

                                          if (!response.ok) {
                                              throw new Error(data.message || 'Ocorreu um erro.');
                                          }
                                          
                                          // Adiciona a nova equipe ao select
                                          const teamSelect = this.$refs.teamSelect;
                                          const newOption = new Option(data.nome, data.id, true, true);
                                          teamSelect.add(newOption);
                                          teamSelect.dispatchEvent(new Event('change')); // Notifica o Alpine sobre a mudança

                                          this.teamFormSuccess = 'Equipe cadastrada com sucesso!';
                                          this.newTeamName = ''; // Limpa o campo
                                          
                                          // Fecha o modal após um breve delay para mostrar a msg de sucesso
                                          setTimeout(() => { this.showTeamModal = false; this.teamFormSuccess = '' }, 1500);

                                      } catch (error) {
                                          this.teamFormError = error.message;
                                      } finally {
                                          this.isSavingTeam = false;
                                      }
                                  }
                              }"
                              x-init="() => {
                                  if (percursoSelecionado) {
                                      updateCategorias();
                                      $nextTick(() => {
                                          categoriaSelecionada = '{{ old('categoria_id', $inscricao->categoria_id) }}';
                                      });
                                  }
                              }">
                            @csrf
                            @method('PATCH')

                            {{-- Seletor de Percurso --}}
                            <div>
                                <label for="percurso_id" class="block text-sm font-medium text-gray-700">Alterar Percurso</label>
                                <select id="percurso_id" x-model="percursoSelecionado" @change="categoriaSelecionada = ''; updateCategorias()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Selecione um percurso --</option>
                                    @foreach($percursosFiltrados as $percurso)
                                        <option value="{{ $percurso->id }}" @selected(old('percurso_id', $inscricao->categoria->percurso_id) == $percurso->id)>
                                            {{ $percurso->descricao }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Seletor de Categoria --}}
                            <div>
                                <label for="categoria_id" class="block text-sm font-medium text-gray-700">Alterar Categoria</label>
                                <select name="categoria_id" id="categoria_id" x-model="categoriaSelecionada" :disabled="!percursoSelecionado || categoriasDoPercurso.length === 0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Selecione uma categoria --</option>
                                    <template x-for="categoria in categoriasDoPercurso" :key="categoria.id">
                                        <option :value="categoria.id" x-text="categoria.nome"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Seletor de Equipe com Botão de Cadastro --}}
                            <div>
                                <label for="equipe_id" class="block text-sm font-medium text-gray-700">Alterar Equipe</label>
                                <div class="flex items-center gap-x-2 mt-1">
                                    <select name="equipe_id" id="equipe_id" x-ref="teamSelect" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Nenhuma equipe (Individual)</option>
                                        @foreach($equipes as $equipe)
                                            <option value="{{ $equipe->id }}" @selected(old('equipe_id', $inscricao->equipe_id) == $equipe->id)>
                                                {{ $equipe->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button @click.prevent="showTeamModal = true" type="button" class="whitespace-nowrap rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                                        + Nova Equipe
                                    </button>
                                </div>
                            </div>

                            {{-- Botões de Ação do Formulário Principal --}}
                            <div class="border-t pt-6 flex items-center justify-end gap-x-4">
                                <a href="{{ route('atleta.inscricoes') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancelar</a>
                                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Salvar Alterações
                                </button>
                            </div>

                            {{-- ============================================= --}}
                            {{-- MODAL DE CADASTRO RÁPIDO DE EQUIPE           --}}
                            {{-- ============================================= --}}
                            <div x-show="showTeamModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="showTeamModal = false" x-cloak>
                                <div @click.away="showTeamModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                                    <h3 class="text-lg font-bold text-gray-900">Cadastrar Nova Equipe</h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="new_team_name" class="block text-sm font-medium text-gray-700">Nome da Equipe</label>
                                            <input type="text" id="new_team_name" x-model="newTeamName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Digite o nome da nova equipe">
                                        </div>
                                        
                                        {{-- Exibição de Erro/Sucesso --}}
                                        <div x-show="teamFormError" x-text="teamFormError" class="text-sm text-red-600"></div>
                                        <div x-show="teamFormSuccess" x-text="teamFormSuccess" class="text-sm text-green-600"></div>

                                    </div>
                                    <div class="mt-6 flex justify-end gap-x-4">
                                        <button @click.prevent="showTeamModal = false" type="button" class="text-sm font-semibold text-gray-700">Cancelar</button>
                                        <button @click.prevent="saveNewTeam()" type="button" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500" :disabled="isSavingTeam">
                                            <span x-show="!isSavingTeam">Salvar Equipe</span>
                                            <span x-show="isSavingTeam">Salvando...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
