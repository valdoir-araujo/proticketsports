{{-- Conteúdo da Aba "Percursos" com layout de duas colunas e modal de edição --}}
<div x-data="{
    showEditModal: false,
    editingPercurso: {},
    openEditModal(percurso) {
        // Clonamos o objeto para o formulário do modal
        this.editingPercurso = JSON.parse(JSON.stringify(percurso));
        this.showEditModal = true;
    }
}">
    @if (session('erro'))
        <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border-l-4 border-red-500 rounded-r-md flex items-center">
            <i class="fa-solid fa-circle-exclamation mr-3 text-lg"></i>
            {{ session('erro') }}
        </div>
    @endif

    @if($evento->isCorrida() && $percursos->isEmpty())
        <div class="mb-6 p-6 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-amber-900 flex items-center"><i class="fa-solid fa-person-running text-amber-600 mr-2"></i> Evento Corrida</h3>
                    <p class="text-sm text-amber-800 mt-1">Crie os percursos 5K, 10K e 21K de uma vez. Depois configure categorias e lotes em cada percurso.</p>
                </div>
                <form method="POST" action="{{ route('organizador.percursos.modelo-corrida', $evento) }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg shadow transition-all">
                        <i class="fa-solid fa-plus-circle mr-2"></i> Criar percursos 5K, 10K e 21K
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        {{-- Coluna da Esquerda: Lista de Percursos --}}
        <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Percursos do Evento</h3>
            <div class="space-y-4">
                @forelse($percursos as $percurso)
                    <div class="p-4 border rounded-md hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-gray-800">{{ $percurso->descricao }}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{-- Formatando para exibir com vírgula e 3 casas decimais se necessário --}}
                                    <span class="mr-4"><i class="fa-solid fa-route fa-fw mr-1 text-blue-500"></i>{{ number_format($percurso->distancia_km, 3, ',', '.') }} km</span>
                                    <span><i class="fa-solid fa-stopwatch fa-fw mr-1 text-red-500"></i>Largada: {{ \Carbon\Carbon::parse($percurso->horario_largada)->format('H:i') }}</span>
                                </p>
                            </div>
                            <div class="flex-shrink-0 flex items-center space-x-4">
                                {{-- NOVOS BOTÕES DE AÇÃO --}}
                                <button @click.prevent="openEditModal({{ Js::from($percurso) }})" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">Editar</button>
                                <form action="{{ route('organizador.percursos.destroy', [$evento, $percurso]) }}" method="POST" onsubmit="return confirm('Tem a certeza que deseja remover este percurso? Todas as categorias e lotes associados serão perdidos.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold">Remover</button>
                                </form>
                                <a href="{{ route('organizador.categorias.index', $percurso) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-semibold">
                                    Categorias <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-6">Nenhum percurso cadastrado para este evento ainda.</p>
                @endforelse
            </div>
        </div>

        {{-- Coluna da Direita: Formulário para Adicionar Novo Percurso (REFATORADO) --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Percurso ao Evento</h3>
            <form method="POST" action="{{ route('organizador.percursos.store', $evento) }}" class="space-y-6">
                @csrf
                
                {{-- CAMPO DE SELEÇÃO DO MODELO DE PERCURSO --}}
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <x-input-label for="percurso_modelo_id" value="Modelo de Percurso" />
                        <a href="{{ route('organizador.modelos-percurso.index') }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                            <i class="fa-solid fa-plus-circle mr-1"></i>
                            Cadastrar Novo Percurso
                        </a>
                    </div>
                    <select id="percurso_modelo_id" name="percurso_modelo_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="" disabled selected>Selecione um modelo...</option>
                        @foreach($percursoModelos as $modelo)
                            <option value="{{ $modelo->id }}" {{ old('percurso_modelo_id') == $modelo->id ? 'selected' : '' }}>
                                {{ $modelo->descricao }} ({{ $modelo->codigo }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('percurso_modelo_id')" class="mt-2" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="distancia_km" value="Distância (km)" />
                        {{-- ALTERAÇÃO: step="0.001" permite 3 casas decimais (metros) --}}
                        <x-text-input id="distancia_km" name="distancia_km" type="number" step="0.001" min="0" class="mt-1 block w-full" :value="old('distancia_km')" required placeholder="Ex: 0.150 ou 10.5" />
                        <p class="text-[10px] text-gray-500 mt-1">Use ponto para decimais. Ex: 0.150 (150m)</p>
                    </div>
                    <div>
                        <x-input-label for="altimetria_metros" value="Altimetria (m)" />
                        <x-text-input id="altimetria_metros" name="altimetria_metros" type="number" class="mt-1 block w-full" :value="old('altimetria_metros')" required />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="horario_alinhamento" value="Horário Alinhamento" />
                        <x-text-input id="horario_alinhamento" name="horario_alinhamento" type="time" class="mt-1 block w-full" :value="old('horario_alinhamento')" required />
                    </div>
                    <div>
                        <x-input-label for="horario_largada" value="Horário Largada" />
                        <x-text-input id="horario_largada" name="horario_largada" type="time" class="mt-1 block w-full" :value="old('horario_largada')" required />
                    </div>
                </div>
                <div>
                    <x-input-label for="strava_route_url" value="URL da Rota no Strava (Opcional)" />
                    <x-text-input id="strava_route_url" name="strava_route_url" type="url" class="mt-1 block w-full" :value="old('strava_route_url')" placeholder="https://www.strava.com/routes/..."/>
                </div>
                <div class="border-t pt-4">
                    <x-primary-button>Adicionar Percurso ao Evento</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE EDIÇÃO DE PERCURSO --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="showEditModal = false" x-cloak>
        <div @click.away="showEditModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Percurso</h3>
            {{-- Ação do formulário agora usa a rota nomeada --}}
            <form x-show="editingPercurso.id" 
                  :action="'{{ route('organizador.percursos.update', [$evento, 'PERCURSO_ID_PLACEHOLDER']) }}'.replace('PERCURSO_ID_PLACEHOLDER', editingPercurso.id)" 
                  method="POST" class="space-y-6">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="edit_p_descricao" value="Descrição" />
                    <x-text-input id="edit_p_descricao" name="descricao" type="text" class="mt-1 block w-full" required x-model="editingPercurso.descricao" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="edit_distancia_km" value="Distância (km)" />
                        {{-- ALTERAÇÃO: step="0.001" aqui também para permitir a edição com precisão --}}
                        <x-text-input id="edit_distancia_km" name="distancia_km" type="number" step="0.001" min="0" class="mt-1 block w-full" required x-model="editingPercurso.distancia_km" />
                        <p class="text-[10px] text-gray-500 mt-1">Ex: 0.150 para 150 metros</p>
                    </div>
                    <div>
                        <x-input-label for="edit_altimetria_metros" value="Altimetria (m)" />
                        <x-text-input id="edit_altimetria_metros" name="altimetria_metros" type="number" class="mt-1 block w-full" required x-model="editingPercurso.altimetria_metros" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="edit_horario_alinhamento" value="Horário Alinhamento" />
                        <x-text-input id="edit_horario_alinhamento" name="horario_alinhamento" type="time" class="mt-1 block w-full" required x-model="editingPercurso.horario_alinhamento" />
                    </div>
                    <div>
                        <x-input-label for="edit_horario_largada" value="Horário Largada" />
                        <x-text-input id="edit_horario_largada" name="horario_largada" type="time" class="mt-1 block w-full" required x-model="editingPercurso.horario_largada" />
                    </div>
                </div>
                <div>
                    <x-input-label for="edit_strava_route_url" value="URL da Rota no Strava (Opcional)" />
                    <x-text-input id="edit_strava_route_url" name="strava_route_url" type="url" class="mt-1 block w-full" placeholder="https://www.strava.com/routes/..." x-model="editingPercurso.strava_route_url"/>
                </div>
                <div class="mt-6 flex justify-end gap-x-4 border-t pt-4">
                    <button @click.prevent="showEditModal = false" type="button" class="text-sm font-semibold text-gray-700">Cancelar</button>
                    <x-primary-button>Salvar Alterações</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>