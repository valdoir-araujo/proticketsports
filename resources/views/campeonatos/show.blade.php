@extends('layouts.public')

@section('title', $campeonato->nome . ' - Proticketsports')

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    {{-- Cabeçalho original --}}
    <header class="bg-gray-900 text-white py-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 flex flex-col md:flex-row md:items-center gap-6">
            @if($campeonato->logo_url)
                <div class="flex-shrink-0">
                    <img src="{{ asset('storage/' . $campeonato->logo_url) }}" alt="{{ $campeonato->nome }}" class="h-24 w-auto object-contain bg-white/10 rounded-lg p-2" onerror="this.style.display='none'">
                </div>
            @endif
            <div>
                <nav class="text-sm text-gray-400 mb-2">
                    <a href="{{ route('campeonatos.index') }}" class="hover:text-orange-400 transition-colors">Campeonatos</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">{{ $campeonato->nome }}</span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight">{{ $campeonato->nome }}</h1>
                <p class="text-gray-300 mt-1">{{ $campeonato->ano }} @if($campeonato->organizacao)· {{ $campeonato->organizacao->nome }}@endif</p>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-4 md:p-8 min-h-screen space-y-10">

        @php
            $etapasRealizadas = $etapas->filter(fn($e) => $e->data_evento && $e->data_evento->isPast());
        @endphp

        {{-- Resumo do campeonato (sem link de ranking geral; ranking só dentro de cada card da etapa) --}}
        <div class="flex flex-wrap items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <span class="inline-flex items-center gap-2 text-slate-700 font-medium">
                <i class="fa-solid fa-flag-checkered text-orange-500"></i>
                <strong>{{ $etapas->count() }}</strong> {{ $etapas->count() === 1 ? 'etapa' : 'etapas' }}
            </span>
            @if($etapas->isNotEmpty())
                <span class="text-slate-500">·</span>
                <span class="text-slate-600">
                    <strong>{{ $etapasRealizadas->count() }}</strong> {{ $etapasRealizadas->count() === 1 ? 'realizada' : 'realizadas' }}
                </span>
            @endif
        </div>

        {{-- Etapas --}}
        <section>
            <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-flag-checkered text-orange-500"></i>
                Etapas ({{ $etapas->count() }})
            </h2>

            @if($etapas->isNotEmpty())
                <div class="space-y-3">
                    @foreach($etapas as $index => $evento)
                        @php $jaRealizada = $evento->data_evento && $evento->data_evento->isPast(); @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5 flex flex-col md:flex-row md:items-center gap-4 hover:shadow-md hover:border-orange-200 transition-all group {{ $index % 2 === 0 ? '' : 'bg-slate-50/50' }}">
                            <div class="flex-shrink-0 w-20 h-20 md:w-24 md:h-24 rounded-lg bg-gray-100 overflow-hidden border border-gray-200 flex items-center justify-center">
                                @if($evento->thumbnail_url ?? $evento->banner_url)
                                    <img src="{{ asset('storage/' . ($evento->thumbnail_url ?? $evento->banner_url)) }}" alt="{{ $evento->nome }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                    <i class="fa-solid fa-flag text-2xl text-orange-400 hidden"></i>
                                @else
                                    <i class="fa-solid fa-flag text-2xl text-orange-400"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    @if($jaRealizada)
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-slate-200 text-slate-700">Realizada</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-700">Em breve</span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-slate-900 text-lg group-hover:text-orange-600 transition-colors">
                                    @if($evento->status === 'publicado')
                                        <a href="{{ route('eventos.public.show', $evento) }}" class="hover:text-orange-600">{{ $evento->nome }}</a>
                                    @else
                                        {{ $evento->nome }}
                                    @endif
                                </h3>
                                <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-600">
                                    @if($evento->data_evento)
                                        <span><i class="fa-regular fa-calendar mr-1.5 text-orange-500"></i>{{ $evento->data_evento->format('d/m/Y') }} às {{ $evento->data_evento->format('H:i') }}</span>
                                    @endif
                                    @if($evento->cidade)
                                        <span><i class="fa-solid fa-location-dot mr-1.5 text-orange-500"></i>{{ $evento->cidade->nome }}{{ $evento->cidade->estado ? ' - ' . $evento->cidade->estado->uf : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($evento->status === 'publicado')
                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    @if($jaRealizada)
                                        <a href="{{ route('eventos.public.resultados', $evento) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg text-sm transition-colors">
                                            <i class="fa-solid fa-medal"></i> Ver ranking da etapa
                                        </a>
                                    @else
                                        <a href="{{ route('eventos.public.inscritos', $evento) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-slate-300 text-slate-700 font-medium rounded-lg text-sm hover:bg-slate-50 transition-colors">
                                            <i class="fa-solid fa-list"></i> Inscritos
                                        </a>
                                        <a href="{{ route('eventos.public.show', $evento) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-300 text-slate-700 font-bold rounded-lg text-sm hover:bg-slate-50 transition-colors">
                                            Ver evento <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 py-10 text-center text-gray-500">
                    <i class="fa-solid fa-flag text-3xl mb-2 text-gray-300"></i>
                    <p>Nenhuma etapa cadastrada para este campeonato.</p>
                </div>
            @endif
        </section>

        {{-- Regulamento --}}
        @if($campeonato->regulamento_url)
            <section class="pt-8 border-t border-gray-200">
                <a href="{{ asset('storage/' . $campeonato->regulamento_url) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-orange-600 font-bold hover:text-orange-800 transition-colors">
                    <i class="fa-solid fa-file-lines"></i>
                    Baixar regulamento
                </a>
            </section>
        @endif

        <div>
            <a href="{{ route('campeonatos.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-orange-600 font-bold transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Voltar aos campeonatos
            </a>
        </div>
    </div>
@endsection
