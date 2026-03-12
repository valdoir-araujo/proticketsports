<x-app-layout>
    <x-slot name="title">Recibo - {{ $inscricao->evento->nome }} - {{ config('app.name') }}</x-slot>

    <div class="min-h-screen bg-slate-100 pb-8">
        {{-- Cabeçalho compacto --}}
        <div class="bg-white border-b border-slate-200 px-4 py-3 shadow-sm">
            <div class="max-w-lg mx-auto flex items-center justify-between">
                <a href="{{ route('inscricao.show', $inscricao) }}" class="text-slate-600 hover:text-slate-900 p-2 -ml-2">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <h1 class="text-lg font-bold text-slate-800">Recibo de Inscrição</h1>
                <span class="w-9"></span>
            </div>
        </div>

        <div class="max-w-lg mx-auto px-4 py-6 space-y-4">
            {{-- Card principal --}}
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                {{-- Status --}}
                <div class="bg-emerald-600 text-white px-4 py-3 text-center">
                    <p class="text-sm font-medium opacity-90">Confirmação de pagamento</p>
                    <p class="text-2xl font-black mt-0.5">{{ $inscricao->evento->nome }}</p>
                </div>

                <div class="p-4 space-y-4">
                    {{-- Código + QR Code (para check-in) --}}
                    <div class="flex flex-col sm:flex-row items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        @if($qrBase64)
                            <div class="flex-shrink-0 w-40 h-40 bg-white rounded-xl border-2 border-slate-200 p-2 flex items-center justify-center">
                                <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Code para check-in" class="w-full h-full object-contain">
                            </div>
                        @endif
                        <div class="flex-1 text-center sm:text-left">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Código da inscrição (check-in)</p>
                            <p class="text-xl font-mono font-bold text-slate-800 mt-1">{{ $inscricao->codigo_inscricao }}</p>
                            <p class="text-xs text-slate-500 mt-2">Apresente este QR Code ou código no check-in do evento.</p>
                        </div>
                    </div>

                    {{-- Dados do atleta --}}
                    <div>
                        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Atleta</h2>
                        <p class="font-semibold text-slate-800">{{ $inscricao->atleta->user->name ?? 'N/A' }}</p>
                        <p class="text-sm text-slate-600">{{ $inscricao->atleta->user->email ?? '' }}</p>
                    </div>

                    {{-- Percurso e categoria --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Percurso</h2>
                            <p class="font-medium text-slate-800">{{ optional(optional($inscricao->categoria)->percurso)->descricao ?? '—' }}</p>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Categoria</h2>
                            <p class="font-medium text-slate-800">{{ $inscricao->categoria->nome ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Equipe --}}
                    <div>
                        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Equipe</h2>
                        <p class="font-medium text-slate-800">{{ $inscricao->equipe->nome ?? 'Individual' }}</p>
                    </div>

                    {{-- Corrida: ritmo e pelotão --}}
                    @if($inscricao->evento->isCorrida() && ($inscricao->ritmo_previsto || $inscricao->pelotao_largada))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if($inscricao->ritmo_previsto)
                                <div>
                                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Ritmo previsto</h2>
                                    <p class="font-medium text-slate-800">{{ $inscricao->ritmo_previsto }} min/km</p>
                                </div>
                            @endif
                            @if($inscricao->pelotao_largada)
                                <div>
                                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Pelotão largada</h2>
                                    <p class="font-medium text-slate-800">{{ $inscricao->pelotao_largada }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Produtos comprados --}}
                    @if($inscricao->produtosOpcionais->isNotEmpty())
                        <div>
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Produtos</h2>
                            <ul class="space-y-1">
                                @foreach($inscricao->produtosOpcionais as $p)
                                    <li class="flex justify-between text-sm">
                                        <span class="text-slate-700">{{ $p->nome }}{!! $p->pivot->tamanho ? ' <span class="text-slate-500">(' . e($p->pivot->tamanho) . ')</span>' : '' !!}</span>
                                        <span class="font-medium text-slate-800">x{{ $p->pivot->quantidade ?? 1 }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Valor e data --}}
                    <div class="pt-4 border-t border-slate-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Data do pagamento</span>
                            <span class="font-medium text-slate-800">{{ $inscricao->data_pagamento ? $inscricao->data_pagamento->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-slate-600 font-semibold">Valor pago</span>
                            <span class="text-xl font-bold text-emerald-700">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-slate-500">Documento gerado em {{ now()->format('d/m/Y H:i') }} · {{ config('app.name') }}</p>
        </div>
    </div>
</x-app-layout>
