<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Regras de Pontuação: {{ $campeonato->nome }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Defina a pontuação para cada posição no campeonato.</p>
            </div>
            <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar ao Campeonato
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('sucesso'))
                <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50">
                    {{ session('sucesso') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg" x-data="{ 
                tab: 'geral',
                
                // Inicializa as regras a partir dos dados do controlador
                regrasGerais: {{ Js::from($regrasGerais->values()->isEmpty() ? [['posicao' => 1, 'pontos' => '']] : $regrasGerais->values()) }},
                regrasPorPercurso: {{ Js::from($regrasPorPercurso) }},
                regrasPorCategoria: {{ Js::from($regrasPorCategoria) }},

                // Funções para adicionar e remover regras dinamicamente
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
                    if (tipo === 'geral') {
                        this.regrasGerais.splice(index, 1);
                    } else if (tipo === 'percurso') {
                        this.regrasPorPercurso[id].splice(index, 1);
                    } else if (tipo === 'categoria') {
                        this.regrasPorCategoria[id].splice(index, 1);
                    }
                }
            }">
                
                {{-- Navegação em Abas --}}
                <div class="border-b px-6">
                    <nav class="-mb-px flex space-x-6">
                        <button @click="tab = 'geral'" :class="{'border-orange-500 text-orange-600': tab === 'geral', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'geral'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Regras Gerais</button>
                        <button @click="tab = 'percurso'" :class="{'border-orange-500 text-orange-600': tab === 'percurso', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'percurso'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Por Percurso</button>
                        <button @click="tab = 'categoria'" :class="{'border-orange-500 text-orange-600': tab === 'categoria', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'categoria'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Por Categoria</button>
                    </nav>
                </div>
                
                <form action="{{ route('organizador.campeonatos.regras.store', $campeonato) }}" method="POST" class="p-6">
                    @csrf

                    {{-- Aba de Regras Gerais --}}
                    <div x-show="tab === 'geral'">
                        <p class="text-sm text-gray-600 mb-4">Estas são as regras padrão para todas as etapas, a menos que uma regra mais específica seja definida.</p>
                        <div class="space-y-2">
                            <template x-for="(regra, index) in regrasGerais" :key="index">
                                <div class="flex items-center space-x-2">
                                    <input type="number" :name="`regras_gerais[${index}][posicao]`" x-model="regra.posicao" placeholder="Posição" class="w-1/3 rounded-md border-gray-300 text-sm">
                                    <input type="number" :name="`regras_gerais[${index}][pontos]`" x-model="regra.pontos" placeholder="Pontos" class="w-1/3 rounded-md border-gray-300 text-sm">
                                    {{-- BOTÃO DE REMOVER --}}
                                    <button @click.prevent="removeRegra('geral', null, index)" class="text-red-500 hover:text-red-700 p-2 rounded-md transition duration-150 ease-in-out">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button @click.prevent="addRegra('geral')" class="mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Adicionar Regra Geral</button>
                    </div>

                    {{-- Aba de Regras por Percurso --}}
                    <div x-show="tab === 'percurso'" style="display: none;">
                        <p class="text-sm text-gray-600 mb-4">Defina pontuações específicas para um percurso. Estas regras sobrepõem-se às regras gerais.</p>
                        <div class="space-y-6">
                            @foreach($campeonato->eventos as $evento)
                                @foreach($evento->percursos as $percurso)
                                    <div class="p-4 border rounded-md">
                                        <h4 class="font-bold text-gray-800">{{$evento->nome}} / {{ $percurso->descricao }}</h4>
                                        <div class="space-y-2 mt-2">
                                            <template x-for="(regra, index) in (regrasPorPercurso[{{$percurso->id}}] || [])" :key="index">
                                                <div class="flex items-center space-x-2">
                                                    <input type="number" :name="`regras_percurso[{{$percurso->id}}][${index}][posicao]`" x-model="regra.posicao" placeholder="Posição" class="w-1/3 rounded-md border-gray-300 text-sm">
                                                    <input type="number" :name="`regras_percurso[{{$percurso->id}}][${index}][pontos]`" x-model="regra.pontos" placeholder="Pontos" class="w-1/3 rounded-md border-gray-300 text-sm">
                                                    {{-- BOTÃO DE REMOVER --}}
                                                    <button @click.prevent="removeRegra('percurso', {{$percurso->id}}, index)" class="text-red-500 hover:text-red-700 p-2 rounded-md transition duration-150 ease-in-out">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                         <button @click.prevent="addRegra('percurso', {{$percurso->id}})" class="mt-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Adicionar Regra</button>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    {{-- Aba de Regras por Categoria --}}
                    <div x-show="tab === 'categoria'" style="display: none;">
                        <p class="text-sm text-gray-600 mb-4">Defina pontuações para categorias individuais. Esta é a regra mais específica e sobrepõe-se a todas as outras.</p>
                        <div class="space-y-6">
                             @foreach($campeonato->eventos as $evento)
                                 @foreach($evento->percursos as $percurso)
                                     @foreach($percurso->categorias as $categoria)
                                         <div class="p-4 border rounded-md">
                                             <h4 class="font-bold text-gray-800">{{$percurso->descricao}} / {{ $categoria->nome }}</h4>
                                             <div class="space-y-2 mt-2">
                                                 <template x-for="(regra, index) in (regrasPorCategoria[{{$categoria->id}}] || [])" :key="index">
                                                     <div class="flex items-center space-x-2">
                                                         <input type="number" :name="`regras_categoria[{{$categoria->id}}][${index}][posicao]`" x-model="regra.posicao" placeholder="Posição" class="w-1/3 rounded-md border-gray-300 text-sm">
                                                         <input type="number" :name="`regras_categoria[{{$categoria->id}}][${index}][pontos]`" x-model="regra.pontos" placeholder="Pontos" class="w-1/3 rounded-md border-gray-300 text-sm">
                                                         {{-- BOTÃO DE REMOVER --}}
                                                         <button @click.prevent="removeRegra('categoria', {{$categoria->id}}, index)" class="text-red-500 hover:text-red-700 p-2 rounded-md transition duration-150 ease-in-out">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" />
                                                            </svg>
                                                         </button>
                                                     </div>
                                                 </template>
                                             </div>
                                             <button @click.prevent="addRegra('categoria', {{$categoria->id}})" class="mt-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Adicionar Regra</button>
                                         </div>
                                     @endforeach
                                 @endforeach
                             @endforeach
                        </div>
                    </div>

                    <div class="border-t mt-6 pt-6 flex justify-end">
                        <x-primary-button>Salvar Todas as Regras</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

