<x-app-layout>
    {{-- CABEÇALHO HERO MODERNIZADO --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-32 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                
                {{-- Título e Breadcrumb --}}
                <div class="text-white z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'resultados']) }}" class="inline-flex items-center text-xs font-bold text-blue-200 hover:text-white transition-colors bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm border border-white/10 hover:bg-white/20">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Evento
                        </a>
                        <span class="text-slate-400 text-xs">•</span>
                        <span class="text-xs text-blue-300 font-bold uppercase tracking-wider">Cronometragem</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md flex items-center gap-3">
                        <i class="fa-solid fa-stopwatch-20 text-blue-400"></i> Apuração de Resultados
                    </h2>
                    <p class="text-blue-100 mt-1 font-medium text-lg opacity-90">{{ $evento->nome }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="relative z-20 -mt-24 pb-12" x-data="{
        tab: 'individual',
        timeMask(event) {
            let value = event.target.value.replace(/\D/g, '');
            let output = '';
            const len = value.length;
            if (len > 0) { output += value.substring(0, 2); }
            if (len > 2) { output += ':' + value.substring(2, 4); }
            if (len > 4) { output += ':' + value.substring(4, 6); }
            if (len > 6) { output += '.' + value.substring(6, 9); }
            event.target.value = output;
        }
    }">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Card Principal --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden min-h-[600px]">
                
                {{-- Alerta Informativo --}}
                <div class="bg-blue-50 border-b border-blue-100 p-6 flex items-start gap-4">
                    <div class="bg-blue-100 p-2 rounded-full text-blue-600 shrink-0 shadow-sm">
                        <i class="fa-solid fa-circle-info text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-blue-900 font-bold text-sm uppercase tracking-wide mb-1">Instruções de Preenchimento</h4>
                        <p class="text-blue-700 text-sm leading-relaxed">
                            Insira o tempo de conclusão no formato <code class="bg-white px-1.5 py-0.5 rounded border border-blue-200 font-mono text-blue-800 font-bold">HH:MM:SS.mmm</code> (Ex: 01:45:30.500) e selecione o status.
                            <br>Os dados são salvos <strong>automaticamente</strong> ao sair do campo (auto-save).
                        </p>
                    </div>
                </div>

                {{-- Navegação das Abas --}}
                <div class="bg-slate-50 border-b border-slate-200 px-6 pt-4">
                    <nav class="flex space-x-4" aria-label="Tabs">
                        <button @click="tab = 'individual'" 
                                :class="{ 'border-orange-500 text-orange-600 bg-white shadow-sm rounded-t-lg': tab === 'individual', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-t-lg': tab !== 'individual' }" 
                                class="group relative px-6 py-3 font-bold text-sm border-t-4 transition-all duration-200 flex items-center gap-2">
                            <i class="fa-solid fa-person-running" :class="{ 'text-orange-500': tab === 'individual', 'text-slate-400': tab !== 'individual' }"></i>
                            Resultado Individual
                        </button>
                        <button @click="tab = 'equipes'" 
                                :class="{ 'border-orange-500 text-orange-600 bg-white shadow-sm rounded-t-lg': tab === 'equipes', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-t-lg': tab !== 'equipes' }" 
                                class="group relative px-6 py-3 font-bold text-sm border-t-4 transition-all duration-200 flex items-center gap-2">
                            <i class="fa-solid fa-people-group" :class="{ 'text-orange-500': tab === 'equipes', 'text-slate-400': tab !== 'equipes' }"></i>
                            Resultado por Equipas/Duplas
                        </button>
                    </nav>
                </div>
                
                {{-- ABA 1: RESULTADO INDIVIDUAL --}}
                <div x-show="tab === 'individual'" class="bg-white">
                    <form action="{{ route('organizador.eventos.resultados.store', $evento) }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Atleta / Categoria</th>
                                        <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider w-48">Tempo (HH:MM:SS.mmm)</th>
                                        <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider w-48">Status</th>
                                        <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider w-24">Posição</th>
                                        <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider w-24">Pontos</th>
                                        <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider w-32">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @php
                                        $inscricoesAgrupadas = $inscricoes->groupBy(['categoria.percurso.descricao', 'categoria.genero', 'categoria.nome']);
                                    @endphp
                                    @forelse($inscricoesAgrupadas as $nomePercurso => $generos)
                                        {{-- Cabeçalho do Percurso --}}
                                        <tr class="bg-slate-800 text-white">
                                            <td colspan="6" class="px-6 py-2 text-left text-sm font-bold flex items-center gap-2">
                                                <i class="fa-solid fa-route text-orange-400"></i> {{ $nomePercurso ?: 'N/A' }}
                                            </td>
                                        </tr>
                                        
                                        @foreach($generos as $nomeGenero => $categorias)
                                            @foreach($categorias as $nomeCategoria => $inscritosNaCategoria)
                                                {{-- Cabeçalho da Categoria --}}
                                                <tr class="bg-indigo-50 border-y border-indigo-100">
                                                    <td colspan="6" class="px-6 py-2 text-left text-sm font-bold text-indigo-800 pl-10 flex items-center gap-2">
                                                        <i class="fa-solid fa-layer-group text-indigo-500"></i> Categoria: {{ $nomeCategoria }}
                                                        <span class="text-xs font-normal text-indigo-500 bg-white px-2 py-0.5 rounded-full border border-indigo-200 ml-2">{{ $inscritosNaCategoria->count() }} atletas</span>
                                                    </td>
                                                </tr>

                                                @foreach($inscritosNaCategoria->sortBy('resultado.posicao_categoria') as $inscricao)
                                                    <tr class="hover:bg-slate-50 transition-colors group"
                                                        x-data="{ 
                                                            saving: false, 
                                                            saved: false, 
                                                            error: '', 
                                                            autoSave() { 
                                                                this.saving = true; 
                                                                this.saved = false; 
                                                                this.error = ''; 
                                                                const tempoInput = this.$refs.tempoInput; 
                                                                const statusInput = this.$refs.statusInput; 
                                                                
                                                                // Se preencher tempo, muda status para completou automaticamente
                                                                if (tempoInput.value && statusInput.value === 'nao_iniciada') { 
                                                                    statusInput.value = 'completou'; 
                                                                } 
                                                                
                                                                fetch('{{ route('organizador.eventos.resultados.updateSingle', $inscricao) }}', { 
                                                                    method: 'PATCH', 
                                                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, 
                                                                    body: JSON.stringify({ tempo_conclusao: tempoInput.value, status_corrida: statusInput.value }) 
                                                                }).then(res => res.json().then(data => ({ ok: res.ok, data })))
                                                                  .then(({ ok, data }) => { 
                                                                    if (!ok) { throw new Error(data.message || 'Erro ao salvar.'); } 
                                                                    this.saving = false; 
                                                                    this.saved = true; 
                                                                    setTimeout(() => this.saved = false, 2500); 
                                                                }).catch(err => { 
                                                                    this.saving = false; 
                                                                    this.error = err.message; 
                                                                }); 
                                                            } 
                                                        }">
                                                        
                                                        <td class="pl-10 pr-6 py-3 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-xs font-bold mr-3">
                                                                    {{ $inscricao->atleta->iniciais }}
                                                                </div>
                                                                <div>
                                                                    <div class="text-sm font-bold text-slate-800">{{ $inscricao->atleta->user->name }}</div>
                                                                    <div class="text-xs text-slate-500 font-mono">Num. Atleta: {{ $inscricao->numero_atleta ?? 'S/N' }}</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        
                                                        <td class="px-6 py-3">
                                                            <input type="text" x-ref="tempoInput" 
                                                                   name="resultados[{{ $inscricao->id }}][tempo_conclusao]" 
                                                                   value="{{ $inscricao->resultado?->tempo_formatado }}" 
                                                                   @input="timeMask($event)" 
                                                                   @keydown.enter.prevent="autoSave" 
                                                                   @blur="autoSave" 
                                                                   maxlength="12" 
                                                                   placeholder="00:00:00.000" 
                                                                   class="w-full rounded-lg border-slate-300 text-sm font-mono font-bold text-slate-700 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                                                        </td>
                                                        
                                                        <td class="px-6 py-3">
                                                            <select x-ref="statusInput" name="resultados[{{ $inscricao->id }}][status_corrida]" @change="autoSave" 
                                                                    class="w-full rounded-lg border-slate-300 text-sm focus:ring-orange-500 focus:border-orange-500">
                                                                <option value="nao_iniciada" @selected($inscricao->resultado?->status_corrida == 'nao_iniciada' || is_null($inscricao->resultado))>Não Iniciada</option>
                                                                <option value="completou" @selected($inscricao->resultado?->status_corrida == 'completou')>Completou</option>
                                                                <option value="nao_completou" @selected($inscricao->resultado?->status_corrida == 'nao_completou')>Não Completou</option>
                                                                <option value="desqualificado" @selected($inscricao->resultado?->status_corrida == 'desqualificado')>Desqualificado</option>
                                                            </select>
                                                        </td>
                                                        
                                                        <td class="px-6 py-3 text-center">
                                                            @if($inscricao->resultado?->posicao_categoria)
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-800 text-white text-sm font-bold shadow-sm">
                                                                    {{ $inscricao->resultado->posicao_categoria }}º
                                                                </span>
                                                            @else
                                                                <span class="text-slate-300">-</span>
                                                            @endif
                                                        </td>
                                                        
                                                        <td class="px-6 py-3 text-center">
                                                            @if($inscricao->resultado?->pontos_etapa)
                                                                <span class="font-bold text-blue-600">{{ $inscricao->resultado->pontos_etapa }}</span>
                                                            @else
                                                                <span class="text-slate-300">-</span>
                                                            @endif
                                                        </td>
                                                        
                                                        <td class="px-6 py-3 text-center">
                                                            <span x-show="saving" class="inline-flex items-center text-xs text-orange-500 font-bold bg-orange-50 px-2 py-1 rounded-full">
                                                                <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Salvando
                                                            </span>
                                                            <span x-show="saved" class="inline-flex items-center text-xs text-green-600 font-bold bg-green-50 px-2 py-1 rounded-full border border-green-200">
                                                                <i class="fa-solid fa-check mr-1"></i> Salvo
                                                            </span>
                                                            <span x-show="error" class="text-red-500 font-bold text-xs" x-text="error"></span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-16">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                                        <i class="fa-solid fa-user-slash text-2xl"></i>
                                                    </div>
                                                    <p class="text-slate-500 font-medium">Nenhum inscrito confirmado para este evento.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Footer Fixo de Ação --}}
                        <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-between items-center sticky bottom-0 z-10 shadow-inner">
                            <p class="text-xs text-slate-500 italic hidden sm:block">
                                <i class="fa-solid fa-lightbulb text-yellow-500 mr-1"></i> 
                                Dica: As alterações são salvas automaticamente linha a linha.
                            </p>
                            <x-primary-button class="shadow-lg shadow-indigo-500/30">
                                <i class="fa-solid fa-calculator mr-2"></i> Apurar Classificação Final
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                {{-- ABA 2: RESULTADO POR EQUIPAS (COM ATLETAS NA MESMA LINHA) --}}
                <div x-show="tab === 'equipes'" style="display: none;" class="bg-white">
                    <div class="p-8">
                        @if ($rankingEquipesEtapa->isEmpty())
                             <div class="text-center py-16 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50">
                                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm text-slate-300">
                                    <i class="fa-solid fa-users-slash text-4xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700">Resultado de Equipas Indisponível</h3>
                                <p class="text-slate-500 mt-1 max-w-md mx-auto">Nenhuma equipa pontuou nesta etapa ainda. Certifique-se de apurar a classificação individual primeiro.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider w-20">Pos.</th>
                                            <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider w-1/4">Equipa</th>
                                            {{-- ADICIONADA COLUNA DE INTEGRANTES --}}
                                            <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Integrantes (Atletas)</th>
                                            <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider w-32">Total Pontos</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        @foreach ($rankingEquipesEtapa as $dadosEquipe)
                                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                                <td class="px-6 py-4 text-center">
                                                    @if($loop->iteration <= 3)
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold shadow-md
                                                            {{ $loop->iteration == 1 ? 'bg-yellow-400' : ($loop->iteration == 2 ? 'bg-slate-400' : 'bg-orange-400') }}">
                                                            {{ $loop->iteration }}º
                                                        </span>
                                                    @else
                                                        <span class="text-sm font-bold text-slate-600">{{ $loop->iteration }}º</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-bold text-slate-800">{{ $dadosEquipe['equipe']->nome }}</div>
                                                    @if($dadosEquipe['equipe']->cidade)
                                                        <div class="text-xs text-slate-400">{{ $dadosEquipe['equipe']->cidade }}</div>
                                                    @endif
                                                </td>
                                                {{-- COLUNA DE INTEGRANTES --}}
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        @php
                                                            // Tenta buscar os atletas da equipa que participaram desta etapa
                                                            $atletasEquipe = $dadosEquipe['equipe']->users ?? collect([]);
                                                        @endphp
                                                        
                                                        @if($atletasEquipe->isNotEmpty())
                                                            @foreach($atletasEquipe as $atleta)
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                                    <i class="fa-solid fa-user mr-1.5 text-slate-400"></i>
                                                                    {{ explode(' ', $atleta->name)[0] }} {{-- Primeiro nome --}}
                                                                </span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-xs text-slate-400 italic">Integrantes não listados</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-block px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-bold shadow-sm">
                                                        {{ $dadosEquipe['pontos_totais'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>