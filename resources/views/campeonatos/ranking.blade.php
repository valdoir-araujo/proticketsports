@extends('layouts.public')

@section('title', 'Ranking - ' . $campeonato->nome . ' - Proticketsports')

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .rank-1 { color: #d4af37; }
        .rank-2 { color: #c0c0c0; }
        .rank-3 { color: #cd7f32; }
        .tab-active { border-bottom: 2px solid #ea580c; color: #ea580c; }
    </style>
@endpush

@section('content')
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
                    <a href="{{ route('campeonatos.show', $campeonato) }}" class="hover:text-orange-400 transition-colors">{{ $campeonato->nome }}</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Ranking</span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight">Ranking do Campeonato</h1>
                <p class="text-gray-300 mt-1">{{ $campeonato->nome }} · {{ $campeonato->ano }} @if($campeonato->organizacao)· {{ $campeonato->organizacao->nome }}@endif</p>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-4 md:p-8 min-h-screen" x-data="{ tab: 'atletas' }">
        <p class="text-gray-600 mb-6">Pontuação por etapa e total do campeonato.</p>

        {{-- Menu Atletas / Equipes --}}
        <div class="flex border-b border-gray-200 mb-6">
            <button type="button"
                    @click="tab = 'atletas'"
                    :class="tab === 'atletas' ? 'tab-active font-bold text-orange-600' : 'text-gray-600 hover:text-gray-900'"
                    class="px-6 py-3 flex items-center gap-2 transition-colors border-b-2 border-transparent -mb-px">
                <i class="fa-solid fa-medal text-amber-500"></i>
                Atletas
            </button>
            <button type="button"
                    @click="tab = 'equipes'"
                    :class="tab === 'equipes' ? 'tab-active font-bold text-orange-600' : 'text-gray-600 hover:text-gray-900'"
                    class="px-6 py-3 flex items-center gap-2 transition-colors border-b-2 border-transparent -mb-px">
                <i class="fa-solid fa-users text-blue-500"></i>
                Equipes
            </button>
        </div>

        {{-- Aba Atletas --}}
        <div x-show="tab === 'atletas'" x-cloak>
            @if($rankingAtletas->isNotEmpty())
                <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-100">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-12">#</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Atleta</th>
                                @foreach($etapas as $etapa)
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center whitespace-nowrap">{{ Str::limit($etapa->nome, 15) }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-xs font-bold text-orange-600 uppercase tracking-wider text-right whitespace-nowrap">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rankingAtletas as $idx => $item)
                                @php
                                    $medalClass = $idx < 3 ? 'rank-' . ($idx + 1) : 'text-gray-700';
                                    $pontosEtapas = $pontosAtletaPorEtapa[$item->atleta_id] ?? collect();
                                @endphp
                                <tr class="hover:bg-orange-50/30 transition-colors">
                                    <td class="px-4 py-3 font-black {{ $medalClass }}">{{ $idx + 1 }}º</td>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $item->nome_atleta ?? '—' }}</td>
                                    @foreach($etapas as $etapa)
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ $pontosEtapas->get($etapa->id) ?? '—' }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-right font-bold text-orange-600">{{ (int) $item->total_pontos }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 py-12 text-center text-gray-500">
                    <i class="fa-solid fa-medal text-4xl text-gray-300 mb-3"></i>
                    <p class="font-medium">Nenhum resultado lançado ainda para atletas.</p>
                </div>
            @endif
        </div>

        {{-- Aba Equipes --}}
        <div x-show="tab === 'equipes'" x-cloak>
            @if($rankingEquipes->isNotEmpty())
                <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-100">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-12">#</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Equipe</th>
                                @foreach($etapas as $etapa)
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center whitespace-nowrap">{{ Str::limit($etapa->nome, 15) }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-xs font-bold text-orange-600 uppercase tracking-wider text-right whitespace-nowrap">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rankingEquipes as $idx => $item)
                                @php
                                    $medalClass = $idx < 3 ? 'rank-' . ($idx + 1) : 'text-gray-700';
                                    $pontosEtapas = $pontosEquipePorEtapa[$item->equipe_id] ?? collect();
                                @endphp
                                <tr class="hover:bg-orange-50/30 transition-colors">
                                    <td class="px-4 py-3 font-black {{ $medalClass }}">{{ $idx + 1 }}º</td>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $item->nome_equipe ?? '—' }}</td>
                                    @foreach($etapas as $etapa)
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ $pontosEtapas->get($etapa->id) ?? '—' }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-right font-bold text-orange-600">{{ (int) $item->total_pontos }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 py-12 text-center text-gray-500">
                    <i class="fa-solid fa-users text-4xl text-gray-300 mb-3"></i>
                    <p class="font-medium">Nenhum resultado lançado ainda para equipes.</p>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap gap-4 mt-8">
            <a href="{{ route('campeonatos.show', $campeonato) }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-orange-600 font-bold transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao campeonato
            </a>
            <a href="{{ route('campeonatos.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-orange-600 font-bold transition-colors">
                <i class="fa-solid fa-trophy"></i> Todos os campeonatos
            </a>
        </div>
    </div>
@endsection
