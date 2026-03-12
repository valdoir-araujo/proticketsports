@extends('layouts.public')

@section('title', 'Campeonatos - ' . config('app.name'))
@section('meta_description', 'Campeonatos esportivos em andamento. Acompanhe etapas, rankings e inscrições para corridas e outras modalidades.')
@section('canonical', url()->current())

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endpush

@section('content')
    {{-- Cabeçalho original --}}
    <header class="bg-gray-900 text-white py-16 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight">Campeonatos em Andamento</h1>
            <p class="text-lg mt-3 text-gray-300 max-w-2xl mx-auto">Acompanhe os campeonatos ativos, etapas e rankings.</p>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-4 md:p-8 min-h-screen">
        @php $listaCampeonatos = $campeonatos ?? collect(); @endphp

        @if(isset($listaCampeonatos) && count($listaCampeonatos) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($listaCampeonatos as $campeonato)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-orange-200 transition-all duration-300 flex flex-col group">
                        <a href="{{ route('campeonatos.show', $campeonato) }}" class="relative block overflow-hidden h-48 bg-slate-100">
                            <div class="absolute inset-0 bg-slate-900/5 group-hover:bg-transparent transition-colors z-10"></div>
                            @if($campeonato->logo_url)
                                <img class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
                                     src="{{ asset('storage/' . $campeonato->logo_url) }}"
                                     alt="Logo {{ $campeonato->nome }}"
                                     onerror="this.onerror=null; this.src='https://placehold.co/400x200/e2e8f0/64748b?text=Campeonato'; this.classList.add('object-cover'); this.classList.remove('object-contain');">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fa-solid fa-trophy text-6xl text-orange-400"></i>
                                </div>
                            @endif
                            <span class="absolute top-3 right-3 px-2 py-0.5 rounded bg-white/90 text-xs font-bold text-gray-700 shadow-sm">{{ $campeonato->ano }}</span>
                        </a>
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-lg font-bold text-slate-900 leading-tight mb-2 group-hover:text-orange-600 transition-colors">
                                <a href="{{ route('campeonatos.show', $campeonato) }}" class="block">{{ $campeonato->nome }}</a>
                            </h3>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="inline-block bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">Em andamento</span>
                                <span class="inline-block bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded">{{ $campeonato->eventos_count ?? 0 }} {{ ($campeonato->eventos_count ?? 0) == 1 ? 'etapa' : 'etapas' }}</span>
                            </div>
                            @if($campeonato->organizacao)
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                                    <i class="fa-solid fa-building text-orange-500 w-4"></i>
                                    <span class="truncate">{{ $campeonato->organizacao->nome }}</span>
                                </div>
                            @endif
                            <div class="mt-auto pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                                <a href="{{ route('campeonatos.show', $campeonato) }}" class="inline-flex items-center gap-2 text-sm font-bold text-orange-600 hover:text-orange-700 group/link">
                                    Ver etapas <i class="fa-solid fa-arrow-right group-hover/link:translate-x-1 transition-transform text-xs"></i>
                                </a>
                                <a href="{{ route('campeonatos.ranking', $campeonato) }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-600 hover:text-orange-600 transition-colors">
                                    <i class="fa-solid fa-medal text-amber-500"></i> Ranking geral
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @if(isset($campeonatos) && method_exists($campeonatos, 'links'))
            <div class="mt-12">{{ $campeonatos->links() }}</div>
        @endif
        @else
            <div class="col-span-full py-16 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <i class="fa-solid fa-trophy text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Nenhum campeonato em andamento</h3>
                <p class="text-gray-500 mt-1">No momento não há campeonatos ativos. Confira os eventos disponíveis.</p>
                <a href="{{ route('eventos.public.index') }}" class="inline-block mt-4 text-orange-600 font-bold hover:underline">
                    Ver eventos
                </a>
            </div>
        @endif
    </div>
@endsection
