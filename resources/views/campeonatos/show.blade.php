@extends('layouts.public')

@section('title', $campeonato->nome . ' - Proticketsports')

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endpush

@section('content')
    {{-- Cabeçalho --}}
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

    <div class="max-w-7xl mx-auto p-4 md:p-8 min-h-screen">

        {{-- Etapas do campeonato --}}
        <section class="mb-12">
            <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-flag-checkered text-orange-500"></i>
                Etapas ({{ $etapas->count() }})
            </h2>

            @if($etapas->isNotEmpty())
                <div class="space-y-4">
                    @foreach($etapas as $evento)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5 flex flex-col md:flex-row md:items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-900 text-lg">
                                    @if($evento->status === 'publicado')
                                        <a href="{{ route('eventos.public.show', $evento) }}" class="hover:text-orange-600 transition-colors">{{ $evento->nome }}</a>
                                    @else
                                        {{ $evento->nome }}
                                    @endif
                                </h3>
                                <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-600">
                                    @if($evento->data_evento)
                                        <span><i class="fa-regular fa-calendar mr-1 text-orange-500"></i> {{ $evento->data_evento->format('d/m/Y') }} {{ $evento->data_evento->format('H:i') }}</span>
                                    @endif
                                    @if($evento->cidade)
                                        <span><i class="fa-solid fa-location-dot mr-1 text-orange-500"></i> {{ $evento->cidade->nome }}{{ $evento->cidade->estado ? ' - ' . $evento->cidade->estado->uf : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($evento->status === 'publicado')
                                <a href="{{ route('eventos.public.show', $evento) }}" class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg text-sm transition-colors shrink-0">
                                    Ver evento <i class="fa-solid fa-arrow-right ml-2"></i>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 py-10 text-center text-gray-500">
                    <i class="fa-solid fa-flag text-3xl mb-2"></i>
                    <p>Nenhuma etapa cadastrada para este campeonato.</p>
                </div>
            @endif
        </section>

        {{-- Ranking (placeholders - podem ser preenchidos depois pela lógica real) --}}
        @if($rankingAtletas->isNotEmpty() || $rankingEquipes->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @if($rankingAtletas->isNotEmpty())
                    <section>
                        <h2 class="text-xl font-black text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-medal text-amber-500"></i>
                            Ranking – Atletas
                        </h2>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <ul class="divide-y divide-gray-100">
                                @foreach($rankingAtletas->take(10) as $idx => $item)
                                    <li class="px-4 py-3 flex items-center justify-between">
                                        <span class="font-bold text-slate-500 w-8">{{ $idx + 1 }}º</span>
                                        <span class="flex-1 font-medium text-slate-900">{{ $item->nome_atleta ?? $item->name ?? '—' }}</span>
                                        <span class="font-bold text-orange-600">{{ $item->total_pontos ?? 0 }} pts</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                @endif
                @if($rankingEquipes->isNotEmpty())
                    <section>
                        <h2 class="text-xl font-black text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-users text-blue-500"></i>
                            Ranking – Equipes
                        </h2>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <ul class="divide-y divide-gray-100">
                                @foreach($rankingEquipes->take(10) as $idx => $item)
                                    <li class="px-4 py-3 flex items-center justify-between">
                                        <span class="font-bold text-slate-500 w-8">{{ $idx + 1 }}º</span>
                                        <span class="flex-1 font-medium text-slate-900">{{ $item->nome_equipe ?? $item->nome ?? '—' }}</span>
                                        <span class="font-bold text-orange-600">{{ $item->total_pontos ?? 0 }} pts</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                @endif
            </div>
        @endif

        {{-- Regulamento --}}
        @if($campeonato->regulamento_url)
            <section class="mt-10 pt-8 border-t border-gray-200">
                <a href="{{ asset('storage/' . $campeonato->regulamento_url) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-orange-600 font-bold hover:text-orange-800 transition-colors">
                    <i class="fa-solid fa-file-lines"></i>
                    Baixar regulamento
                </a>
            </section>
        @endif

        <div class="mt-10">
            <a href="{{ route('campeonatos.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-orange-600 font-bold transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Voltar aos campeonatos
            </a>
        </div>
    </div>
@endsection
