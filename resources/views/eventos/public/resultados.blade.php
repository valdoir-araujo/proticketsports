@extends('layouts.public')

@section('title', 'Ranking e resultados - ' . $evento->nome)

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    {{-- Cabeçalho (largura alinhada ao layout: max-w-7xl) --}}
    <section class="relative bg-cover bg-center py-8 md:py-12">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $evento->banner_url ? asset('storage/' . $evento->banner_url) : 'https://images.unsplash.com/photo-1573521193826-58c7a24275a7?q=80&w=1974&auto=format&fit=crop' }}');"></div>
        <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-orange-400 font-semibold text-xs md:text-sm uppercase tracking-wider">Ranking da etapa</p>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white mt-1 leading-tight">{{ $evento->nome }}</h1>
            <nav class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('eventos.public.show', $evento) }}" class="inline-flex items-center gap-x-1.5 text-slate-300 hover:text-white transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Voltar ao evento</span>
                </a>
                @if($evento->campeonato)
                    <span class="text-slate-500">·</span>
                    <a href="{{ route('campeonatos.show', $evento->campeonato) }}" class="text-slate-300 hover:text-white transition-colors">Ver campeonato</a>
                @endif
            </nav>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
        <div class="w-full">
            @php
                $inscricoesAgrupadas = $inscricoes->groupBy(['categoria.percurso.descricao', 'categoria.genero', 'categoria.nome']);
                $temEquipes = $rankingEquipesEtapa->isNotEmpty();
            @endphp

            @if($inscricoes->isNotEmpty() || $temEquipes)
                {{-- Abas: Atletas / Equipes (grid moderno) --}}
                <div x-data="{ tab: 'atletas' }" class="space-y-0">
                    <div class="flex gap-0 bg-white rounded-t-2xl overflow-hidden shadow-lg border border-slate-200 border-b-0">
                        <button type="button"
                                @click="tab = 'atletas'"
                                :class="tab === 'atletas' ? 'bg-orange-500 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                class="flex-1 px-6 py-4 text-sm font-bold transition-all rounded-tl-2xl">
                            <i class="fa-solid fa-person-running mr-2"></i> Por atleta
                        </button>
                        @if($temEquipes)
                            <button type="button"
                                    @click="tab = 'equipes'"
                                    :class="tab === 'equipes' ? 'bg-orange-500 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                    class="flex-1 px-6 py-4 text-sm font-bold transition-all rounded-tr-2xl">
                                <i class="fa-solid fa-people-group mr-2"></i> Por equipe
                            </button>
                        @endif
                    </div>

                    {{-- Conteúdo: Atletas --}}
                    <div x-show="tab === 'atletas'" x-cloak
                         class="bg-white rounded-b-2xl shadow-lg border border-slate-200 border-t-0 overflow-hidden">
                        <div class="px-6 lg:px-8 py-4 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-stopwatch text-orange-500"></i> Classificação por categoria
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-6 lg:px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Atleta / Categoria</th>
                                        <th class="px-6 lg:px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-36">Tempo</th>
                                        <th class="px-6 lg:px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-28">Status</th>
                                        <th class="px-6 lg:px-8 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-20">Pos.</th>
                                        <th class="px-6 lg:px-8 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Pontos</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @forelse($inscricoesAgrupadas as $nomePercurso => $generos)
                                        <tr class="bg-slate-700 text-white">
                                            <td colspan="5" class="px-6 lg:px-8 py-3 text-left text-sm font-bold flex items-center gap-2">
                                                <i class="fa-solid fa-route text-orange-400"></i> {{ $nomePercurso ?: 'N/A' }}
                                            </td>
                                        </tr>
                                        @foreach($generos as $nomeGenero => $categorias)
                                            @foreach($categorias as $nomeCategoria => $inscritosNaCategoria)
                                                <tr class="bg-slate-100/80 border-y border-slate-200">
                                                    <td colspan="5" class="px-6 lg:px-8 py-2.5 text-left text-sm font-bold text-slate-800 flex items-center gap-2">
                                                        <i class="fa-solid fa-layer-group text-orange-500"></i> {{ $nomeCategoria }}
                                                        <span class="text-xs font-normal text-slate-500 bg-white px-2.5 py-1 rounded-full border border-slate-200 shadow-sm">{{ $inscritosNaCategoria->count() }} atletas</span>
                                                    </td>
                                                </tr>
                                                @foreach($inscritosNaCategoria->sortBy('resultado.posicao_categoria') as $inscricao)
                                                    <tr class="hover:bg-orange-50/30 transition-colors">
                                                        <td class="pl-8 lg:pl-10 pr-6 lg:pr-8 py-3 whitespace-nowrap">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-sm font-bold shrink-0 ring-2 ring-white shadow">
                                                                    {{ $inscricao->atleta->iniciais ?? substr(optional($inscricao->atleta->user)->name ?? '?', 0, 2) }}
                                                                </div>
                                                                <div>
                                                                    <div class="text-sm font-bold text-slate-800">{{ $inscricao->atleta->user->name ?? '—' }}</div>
                                                                    @if($inscricao->numero_atleta)
                                                                        <div class="text-xs text-slate-500 font-mono">Num. {{ $inscricao->numero_atleta }}</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 lg:px-8 py-3 text-sm font-mono font-medium text-slate-700">
                                                            {{ $inscricao->resultado?->tempo_formatado ?? '—' }}
                                                        </td>
                                                        <td class="px-6 lg:px-8 py-3 text-sm text-slate-700">
                                                            @if($inscricao->resultado)
                                                                @switch($inscricao->resultado->status_corrida)
                                                                    @case('completou') <span class="text-emerald-600 font-medium">Completou</span> @break
                                                                    @case('nao_completou') Não completou @break
                                                                    @case('nao_iniciada') Não iniciada @break
                                                                    @case('desqualificado') Desqualificado @break
                                                                    @default {{ $inscricao->resultado->status_corrida ?? '—' }}
                                                                @endswitch
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td class="px-6 lg:px-8 py-3 text-center">
                                                            @if($inscricao->resultado?->posicao_categoria)
                                                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-800 text-white text-sm font-bold shadow-md">
                                                                    {{ $inscricao->resultado->posicao_categoria }}º
                                                                </span>
                                                            @else
                                                                <span class="text-slate-300">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 lg:px-8 py-3 text-center">
                                                            @if($inscricao->resultado?->pontos_etapa !== null)
                                                                <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2 py-1 rounded-lg bg-orange-100 text-orange-700 font-bold text-sm">{{ $inscricao->resultado->pontos_etapa }}</span>
                                                            @else
                                                                <span class="text-slate-300">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 lg:px-8 py-12 text-center text-slate-500">
                                                Nenhum resultado por atleta nesta etapa.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Conteúdo: Equipes --}}
                    @if($temEquipes)
                        <div x-show="tab === 'equipes'" x-cloak
                             class="bg-white rounded-b-2xl shadow-lg border border-slate-200 border-t-0 overflow-hidden">
                            <div class="px-6 lg:px-8 py-4 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-people-group text-orange-500"></i> Classificação por equipes
                                </h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50/80">
                                        <tr>
                                            <th class="px-6 lg:px-8 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Pos.</th>
                                            <th class="px-6 lg:px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Equipe</th>
                                            <th class="px-6 lg:px-8 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-32">Pontos</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        @foreach($rankingEquipesEtapa as $pos => $dadosEquipe)
                                            <tr class="hover:bg-orange-50/30 transition-colors">
                                                <td class="px-6 lg:px-8 py-4 text-center">
                                                    @if($pos + 1 <= 3)
                                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-white font-bold shadow-md
                                                            {{ $pos === 0 ? 'bg-amber-400' : ($pos === 1 ? 'bg-slate-400' : 'bg-orange-400') }}">
                                                            {{ $pos + 1 }}º
                                                        </span>
                                                    @else
                                                        <span class="text-sm font-bold text-slate-600">{{ $pos + 1 }}º</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 lg:px-8 py-4 text-sm font-bold text-slate-800">{{ $dadosEquipe['equipe']->nome }}</td>
                                                <td class="px-6 lg:px-8 py-4 text-center">
                                                    <span class="inline-flex items-center justify-center min-w-[2.5rem] px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg text-sm font-bold">
                                                        {{ $dadosEquipe['pontos_totais'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-12 md:p-16 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-5 text-slate-400">
                        <i class="fa-solid fa-stopwatch text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-700">Resultados indisponíveis</h3>
                    <p class="text-slate-500 mt-2 max-w-md mx-auto">Ainda não há resultados publicados para esta etapa.</p>
                    <a href="{{ route('eventos.public.show', $evento) }}" class="mt-6 inline-flex items-center gap-2 text-orange-600 hover:text-orange-700 font-semibold text-sm">
                        <i class="fa-solid fa-arrow-left"></i> Voltar ao evento
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
