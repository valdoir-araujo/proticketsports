@extends('layouts.public')

@section('title', $evento->nome . ' - Proticketsports')

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

            {{-- SECÇÃO DE LOTES GERAIS --}}
            @if($evento->lotesInscricaoGeral->isNotEmpty())
                <div class="mt-8 pt-8 border-t" x-data="{ open: true }">
                    <div class="mb-6 flex justify-between items-center cursor-pointer" @click="open = !open">
                        <h2 class="text-2xl font-bold uppercase tracking-wider border-l-4 border-orange-500 pl-4 text-slate-800">Valores Inscrições</h2>
                        <button class="text-slate-500 hover:text-slate-800 p-2 rounded-full">
                            <i class="fa-solid fa-chevron-up transition-transform" :class="{ 'rotate-180': !open }"></i>
                        </button>
                    </div>
                    
                    <div x-show="open" x-transition x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($evento->lotesInscricaoGeral->sortBy('data_inicio') as $lote)
                            @php
                                $agora = now();
                                $isAtivo = $agora->between($lote->data_inicio, $lote->data_fim);
                                $isEncerrado = $agora->isAfter($lote->data_fim);
                                $cardClasses = 'p-4 rounded-lg border-2 transition-all duration-300';
                                $statusText = ''; $statusClasses = ''; $priceClasses = 'text-slate-800';

                                if ($isAtivo) { $cardClasses .= ' bg-orange-50 border-orange-500 shadow-lg'; $statusText = 'Ativo'; $statusClasses = 'text-white bg-orange-500'; $priceClasses = 'text-orange-600'; } 
                                elseif ($isEncerrado) { $cardClasses .= ' bg-gray-100 border-gray-200 opacity-75'; $statusText = 'Encerrado'; $statusClasses = 'text-gray-700 bg-gray-200'; } 
                                else { $cardClasses .= ' bg-white border-gray-200'; $statusText = 'Em Breve'; $statusClasses = 'text-slate-700 bg-slate-200'; }

                                $taxaEvento = $evento->taxaservico ?? 0;
                                $valorTaxa = $lote->valor * ($taxaEvento / 100);
                            @endphp
                            <div class="{{ $cardClasses }}">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-bold text-slate-800">{{ $lote->nome }}</h3>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $statusClasses }}">{{ $statusText }}</span>
                                </div>
                                <div class="mt-3">
                                    <p class="text-2xl font-black {{ $priceClasses }}">
                                        R$ {{ number_format($lote->valor, 2, ',', '.') }}
                                    </p>
                                    @if($valorTaxa > 0)
                                        <p class="text-sm font-semibold text-gray-600 mt-1">
                                            + R$ {{ number_format($valorTaxa, 2, ',', '.') }} <span class="text-xs font-normal text-gray-500">(Taxa Serviço)</span>
                                        </p>
                                    @else
                                        <p class="text-xs text-green-600 font-bold mt-1">Taxa Grátis</p>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-3 flex items-center border-t pt-2">
                                    <i class="fa-regular fa-clock mr-1"></i> Até {{ $lote->data_fim->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center justify-end gap-4 mt-8 pt-8 border-t">
                
                {{-- Botão Loja do Evento --}}
                <a href="{{ route('loja.index', ['evento_id' => $evento->id]) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-lg rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fa-solid fa-store mr-3"></i>Loja Oficial
                </a>

                @if (now()->isBefore($evento->data_inicio_inscricoes))
                    <span class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-gray-400 text-white font-bold text-lg rounded-lg shadow-md cursor-not-allowed"><i class="fa-solid fa-clock mr-3"></i>Aguarde Inscrições</span>
                @elseif (now()->isAfter($evento->data_fim_inscricoes))
                        <span class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-gray-400 text-white font-bold text-lg rounded-lg shadow-md cursor-not-allowed"><i class="fa-solid fa-lock mr-3"></i>Inscrições Encerradas</span>
                @else
                    @auth
                        @if ($inscricaoExistente)
                            <a href="{{ route('inscricao.show', $inscricaoExistente) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"><i class="fa-solid fa-circle-check mr-3"></i>Ver Minha Inscrição</a>
                        @else
                            <a href="{{ route('inscricao-grupo.identificacao', $evento) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-slate-700 hover:bg-slate-800 text-white font-bold text-lg rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                                <i class="fa-solid fa-users mr-3"></i>Inscrição em Grupo
                            </a>
                            <a href="{{ route('inscricao.identificacao', $evento) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold text-lg rounded-lg shadow-lg hover:shadow-xl hover:from-orange-600 hover:to-red-600 transition-all duration-300 transform hover:-translate-y-1">
                                <i class="fa-solid fa-arrow-right-to-bracket mr-3"></i>Inscreva-se Agora!
                            </a>
                        @endif
                    @else
                        <a href="{{ route('inscricao-grupo.identificacao', $evento) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-slate-700 hover:bg-slate-800 text-white font-bold text-lg rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <i class="fa-solid fa-users mr-3"></i>Inscrição em Grupo
                        </a>
                        <a href="{{ route('inscricao.identificacao', $evento) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold text-lg rounded-lg shadow-lg hover:shadow-xl hover:from-orange-600 hover:to-red-600 transition-all duration-300 transform hover:-translate-y-1">
                            <i class="fa-solid fa-arrow-right-to-bracket mr-3"></i>Inscreva-se Agora!
                        </a>
                    @endauth
                @endif
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
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-6 bg-orange-500 rounded-full"></span>
                        <h2 class="text-xl font-bold uppercase tracking-wide text-slate-800">Regulamento Oficial</h2>
                    </div>
                    <div class="prose prose-slate max-w-none text-slate-600 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <p>O regulamento completo do evento será disponibilizado em breve pelo organizador.</p>
                    </div>
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