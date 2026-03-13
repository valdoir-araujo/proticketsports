@extends('layouts.public')

@section('title', config('app.name') . ' - ' . config('app.tagline'))
@section('meta_description', config('app.tagline') . '. Inscreva-se em corridas, ciclismo, triathlon e mais. Calendário de eventos, inscrições seguras, check-in e resultados.')
@section('canonical', url()->current())

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .animate-bar {
            animation: growBar 2s ease-out forwards;
        }
        @keyframes growBar {
            from { height: 0; }
        }
        /* Remove seta nativa do select Modalidade para não duplicar com o ícone customizado */
        #modalidade_id {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
@endpush


@section('content')

    {{-- ================================================================== --}}
    {{-- 1. BANNER / SLIDER                                                 --}}
    {{-- ================================================================== --}}
    @if($banners->isNotEmpty())
        <div class="relative w-full z-0 group" x-data='{
            activeSlide: 0,
            slides: @json($banners),
            autoplay() {
                if (this.slides.length <= 1) return;
                setInterval(() => { this.next() }, 5000);
            },
            next() {
                this.activeSlide = (this.activeSlide + 1) % this.slides.length;
            },
            prev() {
                this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length;
            }
        }' x-init="autoplay()">

            {{-- Banner: altura fixa; imagem enquadrada com object-cover (corta nas bordas, sem distorcer) --}}
            <div class="relative w-full h-48 md:h-64 lg:h-80 overflow-hidden bg-slate-900">
                <template x-for="(slide, index) in slides" :key="slide.id">
                    <div x-show="activeSlide === index"
                         x-transition:enter="transition-opacity duration-700 ease-out"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition-opacity duration-700 ease-in"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute inset-0 flex">
                        <a :href="slide.link" target="_blank" class="block w-full h-full min-w-0 min-h-0 overflow-hidden">
                            <img :src="slide.url" :alt="slide.titulo"
                                 class="block w-full h-full min-w-0 min-h-0 object-cover object-center"
                                 onerror="this.onerror=null; this.src='https://placehold.co/1920x600/f97316/ffffff?text=Banner+Evento'">
                        </a>
                    </div>
                </template>
            </div>

            {{-- Navegação (Setas) --}}
            <template x-if="slides.length > 1">
                <div>
                    <div class="absolute inset-0 flex items-center justify-between px-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                        <button @click="prev()" class="pointer-events-auto bg-black/30 hover:bg-orange-600 text-white rounded-full w-10 h-10 flex items-center justify-center backdrop-blur-sm transition-all">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button @click="next()" class="pointer-events-auto bg-black/30 hover:bg-orange-600 text-white rounded-full w-10 h-10 flex items-center justify-center backdrop-blur-sm transition-all">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    {{-- Paginação (Bolinhas) --}}
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
                        <template x-for="(slide, index) in slides" :key="slide.id">
                            <button @click="activeSlide = index" 
                                    class="w-2.5 h-2.5 rounded-full transition-all duration-300 shadow-sm border border-white/20" 
                                    :class="{'bg-orange-500 w-6': activeSlide === index, 'bg-white/70 hover:bg-white': activeSlide !== index}"></button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    @else 
        {{-- Fallback (Sem Banners) --}}
        <div class="relative w-full h-48 md:h-64 lg:h-80 bg-slate-800 flex items-center justify-center z-0">
            <div class="text-center px-4">
                <i class="fa-solid fa-image text-slate-700 text-5xl mb-3"></i>
                <p class="text-slate-500 font-medium">Sem banners cadastrados</p> 
            </div>
        </div>
        
    @endif

    {{-- ================================================================== --}}
    {{-- 2. LISTAGEM DE EVENTOS E BUSCA                                     --}}
    {{-- ================================================================== --}}
    <section class="relative py-12 bg-white" 
             x-data="{ searchOpen: false, isMobile: window.innerWidth < 768 }" 
             @resize.window.debounce.300ms="isMobile = window.innerWidth < 768">
        
        <div class="relative z-10 container mx-auto px-4">
            
            <div class="max-w-6xl mx-auto">                 
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 border-l-4 border-orange-500 pl-4">
                        Próximos Eventos
                    </h2>
                    
                    {{-- Botão abrir/fechar pesquisa (Mobile) — só texto para não duplicar ícone da lupa do campo --}}
                    <div class="md:hidden">
                        <button @click="searchOpen = !searchOpen" 
                                class="flex items-center gap-2 text-sm font-semibold text-orange-600 p-2 rounded-md hover:bg-gray-100 transition-colors">
                            <span x-text="searchOpen ? 'Fechar' : 'Filtrar'"></span>
                        </button>
                    </div>
                </div>

                {{-- FORMULÁRIO DE BUSCA --}}
                <div x-show="!isMobile || searchOpen" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200 mb-10">
                    
                    <form action="{{ route('eventos.public.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        
                        <div class="lg:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Nome do Evento ou Cidade</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-search text-gray-400"></i></span>
                                <input type="text" name="search" id="search" class="block w-full rounded-md border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:border-orange-500 focus:ring-orange-500 pl-10 py-3 text-base shadow-sm min-h-[44px]" 
                                       placeholder="Pesquisar..." value="{{ request('search') }}">
                            </div>
                        </div>
                        
                        <div>
                            <label for="modalidade_id" class="block text-sm font-medium text-gray-700 mb-1">Modalidade</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-solid fa-tags text-gray-400"></i></span>
                                <select name="modalidade_id" id="modalidade_id" class="block w-full rounded-md border-gray-300 bg-white text-gray-700 focus:border-orange-500 focus:ring-orange-500 pl-10 pr-10 py-3 text-base shadow-sm cursor-pointer appearance-none bg-no-repeat min-h-[44px]">
                                    <option value="">Todas</option>
                                    @php
                                        $modalidades = isset($modalidades) ? $modalidades : \App\Models\Modalidade::orderBy('nome')->get();
                                    @endphp
                                    @foreach($modalidades as $modalidade)
                                        <option value="{{ $modalidade->id }}" {{ request('modalidade_id') == $modalidade->id ? 'selected' : '' }}>{{ $modalidade->nome }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">A partir de</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fa-regular fa-calendar text-gray-400"></i></span>
                                <input type="date" name="data_inicio" id="data_inicio" class="block w-full rounded-md border-gray-300 bg-white text-gray-700 focus:border-orange-500 focus:ring-orange-500 pl-10 py-3 text-base shadow-sm min-h-[44px]"
                                       value="{{ request('data_inicio') }}">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex items-center justify-center min-h-[44px] px-4 py-3 text-base bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-md shadow-sm transition duration-300">
                                <i class="fa-solid fa-filter mr-2"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>

                {{-- GRID DE EVENTOS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($eventosDestaque ?? [] as $evento)
                        <article class="bg-white rounded-2xl shadow-md border border-slate-200/80 overflow-hidden hover:shadow-xl hover:border-orange-200 transition-all duration-300 flex flex-col group h-full">
                            <div class="h-1 bg-gradient-to-r from-orange-500 to-orange-400"></div>
                            <a href="{{ route('eventos.public.show', $evento) }}" class="relative block overflow-hidden aspect-[16/10] bg-slate-100">
                                <div class="absolute inset-0 bg-slate-900/5 group-hover:bg-transparent transition-colors z-10"></div>
                                <img class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                                     src="{{ $evento->thumbnail_url ? asset('storage/' . $evento->thumbnail_url) : 'https://placehold.co/400x250/e2e8f0/64748b?text=Evento' }}"
                                     alt="{{ $evento->nome }}"
                                     onerror="this.onerror=null; this.src='https://placehold.co/400x250/e2e8f0/64748b?text=Sem+Imagem'">
                            </a>
                            <div class="p-5 flex flex-col flex-grow min-h-0">
                                @if($evento->modalidade)
                                    <span class="inline-block self-start bg-orange-100 text-orange-700 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide mb-2">
                                        {{ $evento->modalidade->nome }}
                                    </span>
                                @endif
                                <h3 class="text-lg font-bold text-slate-900 leading-snug mb-3 line-clamp-2 group-hover:text-orange-600 transition-colors">
                                    <a href="{{ route('eventos.public.show', $evento) }}" class="block">{{ $evento->nome }}</a>
                                </h3>
                                <ul class="space-y-2.5 text-sm text-slate-600 mb-4 flex-grow">
                                    <li class="flex items-start gap-2.5">
                                        <i class="fa-solid fa-location-dot text-orange-500 mt-0.5 shrink-0 w-4 text-center"></i>
                                        <span class="min-w-0">
                                            @if($evento->local)
                                                <span class="font-semibold text-slate-800 block truncate">{{ $evento->local }}</span>
                                            @endif
                                            <span class="text-slate-500">{{ $evento->cidade?->nome ?? '—' }} - {{ $evento->estado?->uf ?? '' }}</span>
                                        </span>
                                    </li>
                                    <li class="flex items-center gap-2.5">
                                        <i class="fa-regular fa-calendar text-blue-500 shrink-0 w-4 text-center"></i>
                                        @if($evento->data_evento)
                                            <span class="font-medium">{{ $evento->data_evento->format('d/m/Y') }} · {{ $evento->data_evento->format('H:i') }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Data a definir</span>
                                        @endif
                                    </li>
                                </ul>
                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                    @php
                                        $agora = now();
                                        $inicio = $evento->data_inicio_inscricoes ? \Carbon\Carbon::parse($evento->data_inicio_inscricoes) : null;
                                        $fim = $evento->data_fim_inscricoes ? \Carbon\Carbon::parse($evento->data_fim_inscricoes) : null;
                                    @endphp
                                    @if($inicio && $agora->lt($inicio))
                                        <span class="text-xs font-bold text-amber-600 flex items-center gap-1.5"><i class="fa-regular fa-clock"></i> Em breve</span>
                                    @elseif($fim && $agora->gt($fim))
                                        <span class="text-xs font-bold text-red-600 flex items-center gap-1.5"><i class="fa-solid fa-lock"></i> Encerrado</span>
                                    @else
                                        <span class="text-xs font-bold text-green-600 flex items-center gap-1.5"><i class="fa-solid fa-circle-check"></i> Inscrições abertas</span>
                                    @endif
                                    <a href="{{ route('eventos.public.show', $evento) }}" class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 transition-colors shadow-sm group/link shrink-0">
                                        Ver evento <i class="fa-solid fa-arrow-right ml-1.5 text-xs transition-transform group-hover/link:translate-x-0.5"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <p class="text-gray-500 text-lg">Nenhum evento em destaque no momento.</p>
                        </div>
                    @endforelse
                </div>

                <div class="text-center mt-12">
                    <a href="{{ route('eventos.public.index') }}" class="inline-block bg-white text-slate-700 border border-slate-300 font-bold py-3 px-8 rounded-full text-lg hover:bg-orange-50 hover:text-orange-600 hover:border-orange-300 transition duration-300 shadow-sm">
                        Ver Calendário Completo
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- 3. SEÇÃO ORGANIZADORES                                             --}}
    {{-- ================================================================== --}}
    <section class="relative py-24 bg-slate-950 overflow-hidden">
        {{-- Conteúdo estático mantido --}}
        <div class="absolute inset-0 opacity-20 pointer-events-none" 
             style="background-image: radial-gradient(#fb923c 1px, transparent 1px); background-size: 30px 30px;">
        </div>
        <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-orange-900/20 to-transparent pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-orange-500 font-bold tracking-widest uppercase text-xs">Área do Organizador</span>
                <h2 class="text-4xl md:text-5xl font-black text-white mt-3 mb-6 leading-tight">
                    Transforme a Gestão do <br> Seu Evento Esportivo
                </h2>
                <p class="text-lg text-slate-400">
                    Abandone as planilhas manuais. Tenha um painel completo, financeiro transparente e pagamentos automáticos.
                </p>
                <a href="{{ route('para-organizadores') }}" class="inline-block mt-4 text-orange-400 font-semibold hover:text-orange-300 transition-colors">
                    Saiba mais sobre a plataforma para organizadores <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <div class="md:col-span-2 bg-slate-900 rounded-3xl p-8 border border-slate-800 hover:border-orange-500/30 transition-colors duration-300 group relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500 mb-6 group-hover:bg-orange-50 group-hover:text-white transition-colors">
                            <i class="fa-solid fa-chart-pie text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Controle Financeiro Total</h3>
                        <p class="text-slate-400 max-w-md">Acompanhe cada inscrição em tempo real. Veja receitas, taxas e solicite repasses com um clique.</p>
                    </div>
                    <div class="absolute bottom-0 right-0 w-1/2 h-32 flex items-end gap-2 px-6 pb-6 opacity-50">
                        <div class="w-full bg-orange-900/30 h-[40%] rounded-t-md"></div>
                        <div class="w-full bg-orange-800/40 h-[60%] rounded-t-md"></div>
                        <div class="w-full bg-orange-700/50 h-[30%] rounded-t-md"></div>
                        <div class="w-full bg-orange-600/60 h-[80%] rounded-t-md animate-bar"></div>
                        <div class="w-full bg-orange-500 h-[100%] rounded-t-md"></div>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-3xl p-8 border border-slate-800 hover:border-green-500/30 transition-colors duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500 mb-6 group-hover:bg-green-500 group-hover:text-white transition-colors">
                        <i class="fa-brands fa-pix text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Pagamento Automático</h3>
                    <p class="text-slate-400 text-sm">Baixa automática de inscrições via Pix e Cartão.</p>
                </div>

                <div class="bg-slate-900 rounded-3xl p-8 border border-slate-800 hover:border-blue-500/30 transition-colors duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 mb-6 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-trophy text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Ranking & Resultados</h3>
                    <p class="text-slate-400 text-sm">Gere resultados por categoria e ranking de campeonato.</p>
                </div>

                <div class="md:col-span-2 bg-gradient-to-r from-orange-600 to-red-600 rounded-3xl p-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-lg shadow-orange-900/20">
                    <div class="text-center md:text-left">
                        <h3 class="text-2xl font-black text-white mb-2">Pronto para começar?</h3>
                        <p class="text-orange-100">Crie seu primeiro evento agora.</p>
                    </div>
                    <div>
                        {{-- LÓGICA DE LINK CORRIGIDA: Se tiver org, vai pro dashboard. Se não, vai pra criar. --}}
                        @auth
                            @if(Auth::user()->organizacoes()->exists())
                                <a href="{{ route('organizador.dashboard') }}" class="inline-block bg-white text-orange-600 font-bold py-3 px-8 rounded-xl hover:bg-orange-50 transition transform hover:-translate-y-1 shadow-xl">
                                    Publicar Evento
                                </a>
                            @else
                                <a href="{{ route('organizador.organizacao.create') }}" class="inline-block bg-white text-orange-600 font-bold py-3 px-8 rounded-xl hover:bg-orange-50 transition transform hover:-translate-y-1 shadow-xl">
                                    Publicar Evento
                                </a>
                            @endif
                        @else
                            <a href="{{ route('register') }}" class="inline-block bg-white text-orange-600 font-bold py-3 px-8 rounded-xl hover:bg-orange-50 transition transform hover:-translate-y-1 shadow-xl">
                                Publicar Evento
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
