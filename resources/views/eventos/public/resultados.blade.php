@extends('layouts.public')

@section('title', 'Resultados - ' . $evento->nome)

@section('content')
    {{-- Cabeçalho --}}
    <section class="relative bg-cover bg-center py-6 md:py-12">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $evento->banner_url ? asset('storage/' . $evento->banner_url) : 'https://images.unsplash.com/photo-1573521193826-58c7a24275a7?q=80&w=1974&auto=format&fit=crop' }}');"></div>
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>
        <div class="container relative mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8 items-center">
                    <div class="md:col-span-2 text-center md:text-left">
                        <p class="text-orange-400 font-semibold text-xs md:text-sm uppercase tracking-wider">Resultados da prova</p>
                        <h1 class="text-xl md:text-3xl font-extrabold text-white mt-1 leading-tight">{{ $evento->nome }}</h1>
                        <a href="{{ route('eventos.public.show', $evento) }}" class="mt-2 md:mt-3 inline-flex items-center gap-x-2 text-xs md:text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Voltar para a página do evento</span>
                        </a>
                    </div>
                    @if($evento->data_evento)
                        <div class="md:col-span-1 flex justify-center md:justify-end">
                            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 md:p-4 flex items-center gap-x-4 text-white text-center">
                                <div>
                                    <span class="text-2xl md:text-3xl font-bold">{{ $evento->data_evento->format('d') }}</span>
                                    <p class="text-[10px] md:text-xs uppercase tracking-wider text-slate-300">{{ $evento->data_evento->translatedFormat('M') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            @php
                $inscricoesAgrupadas = $inscricoes->groupBy(['categoria.percurso.descricao', 'categoria.genero', 'categoria.nome']);
            @endphp

            @if($inscricoes->isNotEmpty())
                {{-- Resultado individual por categoria --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                    <div class="px-4 md:px-6 py-4 bg-slate-50 border-b border-slate-200">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-stopwatch text-orange-500"></i> Classificação por categoria
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Atleta / Categoria</th>
                                    <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-36">Tempo</th>
                                    <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-28">Status</th>
                                    <th class="px-4 md:px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-20">Pos.</th>
                                    <th class="px-4 md:px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-20">Pontos</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse($inscricoesAgrupadas as $nomePercurso => $generos)
                                    <tr class="bg-slate-700 text-white">
                                        <td colspan="5" class="px-4 md:px-6 py-2 text-left text-sm font-bold flex items-center gap-2">
                                            <i class="fa-solid fa-route text-orange-400"></i> {{ $nomePercurso ?: 'N/A' }}
                                        </td>
                                    </tr>
                                    @foreach($generos as $nomeGenero => $categorias)
                                        @foreach($categorias as $nomeCategoria => $inscritosNaCategoria)
                                            <tr class="bg-indigo-50 border-y border-indigo-100">
                                                <td colspan="5" class="px-4 md:px-6 py-2 text-left text-sm font-bold text-indigo-800 pl-6 md:pl-10 flex items-center gap-2">
                                                    <i class="fa-solid fa-layer-group text-indigo-500"></i> {{ $nomeCategoria }}
                                                    <span class="text-xs font-normal text-indigo-500 bg-white px-2 py-0.5 rounded-full border border-indigo-200 ml-2">{{ $inscritosNaCategoria->count() }} atletas</span>
                                                </td>
                                            </tr>
                                            @foreach($inscritosNaCategoria->sortBy('resultado.posicao_categoria') as $inscricao)
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="pl-6 md:pl-10 pr-4 md:pr-6 py-3 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-xs font-bold mr-3 shrink-0">
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
                                                    <td class="px-4 md:px-6 py-3 text-sm font-mono font-medium text-slate-700">
                                                        {{ $inscricao->resultado?->tempo_formatado ?? '—' }}
                                                    </td>
                                                    <td class="px-4 md:px-6 py-3 text-sm text-slate-700">
                                                        @if($inscricao->resultado)
                                                            @switch($inscricao->resultado->status_corrida)
                                                                @case('completou') Completou @break
                                                                @case('nao_completou') Não completou @break
                                                                @case('nao_iniciada') Não iniciada @break
                                                                @case('desqualificado') Desqualificado @break
                                                                @default {{ $inscricao->resultado->status_corrida ?? '—' }}
                                                            @endswitch
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="px-4 md:px-6 py-3 text-center">
                                                        @if($inscricao->resultado?->posicao_categoria)
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-800 text-white text-sm font-bold shadow-sm">
                                                                {{ $inscricao->resultado->posicao_categoria }}º
                                                            </span>
                                                        @else
                                                            <span class="text-slate-300">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 md:px-6 py-3 text-center">
                                                        @if($inscricao->resultado?->pontos_etapa !== null)
                                                            <span class="font-bold text-indigo-600">{{ $inscricao->resultado->pontos_etapa }}</span>
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
                                        <td colspan="5" class="px-4 md:px-6 py-12 text-center text-slate-500">
                                            Nenhum inscrito confirmado com resultado para este evento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Ranking por equipes (se houver) --}}
                @if($rankingEquipesEtapa->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-4 md:px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-people-group text-indigo-500"></i> Classificação por equipes
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 md:px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-20">Pos.</th>
                                        <th class="px-4 md:px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Equipe</th>
                                        <th class="px-4 md:px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-28">Pontos</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($rankingEquipesEtapa as $pos => $dadosEquipe)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 md:px-6 py-3 text-center">
                                                @if($pos + 1 <= 3)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold shadow-md
                                                        {{ $pos === 0 ? 'bg-amber-400' : ($pos === 1 ? 'bg-slate-400' : 'bg-orange-400') }}">
                                                        {{ $pos + 1 }}º
                                                    </span>
                                                @else
                                                    <span class="text-sm font-bold text-slate-600">{{ $pos + 1 }}º</span>
                                                @endif
                                            </td>
                                            <td class="px-4 md:px-6 py-3 text-sm font-bold text-slate-800">{{ $dadosEquipe['equipe']->nome }}</td>
                                            <td class="px-4 md:px-6 py-3 text-center">
                                                <span class="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-bold">
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
            @else
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <i class="fa-solid fa-stopwatch text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700">Resultados indisponíveis</h3>
                    <p class="text-slate-500 mt-1 max-w-md mx-auto">Ainda não há resultados publicados para esta prova. Volte à página do evento para mais informações.</p>
                    <a href="{{ route('eventos.public.show', $evento) }}" class="mt-4 inline-flex items-center gap-2 text-orange-600 hover:text-orange-700 font-semibold text-sm">
                        <i class="fa-solid fa-arrow-left"></i> Voltar ao evento
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
