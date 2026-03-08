@extends('layouts.public')

@section('title', $evento->nome . ' - ' . config('app.name'))

@section('content')

    {{-- Cabeçalho Moderno e Compacto (Com Imagem) --}}
    <section class="relative bg-cover bg-center py-10 md:py-14">
        {{-- Imagem de fundo --}}
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $evento->banner_url ? asset('storage/' . $evento->banner_url) : 'https://images.unsplash.com/photo-1573521193826-58c7a24275a7?q=80&w=1974&auto=format&fit=crop' }}');"></div>
        {{-- Overlay escuro para legibilidade --}}
        <div class="absolute inset-0 bg-slate-900/85 backdrop-blur-[2px]"></div>

        {{-- Conteúdo Alinhado --}}
        <div class="container relative mx-auto px-4">
            <div class="max-w-6xl mx-auto px-6 md:px-8">
                {{-- ALTERAÇÃO GRID: Mudado para 5 colunas para dar mais espaço à imagem (40% da largura) --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-8 items-center">
                    
                    {{-- Coluna Esquerda: Título e Info (Ocupa 3/5 ou 60%) --}}
                    <div class="md:col-span-3 text-center md:text-left">
                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-3">
                            <span class="text-orange-400 font-bold text-xs uppercase tracking-wider border border-orange-400/30 px-3 py-1 rounded-full">
                                {{ $evento->modalidade ? $evento->modalidade->nome : 'Evento' }}
                            </span>
                        </div>
                        
                        {{-- Nome do Evento --}}
                        <h1 class="text-2xl md:text-4xl font-extrabold text-white leading-tight drop-shadow-md mb-3">{{ $evento->nome }}</h1>
                        
                        {{-- Cidade/UF --}}
                        @if($evento->cidade)
                            <div class="flex justify-center md:justify-start">
                                <span class="text-slate-300 font-medium text-sm uppercase tracking-wider flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-orange-500"></i> {{ $evento->cidade->nome }} - {{ $evento->cidade->estado->uf }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Coluna Direita: Imagem do Evento (Ocupa 2/5 ou 40%) --}}
                    <div class="md:col-span-2 flex justify-center md:justify-end">
                        {{-- Agora a imagem tem espaço para crescer até max-w-lg --}}
                        <div class="w-full max-w-lg rounded-xl overflow-hidden shadow-2xl border-4 border-white/10 bg-slate-800">
                             <img src="{{ $evento->thumbnail_url ? asset('storage/' . $evento->thumbnail_url) : 'https://placehold.co/600x400?text=Imagem+Evento' }}" 
                                  alt="Imagem do evento {{ $evento->nome }}"
                                  class="w-full h-full object-cover aspect-video">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CORPO DA PÁGINA --}}
    <div class="container mx-auto p-4 md:p-8 max-w-6xl">

        {{-- CARD PRINCIPAL --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6 md:p-8 mt-4 relative z-10">
            
            {{-- ÍCONE REMOVIDO CONFORME SOLICITADO --}}
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-2">
                <div class="p-6 rounded-lg bg-slate-50 border">
                    <p class="text-left text-lg font-bold text-slate-800 mb-2">INSCRIÇÕES ENCERRAM EM</p> <hr class="mb-4">
                    <div id="countdown" class="flex justify-around text-center">
                        <div><span id="days" class="text-4xl font-bold text-slate-700">00</span><p class="text-sm text-slate-500">Dias</p></div>
                        <div><span id="hours" class="text-4xl font-bold text-slate-700">00</span><p class="text-sm text-slate-500">Horas</p></div>
                        <div><span id="minutes" class="text-4xl font-bold text-slate-700">00</span><p class="text-sm text-slate-500">Minutos</p></div>
                        <div><span id="seconds" class="text-4xl font-bold text-slate-700">00</span><p class="text-sm text-slate-500">Segundos</p></div>
                    </div>
                </div>
                
                <div class="flex flex-col justify-center space-y-6">
                    <div class="flex items-center">
                        <div class="bg-red-100 text-red-600 p-4 rounded-xl shadow-sm">
                            <i class="fa-regular fa-calendar-alt fa-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-500 uppercase">{{ $evento->data_evento->translatedFormat('l') }}</p>
                            <p class="text-lg font-bold text-slate-800">{{ $evento->data_evento->translatedFormat('d \de F \de Y') }}</p>
                            <p class="text-sm text-gray-600">Início às {{ $evento->data_evento->translatedFormat('H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="bg-blue-100 text-blue-600 p-4 rounded-xl shadow-sm">
                            <i class="fa-solid fa-location-dot fa-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-lg font-bold text-slate-800">{{ $evento->local }}</p>
                            <p class="text-sm text-gray-600">{{ $evento->cidade ? $evento->cidade->nome . ' - ' . $evento->cidade->estado->uf : '' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- VALORES INSCRIÇÕES — listagem compacta de lotes --}}
            @if($evento->lotesInscricaoGeral->isNotEmpty())
                <div class="mt-8 pt-8 border-t" x-data="{ open: true }">
                    <div class="flex justify-between items-center cursor-pointer mb-3" @click="open = !open">
                        <h2 class="text-xl font-bold uppercase tracking-wide border-l-4 border-orange-500 pl-3 text-slate-800">Valores Inscrições</h2>
                        <span class="text-slate-400 p-1"><i class="fa-solid fa-chevron-up text-sm transition-transform" :class="{ 'rotate-180': !open }"></i></span>
                    </div>
                    <div x-show="open" x-transition x-cloak class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50/50">
                        {{-- Desktop: tabela compacta --}}
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-100 border-b border-slate-200 text-left text-slate-600 font-semibold">
                                        <th class="py-2.5 px-3">Lote</th>
                                        <th class="py-2.5 px-3">Valor</th>
                                        <th class="py-2.5 px-3">Taxa</th>
                                        <th class="py-2.5 px-3">Válido até</th>
                                        <th class="py-2.5 px-3 text-center whitespace-nowrap" style="min-width: 5.5rem;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evento->lotesInscricaoGeral->sortBy('data_inicio') as $lote)
                                        @php
                                            $agora = now();
                                            $isAtivo = $agora->between($lote->data_inicio, $lote->data_fim);
                                            $isEncerrado = $agora->isAfter($lote->data_fim);
                                            $taxaEvento = $evento->taxaservico ?? 0;
                                            $valorTaxa = $lote->valor * ($taxaEvento / 100);
                                            $statusText = $isAtivo ? 'Ativo' : ($isEncerrado ? 'Encerrado' : 'Em Breve');
                                            $statusBg = $isAtivo ? 'bg-orange-500 text-white' : ($isEncerrado ? 'bg-gray-200 text-gray-700' : 'bg-slate-200 text-slate-700');
                                        @endphp
                                        <tr class="border-b border-slate-100 last:border-0 hover:bg-white/60 transition-colors">
                                            <td class="py-2.5 px-3 font-medium text-slate-800">{{ $lote->nome }}</td>
                                            <td class="py-2.5 px-3 font-bold {{ $isAtivo ? 'text-orange-600' : 'text-slate-800' }}">R$ {{ number_format($lote->valor, 2, ',', '.') }}</td>
                                            <td class="py-2.5 px-3 text-slate-600">{{ $valorTaxa > 0 ? '+ R$ ' . number_format($valorTaxa, 2, ',', '.') : '—' }}</td>
                                            <td class="py-2.5 px-3 text-slate-500">{{ $lote->data_fim->format('d/m/Y H:i') }}</td>
                                            <td class="py-2.5 px-3 text-center whitespace-nowrap"><span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $statusBg }} whitespace-nowrap">{{ $statusText }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Mobile: linhas compactas --}}
                        <div class="sm:hidden divide-y divide-slate-200">
                            @foreach($evento->lotesInscricaoGeral->sortBy('data_inicio') as $lote)
                                @php
                                    $agora = now();
                                    $isAtivo = $agora->between($lote->data_inicio, $lote->data_fim);
                                    $isEncerrado = $agora->isAfter($lote->data_fim);
                                    $valorTaxa = $lote->valor * (($evento->taxaservico ?? 0) / 100);
                                    $statusText = $isAtivo ? 'Ativo' : ($isEncerrado ? 'Encerrado' : 'Em Breve');
                                    $statusBg = $isAtivo ? 'bg-orange-500 text-white' : ($isEncerrado ? 'bg-gray-200 text-gray-700' : 'bg-slate-200 text-slate-700');
                                @endphp
                                <div class="px-3 py-2.5 flex justify-between items-center gap-2">
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-800 truncate">{{ $lote->nome }}</p>
                                        <p class="text-xs text-slate-500">Até {{ $lote->data_fim->format('d/m/Y') }} · {{ $valorTaxa > 0 ? '+ R$ ' . number_format($valorTaxa, 2, ',', '.') . ' taxa' : 'sem taxa' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="font-bold {{ $isAtivo ? 'text-orange-600' : 'text-slate-700' }}">R$ {{ number_format($lote->valor, 2, ',', '.') }}</span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $statusBg }} whitespace-nowrap">{{ $statusText }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 mt-8 pt-8 border-t">
                @if (now()->isBefore($evento->data_inicio_inscricoes))
                    <span class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-200 text-slate-500 font-semibold text-base cursor-not-allowed"><i class="fa-solid fa-clock mr-2"></i>Aguarde Inscrições</span>
                @elseif (now()->isAfter($evento->data_fim_inscricoes))
                    <span class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-200 text-slate-500 font-semibold text-base cursor-not-allowed"><i class="fa-solid fa-lock mr-2"></i>Inscrições Encerradas</span>
                @else
                    @auth
                        @if ($inscricaoExistente)
                            <a href="{{ route('inscricao.show', $inscricaoExistente) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-semibold text-base shadow-md hover:shadow-lg transition-all"><i class="fa-solid fa-circle-check mr-2"></i>Ver Minha Inscrição</a>
                            <a href="{{ route('inscricao-grupo.identificacao', $evento) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-semibold text-base shadow-md hover:shadow-lg transition-all"><i class="fa-solid fa-users mr-2"></i>Inscrição em Grupo</a>
                        @else
                            <a href="{{ route('inscricao.identificacao', $evento) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold text-base shadow-md hover:shadow-lg transition-all"><i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>Inscreva-se Agora!</a>
                            <a href="{{ route('inscricao-grupo.identificacao', $evento) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-semibold text-base shadow-md hover:shadow-lg transition-all"><i class="fa-solid fa-users mr-2"></i>Inscrição em Grupo</a>
                        @endif
                    @else
                        <a href="{{ route('inscricao.identificacao', $evento) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold text-base shadow-md hover:shadow-lg transition-all"><i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>Inscreva-se Agora!</a>
                        <a href="{{ route('inscricao-grupo.identificacao', $evento) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-semibold text-base shadow-md hover:shadow-lg transition-all"><i class="fa-solid fa-users mr-2"></i>Inscrição em Grupo</a>
                    @endauth
                @endif
                <a href="{{ route('loja.index', ['evento_id' => $evento->id]) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl border-2 border-teal-600 text-teal-600 hover:bg-teal-600 hover:text-white font-semibold text-base transition-all"><i class="fa-solid fa-store mr-2"></i>Loja Oficial</a>
            </div>
        </div>

        {{-- CARD SECUNDÁRIO --}}
        <div x-data="{ tab: 'sobre', showListMessage: false, open: true }" class="bg-white rounded-xl shadow-lg mt-8">
             <div @click="open = !open" class="p-4 md:p-6 flex justify-between items-center cursor-pointer border-b">
                 <h2 class="text-2xl font-bold uppercase tracking-wider text-slate-800">Detalhes do Evento</h2>
                 <button class="text-slate-500 hover:text-slate-800 p-2 rounded-full">
                     <i class="fa-solid fa-chevron-up transition-transform" :class="{ 'rotate-180': !open }"></i>
                 </button>
             </div>
            
            <div x-show="open" x-transition x-cloak>
                <div class="border-b bg-gray-50 overflow-x-auto">
                    <nav class="p-2 flex whitespace-nowrap">
                        <button @click="tab = 'sobre'" :class="{ 'bg-orange-500 text-white shadow': tab === 'sobre', 'text-gray-600 hover:bg-gray-200': tab !== 'sobre' }" class="flex items-center flex-shrink-0 py-2 px-4 font-medium text-sm rounded-md transition-all duration-200 mx-1"><i class="fa-solid fa-circle-info mr-2"></i> Sobre o Evento</button>
                        @if($evento->lista_inscritos_publica)
                            <a href="{{ route('eventos.public.inscritos', $evento) }}" class="flex items-center flex-shrink-0 py-2 px-4 font-medium text-sm rounded-md transition-all duration-200 text-gray-600 hover:bg-gray-200 mx-1"><i class="fa-solid fa-users mr-2"></i> Lista de Inscritos</a>
                        @else
                            <button @click="showListMessage = !showListMessage" class="flex items-center flex-shrink-0 py-2 px-4 font-medium text-sm rounded-md transition-all duration-200 text-gray-600 hover:bg-gray-200 mx-1"><i class="fa-solid fa-users mr-2"></i> Lista de Inscritos</button>
                        @endif
                        <button @click="tab = 'regulamento'" :class="{ 'bg-orange-500 text-white shadow': tab === 'regulamento', 'text-gray-600 hover:bg-gray-200': tab !== 'regulamento' }" class="flex items-center flex-shrink-0 py-2 px-4 font-medium text-sm rounded-md transition-all duration-200 mx-1"><i class="fa-solid fa-file-contract mr-2"></i> Regulamento</button>
                        <button @click="tab = 'contatos'" :class="{ 'bg-orange-500 text-white shadow': tab === 'contatos', 'text-gray-600 hover:bg-gray-200': tab !== 'contatos' }" class="flex items-center flex-shrink-0 py-2 px-4 font-medium text-sm rounded-md transition-all duration-200 mx-1"><i class="fa-solid fa-address-card mr-2"></i> Contatos</button>
                    </nav>
                </div>

                <div x-show="showListMessage" style="display: none;" class="p-4 bg-yellow-50 text-yellow-800 border-b text-sm" x-cloak>
                    <div class="flex items-center"><i class="fa-solid fa-circle-info mr-3 text-yellow-500"></i><span>A lista de inscritos para este evento não está disponível publicamente no momento.</span></div>
                </div>
                
                <div x-show="tab === 'sobre'" class="p-6 md:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                        <div class="lg:col-span-3" x-data="{ open: true }">
                            <div class="flex justify-between items-center mb-4 cursor-pointer" @click="open = !open">
                                <h2 class="text-2xl font-bold uppercase tracking-wider border-l-4 border-orange-500 pl-4 text-slate-800">Descrição</h2>
                                <button class="text-slate-500 hover:text-slate-800">
                                    <i class="fa-solid fa-chevron-up transition-transform" :class="{ 'rotate-180': !open }"></i>
                                </button>
                            </div>
                            <div x-show="open" x-transition x-cloak class="bg-slate-50 border rounded-lg p-6">
                                <div class="prose max-w-none text-gray-700">
                                    @if($evento->descricao_completa) {!! $evento->descricao_completa !!} @else <p>Descrição completa do evento em breve.</p> @endif
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <div class="space-y-6">
                                @forelse($evento->percursos->sortBy('id') as $percurso)
                                    <div class="border rounded-lg overflow-hidden shadow-sm" x-data="{ open: true }">
                                        <div @click="open = !open" class="p-4 flex justify-between items-center cursor-pointer bg-slate-100 hover:bg-slate-200 transition-colors">
                                            <h3 class="text-lg font-bold text-slate-700 border-l-4 border-orange-500 pl-4">Percurso - {{ $percurso->descricao }}</h3>
                                            <button class="text-slate-500 hover:text-slate-800">
                                                <i class="fa-solid fa-chevron-up transition-transform" :class="{ 'rotate-180': !open }"></i>
                                            </button>
                                        </div>
                                        <div x-show="open" x-transition x-cloak class="p-4 border-t bg-white">
                                            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                                <span>
                                                    <i class="fa-solid fa-route mr-2 text-blue-500"></i>
                                                    <strong>Distância:</strong> 
                                                    {{ floor($percurso->distancia_km) == $percurso->distancia_km ? number_format($percurso->distancia_km, 0, ',', '.') : number_format($percurso->distancia_km, 3, ',', '.') }} km
                                                </span>
                                                <span><i class="fa-solid fa-mountain mr-2 text-green-500"></i><strong>Altimetria:</strong> {{ $percurso->altimetria_metros }} m</span>
                                                <span><i class="fa-solid fa-clock mr-2 text-yellow-500"></i><strong>Alinhamento:</strong> {{ \Carbon\Carbon::parse($percurso->horario_alinhamento)->format('H:i') }}</span>
                                                <span><i class="fa-solid fa-stopwatch mr-2 text-red-500"></i><strong>Largada:</strong> {{ \Carbon\Carbon::parse($percurso->horario_largada)->format('H:i') }}</span>
                                            </div>
                                            @if($percurso->strava_route_url)<a href="{{ $percurso->strava_route_url }}" target="_blank" class="inline-block mt-3 text-orange-600 font-semibold text-sm hover:underline"><i class="fa-brands fa-strava mr-1"></i>Ver Rota no Strava</a>@endif
                                            
                                            {{-- ======================================================== --}}
                                            {{-- LISTAGEM DE CATEGORIAS COM SUBTITULO MODERNO --}}
                                            {{-- ======================================================== --}}
                                            <div class="mt-6 mb-4">
                                                <div class="relative flex py-2 items-center">
                                                    <span class="flex-shrink-0 mr-4 text-sm font-extrabold text-slate-800 uppercase tracking-wide">
                                                        <i class="fa-solid fa-users-line mr-2 text-orange-500"></i> Categorias
                                                    </span>
                                                    <div class="flex-grow border-t border-slate-200"></div>
                                                </div>
                                                
                                                @forelse($percurso->categorias->groupBy('genero') as $genero => $categoriasDoGenero)
                                                    <div class="mb-3">
                                                        <p class="text-xs font-bold uppercase mb-2 border-l-4 border-orange-500 pl-4 text-orange-500">{{ ucfirst($genero) }}</p>
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($categoriasDoGenero->sortBy('id') as $categoria)
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                                    {{ $categoria->nome }}
                                                                    @if(isset($categoria->idade_min) || isset($categoria->idade_max))
                                                                        <span class="ml-1 text-slate-400 text-[10px]">
                                                                            ({{ $categoria->idade_min ?? '0' }}-{{ $categoria->idade_max ?? '99' }})
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="text-xs text-slate-400 italic">Em breve.</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                        <p class="text-slate-500">Informações de percurso em breve.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                
                <div x-show="tab === 'regulamento'" style="display: none;" class="p-6 md:p-10">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-orange-500 rounded-full"></span>
                            <h2 class="text-xl font-bold uppercase tracking-wide text-slate-800">Regulamento Oficial</h2>
                        </div>
                        @if((($evento->regulamento_tipo === 'pdf' && $evento->regulamento_arquivo) || ($evento->regulamento_tipo === 'texto' && $evento->regulamento_texto)) && $evento->regulamento_atualizado_em)
                            <p class="text-sm text-slate-500 flex items-center">
                                <i class="fa-regular fa-clock mr-2"></i> Última atualização: {{ $evento->regulamento_atualizado_em->format('d/m/Y \à\s H:i') }}
                            </p>
                        @endif
                    </div>
                    @if($evento->regulamento_tipo === 'pdf' && $evento->regulamento_arquivo)
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 text-center">
                            <i class="fa-solid fa-file-pdf text-5xl text-red-500 mb-4"></i>
                            <p class="text-slate-700 font-medium mb-4">Regulamento disponível em PDF.</p>
                            <a href="{{ asset('storage/' . $evento->regulamento_arquivo) }}" target="_blank" rel="noopener" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md transition-all">
                                <i class="fa-solid fa-download mr-2"></i> Baixar regulamento (PDF)
                            </a>
                        </div>
                    @elseif($evento->regulamento_tipo === 'texto' && $evento->regulamento_texto)
                        <div class="prose prose-slate prose-headings:text-slate-800 prose-p:text-slate-600 prose-li:text-slate-600 max-w-none bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100">
                            {!! $evento->regulamento_texto !!}
                        </div>
                    @else
                        <div class="prose prose-slate max-w-none text-slate-600 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                            <p>O regulamento completo do evento será disponibilizado em breve pelo organizador.</p>
                        </div>
                    @endif
                </div>

                <div x-show="tab === 'contatos'" style="display: none;" class="p-6 md:p-10">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-6 bg-teal-500 rounded-full"></span>
                        <h2 class="text-xl font-bold uppercase tracking-wide text-slate-800">Contatos do Organizador</h2>
                    </div>
                    @if($evento->eventoContatos->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($evento->eventoContatos as $contato)
                                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col">
                                    <div class="flex items-start gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 shrink-0">
                                            <i class="fa-solid fa-user text-xl"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-slate-800 text-lg">{{ $contato->nome }}</p>
                                            @if($contato->cargo)<p class="text-sm text-slate-500">{{ $contato->cargo }}</p>@endif
                                            @if($contato->telefone)
                                                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $contato->telefone) }}" target="_blank" rel="noopener" class="inline-flex items-center mt-3 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                                    <i class="fa-brands fa-whatsapp mr-2"></i> WhatsApp
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-8 text-center text-slate-500">
                            <i class="fa-solid fa-address-book text-4xl text-slate-300 mb-3"></i>
                            <p class="font-medium">Nenhum contato cadastrado para este evento.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<style>[x-cloak] { display: none !important; }</style>
<script>
    @if($evento->data_fim_inscricoes)
        const countdownDate = new Date("{{ \Carbon\Carbon::parse($evento->data_fim_inscricoes)->toIso8601String() }}").getTime();
        const countdownFunction = setInterval(function() {
            const elDays = document.getElementById("days");
            if (!elDays) return; 
            const now = new Date().getTime();
            const distance = countdownDate - now;
            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                elDays.innerText = String(days).padStart(2, '0');
                document.getElementById("hours").innerText = String(hours).padStart(2, '0');
                document.getElementById("minutes").innerText = String(minutes).padStart(2, '0');
                document.getElementById("seconds").innerText = String(seconds).padStart(2, '0');
            } else {
                clearInterval(countdownFunction);
                const countdownElement = document.getElementById("countdown");
                if(countdownElement) {
                    countdownElement.innerHTML = "<div class='w-full text-center'><span class='text-xl font-bold text-red-600'>Inscrições Encerradas!</span></div>";
                    countdownElement.classList.add('items-center', 'justify-center');
                }
            }
        }, 1000);
    @endif
</script>
@endpush