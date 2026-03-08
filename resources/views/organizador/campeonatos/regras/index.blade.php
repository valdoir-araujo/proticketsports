<x-app-layout>
    {{-- Hero no padrão do site --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-24 overflow-hidden shadow-xl">
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-600/20 blur-3xl pointer-events-none mix-blend-screen"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="text-white z-10">
                    <div class="flex items-center gap-2 mb-2 text-blue-200 text-sm font-medium">
                        <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="hover:text-white transition-colors">Campeonato</a>
                        <i class="fa-solid fa-chevron-right text-xs opacity-50"></i>
                        <span class="text-white">Regras de Pontuação</span>
                    </div>
                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold backdrop-blur-md">
                        <i class="fa-solid fa-list-ol text-orange-400"></i>
                        {{ $campeonato->nome }}
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Regras de Pontuação
                    </h1>
                    <p class="text-blue-100 mt-2 text-lg">Defina a pontuação para cada posição no campeonato.</p>
                </div>

                <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl font-bold text-sm text-white backdrop-blur-md transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Voltar ao Campeonato
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-12 relative z-10">
        @if (session('sucesso'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
                {{ session('sucesso') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden" x-data="{
            tab: 'geral',
            regrasGerais: {{ Js::from($regrasGerais->values()->isEmpty() ? [['posicao' => 1, 'pontos' => '']] : $regrasGerais->values()) }},
            regrasPorPercurso: {{ Js::from($regrasPorPercurso) }},
            regrasPorCategoria: {{ Js::from($regrasPorCategoria) }},
            addRegra(tipo, id = null) {
                if (tipo === 'geral') {
                    const nextPosicao = this.regrasGerais.length > 0 ? Math.max(...this.regrasGerais.map(r => Number(r.posicao) || 0)) + 1 : 1;
                    this.regrasGerais.push({ posicao: nextPosicao, pontos: '' });
                } else if (tipo === 'percurso') {
                    if (!this.regrasPorPercurso[id]) this.regrasPorPercurso[id] = [];
                    const currentRules = this.regrasPorPercurso[id];
                    const nextPosicao = currentRules.length > 0 ? Math.max(...currentRules.map(r => Number(r.posicao) || 0)) + 1 : 1;
                    this.regrasPorPercurso[id].push({ posicao: nextPosicao, pontos: '' });
                } else if (tipo === 'categoria') {
                    if (!this.regrasPorCategoria[id]) this.regrasPorCategoria[id] = [];
                    const currentRules = this.regrasPorCategoria[id];
                    const nextPosicao = currentRules.length > 0 ? Math.max(...currentRules.map(r => Number(r.posicao) || 0)) + 1 : 1;
                    this.regrasPorCategoria[id].push({ posicao: nextPosicao, pontos: '' });
                }
            },
            removeRegra(tipo, id, index) {
                if (tipo === 'geral') this.regrasGerais.splice(index, 1);
                else if (tipo === 'percurso') this.regrasPorPercurso[id].splice(index, 1);
                else if (tipo === 'categoria') this.regrasPorCategoria[id].splice(index, 1);
            }
        }">

            {{-- Abas --}}
            <div class="flex p-1 bg-slate-100 border-b border-slate-100">
                <button type="button" @click="tab = 'geral'" :class="tab === 'geral' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-800'" class="flex-1 py-3 rounded-lg text-sm font-bold transition-all">
                    Regras Gerais
                </button>
                <button type="button" @click="tab = 'percurso'" :class="tab === 'percurso' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-800'" class="flex-1 py-3 rounded-lg text-sm font-bold transition-all">
                    Por Percurso
                </button>
                <button type="button" @click="tab = 'categoria'" :class="tab === 'categoria' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-800'" class="flex-1 py-3 rounded-lg text-sm font-bold transition-all">
                    Por Categoria
                </button>
            </div>

            <form action="{{ route('organizador.campeonatos.regras.store', $campeonato) }}" method="POST" class="p-6 md:p-8">
                @csrf

                {{-- Aba Regras Gerais --}}
                <div x-show="tab === 'geral'" class="space-y-4">
                    <p class="text-slate-600 text-sm">Estas são as regras padrão para todas as etapas, a menos que uma regra mais específica seja definida.</p>
                    <div class="space-y-3">
                        <template x-for="(regra, index) in regrasGerais" :key="index">
                            <div class="flex items-center gap-3">
                                <input type="number" :name="`regras_gerais[${index}][posicao]`" x-model="regra.posicao" placeholder="Posição" min="1" class="w-24 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 text-sm">
                                <input type="number" :name="`regras_gerais[${index}][pontos]`" x-model="regra.pontos" placeholder="Pontos" class="w-28 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 text-sm">
                                <button type="button" @click.prevent="removeRegra('geral', null, index)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Remover">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click.prevent="addRegra('geral')" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800">
                        <i class="fa-solid fa-plus"></i> Adicionar Regra Geral
                    </button>
                </div>

                {{-- Aba Por Percurso --}}
                <div x-show="tab === 'percurso'" style="display: none;" class="space-y-6">
                    <p class="text-slate-600 text-sm">Defina pontuações específicas para um percurso. Estas regras sobrepõem-se às regras gerais.</p>
                    @foreach($campeonato->eventos as $evento)
                        @foreach($evento->percursos as $percurso)
                            <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50">
                                <h4 class="font-bold text-slate-800 mb-3">{{ $evento->nome }} / {{ $percurso->descricao }}</h4>
                                <div class="space-y-3">
                                    <template x-for="(regra, index) in (regrasPorPercurso[{{ $percurso->id }}] || [])" :key="index">
                                        <div class="flex items-center gap-3">
                                            <input type="number" :name="`regras_percurso[{{ $percurso->id }}][${index}][posicao]`" x-model="regra.posicao" placeholder="Posição" min="1" class="w-24 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <input type="number" :name="`regras_percurso[{{ $percurso->id }}][${index}][pontos]`" x-model="regra.pontos" placeholder="Pontos" class="w-28 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <button type="button" @click.prevent="removeRegra('percurso', {{ $percurso->id }}, index)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"><i class="fa-solid fa-trash-can text-sm"></i></button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click.prevent="addRegra('percurso', {{ $percurso->id }})" class="mt-3 text-sm font-bold text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-plus mr-1"></i> Adicionar Regra</button>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                {{-- Aba Por Categoria --}}
                <div x-show="tab === 'categoria'" style="display: none;" class="space-y-6">
                    <p class="text-slate-600 text-sm">Defina pontuações para categorias individuais. Esta é a regra mais específica e sobrepõe-se a todas as outras.</p>
                    @foreach($campeonato->eventos as $evento)
                        @foreach($evento->percursos as $percurso)
                            @foreach($percurso->categorias as $categoria)
                                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50">
                                    <h4 class="font-bold text-slate-800 mb-3">{{ $percurso->descricao }} / {{ $categoria->nome }}</h4>
                                    <div class="space-y-3">
                                        <template x-for="(regra, index) in (regrasPorCategoria[{{ $categoria->id }}] || [])" :key="index">
                                            <div class="flex items-center gap-3">
                                                <input type="number" :name="`regras_categoria[{{ $categoria->id }}][${index}][posicao]`" x-model="regra.posicao" placeholder="Posição" min="1" class="w-24 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <input type="number" :name="`regras_categoria[{{ $categoria->id }}][${index}][pontos]`" x-model="regra.pontos" placeholder="Pontos" class="w-28 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <button type="button" @click.prevent="removeRegra('categoria', {{ $categoria->id }}, index)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"><i class="fa-solid fa-trash-can text-sm"></i></button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click.prevent="addRegra('categoria', {{ $categoria->id }})" class="mt-3 text-sm font-bold text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-plus mr-1"></i> Adicionar Regra</button>
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>

                <div class="border-t border-slate-100 mt-8 pt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all">
                        <i class="fa-solid fa-check"></i> Salvar Todas as Regras
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
