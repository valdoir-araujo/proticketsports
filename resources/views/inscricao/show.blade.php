<x-app-layout>
    <x-slot name="title">Resumo da Inscrição - {{ config('app.name') }}</x-slot>

    @if($inscricao->status === 'confirmada')
        {{-- CABEÇALHO HERO (Confirmada) --}}
        <div class="relative bg-gradient-to-br from-slate-900 via-green-900/90 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
            <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#22c55e 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-green-600/20 blur-3xl pointer-events-none mix-blend-screen"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-white z-10 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold text-green-200 justify-center md:justify-start">
                            <i class="fa-solid fa-check-double"></i> Inscrição Confirmada
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">Inscrição Confirmada!</h1>
                        <p class="text-green-100 mt-2 text-lg font-light opacity-90">Parabéns, {{ $inscricao->atleta->user->name }}! Sua vaga no evento está garantida.</p>
                    </div>
                    <div class="z-10">
                        <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white text-sm font-bold transition-all">
                            <i class="fa-solid fa-list mr-2"></i> Minhas Inscrições
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-20 -mt-20 pb-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center"><i class="fa-solid fa-receipt"></i></span>
                                    Recibo da Inscrição
                                </h2>
                            </div>
                            <div class="p-6 space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-slate-500">Evento:</span><span class="font-medium text-slate-800 text-right">{{ $inscricao->evento->nome }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Categoria:</span><span class="font-medium text-slate-800">{{ $inscricao->categoria->nome }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Código:</span><span class="font-medium text-slate-800 font-mono">{{ $inscricao->codigo_inscricao }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Data do Pagamento:</span><span class="font-medium text-slate-800">{{ $inscricao->data_pagamento ? \Carbon\Carbon::parse($inscricao->data_pagamento)->format('d/m/Y \à\s H:i') : 'N/A' }}</span></div>
                                <div class="flex justify-between border-t pt-3 mt-3"><span class="text-slate-500 font-bold">Valor Pago:</span><span class="font-bold text-lg text-green-700">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span></div>
                                <div class="flex justify-between items-center"><span class="text-slate-500">Status:</span><span class="px-3 py-1 text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><i class="fa-solid fa-check mr-1"></i> Confirmada</span></div>
                            </div>
                        </div>
                        @if($inscricao->resultado)
                            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                        <i class="fa-solid fa-stopwatch text-indigo-600"></i> Seu resultado neste evento
                                    </h2>
                                </div>
                                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div><span class="text-slate-500 block">Tempo</span><span class="font-mono font-bold text-slate-800">{{ $inscricao->resultado->tempo_formatado ?? '—' }}</span></div>
                                    <div><span class="text-slate-500 block">Status da corrida</span><span class="font-medium text-slate-800">
                                        @switch($inscricao->resultado->status_corrida)
                                            @case('completou') Completou @break
                                            @case('nao_completou') Não completou @break
                                            @case('nao_iniciada') Não iniciada @break
                                            @case('desqualificado') Desqualificado @break
                                            @default {{ $inscricao->resultado->status_corrida ?? '—' }}
                                        @endswitch
                                    </span></div>
                                    @if($inscricao->resultado->posicao_categoria)
                                        <div><span class="text-slate-500 block">Posição na categoria</span><span class="font-bold text-slate-800">{{ $inscricao->resultado->posicao_categoria }}º</span></div>
                                    @endif
                                    @if($inscricao->resultado->pontos_etapa !== null)
                                        <div><span class="text-slate-500 block">Pontos da etapa</span><span class="font-bold text-indigo-600">{{ $inscricao->resultado->pontos_etapa }}</span></div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="text-center">
                            <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-lg">
                                <i class="fa-solid fa-list"></i> Ver Todas as Minhas Inscrições
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-1"></div>
                </div>
            </div>
        </div>

    @elseif($inscricao->status === 'aguardando_pagamento')
        {{-- CABEÇALHO HERO (Pré-inscrição) — mesmo padrão da página de pagamento --}}
        <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
            <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-white z-10 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold text-orange-200 justify-center md:justify-start">
                            <i class="fa-solid fa-hourglass-half"></i> Pré-inscrição
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">Pré-inscrição Realizada!</h1>
                        <p class="text-blue-100 mt-2 text-lg font-light opacity-90">Obrigado, {{ $inscricao->atleta->user->name }}. Seu registro foi recebido. Falta apenas o pagamento para confirmar sua vaga.</p>
                    </div>
                    <div class="z-10">
                        <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white text-sm font-bold transition-all">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Minhas Inscrições
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-20 -mt-20 pb-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-lg">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i></div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-red-800">Atenção:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                @if (session('cupom_success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-xl">{{ session('cupom_success') }}</div>
                @endif
                @if (session('cupom_error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl">{{ session('cupom_error') }}</div>
                @endif
                @if (session('info'))
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-xl"><i class="fa-solid fa-info-circle text-blue-500 mr-2"></i>{{ session('info') }}</div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {{-- COLUNA 1: Conteúdo principal --}}
                    <div class="lg:col-span-2 order-1 lg:order-1 space-y-6">
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-file-lines"></i></span>
                                    Dados da Inscrição
                                </h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Evento</p>
                                        <p class="text-slate-800 font-semibold leading-snug">{{ $inscricao->evento->nome }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Categoria</p>
                                        <p class="text-slate-800 font-semibold">{{ $inscricao->categoria->nome }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Código da inscrição</p>
                                        <p class="text-slate-800 font-mono font-bold text-lg">{{ $inscricao->codigo_inscricao }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</p>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-amber-100 text-amber-800">
                                            <i class="fa-solid fa-clock mr-1.5"></i> Aguardando Pagamento
                                        </span>
                                    </div>
                                </div>

                                @if($inscricao->produtosOpcionais->isNotEmpty())
                                    <div class="flex gap-3 p-4 rounded-xl bg-amber-50 border border-amber-100 text-amber-800 text-sm mb-6">
                                        <i class="fa-solid fa-circle-info text-lg shrink-0 mt-0.5"></i>
                                        <p><strong>Importante:</strong> A reserva dos itens opcionais só será confirmada após a aprovação do pagamento. O estoque é limitado.</p>
                                    </div>
                                @endif

                                {{-- Cupom --}}
                                <div class="rounded-xl border border-slate-200 p-5 mb-6">
                                    <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                        <i class="fa-solid fa-tag text-slate-400"></i> Cupom de desconto
                                    </h3>
                                    @if($errors->has('cupom'))
                                        <div class="mb-3 p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ $errors->first('cupom') }}</div>
                                    @endif
                                    <form action="{{ route('inscricao.cupom.aplicar', $inscricao) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="codigo_cupom" placeholder="Digite o código do cupom" class="flex-1 rounded-lg border-slate-300 text-sm focus:border-orange-500 focus:ring-orange-500" required>
                                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition-colors">Aplicar</button>
                                    </form>
                                </div>

                                {{-- CTA Pagamento --}}
                                <div class="text-center pt-4 border-t border-slate-100">
                                    <a href="{{ route('pagamento.show', $inscricao) }}" class="inline-flex items-center justify-center gap-2 w-full md:w-auto px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-lg rounded-xl shadow-lg shadow-emerald-500/25 transition-all hover:-translate-y-0.5">
                                        <i class="fa-solid fa-lock"></i>
                                        <span>Ir para pagamento seguro</span>
                                    </a>
                                    <p class="text-slate-500 text-sm mt-3">Pagamento via PIX ou cartão de crédito</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- COLUNA 2: Resumo do valor (sticky, mesmo padrão da página de pagamento) --}}
                    <div class="lg:col-span-1 order-2 lg:order-2">
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden sticky top-6">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                        <i class="fa-solid fa-receipt"></i>
                                    </span>
                                    Resumo do valor
                                </h2>
                            </div>
                            <div class="p-6 space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-600">Valor da inscrição</span>
                                    <span class="font-medium text-slate-900">R$ {{ number_format($inscricao->valor_original, 2, ',', '.') }}</span>
                                </div>
                                @if($inscricao->produtosOpcionais->isNotEmpty())
                                    <div class="py-2">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Itens adicionais</p>
                                        <ul class="space-y-2">
                                            @foreach ($inscricao->produtosOpcionais as $produto)
                                                <li class="flex justify-between items-start text-xs">
                                                    <span class="text-slate-600">
                                                        {{ $produto->pivot->quantidade }}x {{ $produto->nome }}
                                                        @if($produto->pivot->tamanho) <span class="text-slate-400">({{ $produto->pivot->tamanho }})</span> @endif
                                                    </span>
                                                    <span class="font-medium text-slate-900">R$ {{ number_format($produto->pivot->valor_pago_por_item * $produto->pivot->quantidade, 2, ',', '.') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if($inscricao->cupom_id && $inscricao->valor_desconto > 0)
                                    <div class="flex justify-between text-sm text-green-600 bg-green-50 p-2 rounded-lg border border-green-100">
                                        <span class="flex items-center gap-1"><i class="fa-solid fa-tag"></i> Desconto ({{ $inscricao->cupom->codigo }})</span>
                                        <span class="font-bold">- R$ {{ number_format($inscricao->valor_desconto, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($inscricao->taxa_plataforma > 0)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-600">Taxa de serviço</span>
                                        <span class="font-medium text-slate-900">R$ {{ number_format($inscricao->taxa_plataforma, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="pt-4 border-t-2 border-slate-100 flex justify-between items-center px-6 pb-6">
                                <span class="text-sm font-bold text-slate-500 uppercase">Total a pagar</span>
                                <span class="text-3xl font-black text-indigo-700">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span>
                            </div>
                            <div class="bg-slate-50 p-4 text-xs text-slate-500 text-center border-t border-slate-100">
                                <i class="fa-solid fa-shield-halved text-green-500 mr-1"></i> Ambiente 100% Seguro
                            </div>
                        </div>
                    </div>
                </div>

                @if($inscricao->evento->eventoContatos->isNotEmpty())
                    <div class="mt-8 bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
                        <p class="text-sm font-bold text-slate-600 uppercase tracking-wide mb-3 flex items-center">
                            <i class="fa-solid fa-address-card mr-2 text-slate-500"></i> Contato do organizador
                        </p>
                        <ul class="space-y-2 text-sm text-slate-700">
                            @foreach($inscricao->evento->eventoContatos as $contato)
                                <li class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    @if($contato->nome)<span class="font-semibold">{{ $contato->nome }}</span>@endif
                                    @if($contato->cargo)<span class="text-slate-500">({{ $contato->cargo }})</span>@endif
                                    @if($contato->telefone)
                                        <a href="https://wa.me/55{{ preg_replace('/\D/', '', $contato->telefone) }}" target="_blank" rel="noopener" class="text-teal-600 hover:underline inline-flex items-center"><i class="fa-brands fa-whatsapp mr-1"></i>{{ $contato->telefone }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

    @else
        {{-- CABEÇALHO HERO (Outros status: cancelada etc.) --}}
        <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-white z-10 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 text-xs font-bold text-slate-200 justify-center md:justify-start">
                            <i class="fa-solid fa-info-circle"></i> Inscrição
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">Inscrição {{ ucfirst(str_replace('_', ' ', $inscricao->status)) }}</h1>
                        <p class="text-slate-300 mt-2 text-lg font-light">Esta inscrição não pode mais ser paga ou alterada.</p>
                    </div>
                    <div class="z-10">
                        <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white text-sm font-bold transition-all">
                            <i class="fa-solid fa-list mr-2"></i> Minhas Inscrições
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-20 -mt-20 pb-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 text-center">
                    <div class="mx-auto bg-slate-100 text-slate-500 w-20 h-20 rounded-full flex items-center justify-center mb-6">
                        <i class="fa-solid fa-xmark text-4xl"></i>
                    </div>
                    <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center justify-center gap-2 mt-6 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-lg">
                        <i class="fa-solid fa-list"></i> Ver Minhas Inscrições
                    </a>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
