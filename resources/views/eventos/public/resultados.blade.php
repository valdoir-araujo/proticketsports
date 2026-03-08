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

                    {{-- Conteúdo: Atletas — cards por categoria, visual limpo --}}
                    <div x-show="tab === 'atletas'" x-cloak
                         class="bg-slate-50/50 rounded-b-2xl shadow-lg border border-slate-200 border-t-0 overflow-hidden">
                        <div class="px-6 lg:px-8 py-5 border-b border-slate-200 bg-white">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-stopwatch text-orange-500"></i> Classificação por categoria
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">Encontre sua categoria e confira sua posição e tempo.</p>
                        </div>
                        <div class="p-6 lg:p-8 space-y-8">
                            @forelse($inscricoesAgrupadas as $nomePercurso => $generos)
                                {{-- Percurso (ex: Pro, Sport) --}}
                                <section>
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-500 text-white">
                                            <i class="fa-solid fa-route text-lg"></i>
                                        </span>
                                        <h3 class="text-xl font-bold text-slate-800">{{ $nomePercurso ?: 'Geral' }}</h3>
                                    </div>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        @foreach($generos as $nomeGenero => $categorias)
                                            @foreach($categorias as $nomeCategoria => $inscritosNaCategoria)
                                                {{-- Card por categoria (ex: Elite, Master A2) --}}
                                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                                    <div class="px-5 py-3 bg-white border-b border-slate-100 flex items-center justify-between">
                                                        <span class="font-bold text-slate-800">{{ $nomeCategoria }}</span>
                                                        <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">{{ $inscritosNaCategoria->count() }} {{ $inscritosNaCategoria->count() === 1 ? 'atleta' : 'atletas' }}</span>
                                                    </div>
                                                    <ul class="divide-y divide-slate-100">
                                                        @foreach($inscritosNaCategoria->sortBy('resultado.posicao_categoria') as $inscricao)
                                                            @php
                                                                $pos = $inscricao->resultado?->posicao_categoria;
                                                                $posClass = $pos === 1 ? 'bg-amber-400 text-white' : ($pos === 2 ? 'bg-slate-400 text-white' : ($pos === 3 ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700'));
                                                            @endphp
                                                            <li class="px-5 py-4 hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                                                                <div class="flex items-center gap-4 min-w-0 flex-1">
                                                                    <span class="flex-shrink-0 w-10 h-10 rounded-xl {{ $posClass }} flex items-center justify-center text-sm font-bold">
                                                                        @if($pos){{ $pos }}º@else—@endif
                                                                    </span>
                                                                    <div class="min-w-0">
                                                                        <div class="font-bold text-slate-800 truncate">{{ $inscricao->atleta->user->name ?? '—' }}</div>
                                                                        @if($inscricao->numero_atleta)
                                                                            <div class="text-xs text-slate-500 font-mono">Nº {{ $inscricao->numero_atleta }}</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="grid grid-cols-3 gap-3 sm:flex sm:items-center sm:gap-6 text-sm flex-shrink-0 border-t border-slate-100 pt-3 sm:border-0 sm:pt-0">
                                                                    <div>
                                                                        <div class="text-xs text-slate-400 uppercase tracking-wide">Tempo</div>
                                                                        <div class="font-mono font-semibold text-slate-700">{{ $inscricao->resultado?->tempo_formatado ?? '—' }}</div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs text-slate-400 uppercase tracking-wide">Status</div>
                                                                        <div class="font-medium">
                                                                            @if($inscricao->resultado)
                                                                                @switch($inscricao->resultado->status_corrida)
                                                                                    @case('completou')<span class="text-emerald-600">Completou</span>@break
                                                                                    @case('nao_completou')<span class="text-slate-600">Não completou</span>@break
                                                                                    @case('nao_iniciada')<span class="text-slate-500">Não iniciada</span>@break
                                                                                    @case('desqualificado')<span class="text-red-600">Desqualificado</span>@break
                                                                                    @default<span>{{ $inscricao->resultado->status_corrida ?? '—' }}</span>@break
                                                                                @endswitch
                                                                            @else
                                                                                <span class="text-slate-400">—</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-xs text-slate-400 uppercase tracking-wide">Pontos</div>
                                                                        @if($inscricao->resultado?->pontos_etapa !== null)
                                                                            <div class="font-bold text-orange-600">{{ $inscricao->resultado->pontos_etapa }}</div>
                                                                        @else
                                                                            <div class="text-slate-400">—</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </section>
                            @empty
                                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
                                    Nenhum resultado por atleta nesta etapa.
                                </div>
                            @endforelse
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
