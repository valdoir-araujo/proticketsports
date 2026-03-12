@extends('layouts.public')

@section('title', 'Calendário de Eventos - ' . config('app.name'))
@section('meta_description', 'Calendário de corridas, ciclismo, triathlon e eventos esportivos. Veja datas, cidades e inscreva-se online.')
@section('canonical', url()->current())

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        #modalidade_id { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
    </style>
@endpush

@section('content')
    {{-- Cabeçalho da Página --}}
    <header class="bg-gray-900 text-white py-16 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight">Calendário de Eventos</h1>
            <p class="text-lg mt-3 text-gray-300 max-w-2xl mx-auto">Confira as próximas provas, desafios e competições.</p>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-4 md:p-8 min-h-screen" 
         x-data="{ searchOpen: false, isMobile: window.innerWidth < 768 }" 
         @resize.window.debounce.300ms="isMobile = window.innerWidth < 768">

        {{-- Título + Botão Filtrar (mobile) — mesmo padrão da welcome --}}
        <div class="flex justify-between items-center mb-6 -mt-8 relative z-20">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 border-l-4 border-orange-500 pl-4">
                Próximos Eventos
            </h2>
            <div class="md:hidden">
                <button type="button" @click="searchOpen = !searchOpen" 
                        class="flex items-center gap-2 text-sm font-semibold text-orange-600 p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <span x-text="searchOpen ? 'Fechar' : 'Filtrar'"></span>
                </button>
            </div>
        </div>

        {{-- Formulário de pesquisa (igual ao da welcome) --}}
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
                        <input type="date" name="data_inicio" id="data_inicio" class="block w-full rounded-md border-gray-300 bg-white text-gray-700 focus:border-orange-500 focus:ring-orange-500 pl-10 py-3 text-base shadow-sm min-h-[44px]" value="{{ request('data_inicio') }}">
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full flex items-center justify-center min-h-[44px] px-4 py-3 text-base bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-md shadow-sm transition duration-300">
                        <i class="fa-solid fa-filter mr-2"></i> Filtrar
                    </button>
                </div>
            </form>
            @if(request()->anyFilled(['search', 'modalidade_id', 'data_inicio']))
                <div class="flex justify-end mt-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('eventos.public.index') }}" class="text-sm text-gray-500 hover:text-orange-600 underline decoration-dotted">Limpar filtros</a>
                </div>
            @endif
        </div>

        {{-- Grelha de Eventos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                // PROTEÇÃO: Garante que $eventos seja iterável
                $listaEventos = isset($eventos) ? $eventos : [];
            @endphp

            @forelse($listaEventos as $evento)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group">
                    {{-- Imagem do Card --}}
                    <a href="{{ route('eventos.public.show', $evento) }}" class="relative block overflow-hidden h-60">
                        <div class="absolute inset-0 bg-slate-900/10 group-hover:bg-transparent transition-colors z-10"></div>
                        <img class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500" 
                             src="{{ $evento->thumbnail_url ? asset('storage/' . $evento->thumbnail_url) : 'https://via.placeholder.com/400x300?text=Evento' }}" 
                             alt="Imagem do evento {{ $evento->nome }}"
                             onerror="this.onerror=null; this.src='https://placehold.co/400x300/e2e8f0/64748b?text=Sem+Imagem'">
                    </a>

                    {{-- Corpo do Card --}}
                    <div class="p-5 flex flex-col flex-grow">
                        {{-- Título --}}
                        <h3 class="text-lg font-bold text-slate-900 leading-tight mb-1 group-hover:text-orange-600 transition-colors">
                            <a href="{{ route('eventos.public.show', $evento) }}">
                                {{ $evento->nome }}
                            </a>
                        </h3>

                        {{-- Modalidade (Logo abaixo do título) --}}
                        @if($evento->modalidade)
                            <div class="mb-3">
                                    <span class="inline-block bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">
                                    {{ $evento->modalidade->nome }}
                                </span>
                            </div>
                        @endif
                        
                        <div class="space-y-3 mb-4">
                            {{-- Local + Cidade --}}
                            <div class="flex items-start text-sm text-gray-600">
                                <i class="fa-solid fa-location-dot mt-1 mr-2 text-orange-500 w-4 text-center"></i>
                                <div>
                                    {{-- Local do Evento (em negrito) --}}
                                    @if($evento->local)
                                        <span class="font-bold text-gray-800 block leading-tight">{{ $evento->local }}</span>
                                    @endif
                                    
                                    {{-- Cidade / UF --}}
                                    <span class="text-xs text-gray-500">
                                        {{ $evento->cidade->nome ?? ($evento->cidade ?? 'Cidade n/d') }} - {{ $evento->estado->uf ?? ($evento->estado->nome ?? ($evento->estado ?? 'UF')) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Data Completa --}}
                            <div class="flex items-center text-sm text-gray-600">
                                    <i class="fa-regular fa-calendar mt-0.5 mr-2 text-blue-500 w-4 text-center"></i>
                                    <span class="font-medium">
                                    @if($evento->data_evento)
                                        {{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y') }}
                                        <span class="text-gray-300 mx-1">|</span>
                                        {{ \Carbon\Carbon::parse($evento->data_evento)->format('H:i') }}
                                    @else
                                        <span class="text-gray-400 italic">Data a definir</span>
                                    @endif
                                    </span>
                            </div>
                        </div>

                        <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                            @if($evento->data_fim_inscricoes && now()->isAfter($evento->data_fim_inscricoes))
                                <span class="text-xs font-bold text-red-500 uppercase flex items-center">
                                    <i class="fa-solid fa-lock mr-1.5"></i> Encerrado
                                </span>
                            @else
                                <span class="text-xs font-bold text-green-600 uppercase flex items-center">
                                    <i class="fa-solid fa-circle-check mr-1.5"></i> Inscrições Abertas
                                </span>
                            @endif

                            <a href="{{ route('eventos.public.show', $evento) }}" class="text-sm font-bold text-orange-600 hover:text-orange-800 transition-colors flex items-center group/link">
                                Detalhes <i class="fa-solid fa-arrow-right ml-1 transition-transform group-hover/link:translate-x-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fa-solid fa-calendar-xmark text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Nenhum evento encontrado</h3>
                    <p class="text-gray-500 mt-1">Tente ajustar os filtros de busca para encontrar o que procura.</p>
                    <a href="{{ route('eventos.public.index') }}" class="inline-block mt-4 text-orange-600 font-bold hover:underline">
                        Limpar todos os filtros
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Paginação (Protegida) --}}
        <div class="mt-12">
            @if(isset($eventos) && method_exists($eventos, 'links'))
                {{ $eventos->withQueryString()->links() }}
            @endif
        </div>
    </div>
@endsection