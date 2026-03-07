@extends('layouts.public')

@section('title', 'Pagamento - Inscrição em grupo')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('inscricao-grupo.percurso', $evento) }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 mb-6">
            <i class="fa-solid fa-arrow-left"></i> Voltar (percurso)
        </a>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                <h1 class="text-xl font-black text-white">Resumo e pagamento</h1>
                <p class="text-orange-100 text-sm mt-1">{{ $evento->nome }}</p>
                <p class="text-orange-200 text-xs mt-2">Etapa 3 de 3</p>
            </div>

            <div class="p-6 space-y-6">
                @if($errors->any())
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div>
                    <h2 class="text-sm font-bold text-slate-500 uppercase mb-2">Resumo por atleta</h2>
                    <ul class="text-slate-800 space-y-2">
                        @foreach($atletasOrdenados as $atleta)
                            @php
                                $resumo = $resumoPorAtleta[$atleta->id] ?? null;
                            @endphp
                            <li class="flex justify-between items-start p-2 rounded bg-slate-50">
                                <span><strong>{{ $atleta->user->name ?? 'Atleta' }}</strong>
                                    @if($resumo)
                                        <span class="block text-xs text-slate-500">{{ $resumo['categoria']->percurso->descricao ?? '' }} — {{ $resumo['categoria']->nome }}</span>
                                    @endif
                                    @if(!empty($resumo['equipe_nome']))
                                        <span class="block text-xs text-slate-600 mt-0.5"><i class="fa-solid fa-shirt mr-1 text-orange-500"></i>{{ $resumo['equipe_nome'] }}</span>
                                    @endif
                                </span>
                                @if($resumo)
                                    <span class="font-medium">R$ {{ number_format($resumo['valor_base'], 2, ',', '.') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between text-slate-700">
                        <span>Subtotal inscrições</span>
                        <span>R$ {{ number_format($valorInscricoes, 2, ',', '.') }}</span>
                    </div>
                    @if($totalProdutos > 0)
                        <div class="flex justify-between text-slate-700">
                            <span>Produtos opcionais</span>
                            <span>R$ {{ number_format($totalProdutos, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-700">
                        <span>Taxa ({{ number_format($pctTaxa, 0) }}%)</span>
                        <span>R$ {{ number_format($taxa, 2, ',', '.') }}</span>
                    </div>
                    @if($cupom && $descontoCupom > 0)
                        <div class="flex justify-between text-green-600 font-medium">
                            <span>Desconto ({{ $cupom->codigo }})</span>
                            <span>- R$ {{ number_format($descontoCupom, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-lg text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total</span>
                        <span>R$ {{ number_format($totalComDesconto, 2, ',', '.') }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('inscricao-grupo.aplicar-cupom', $evento) }}" class="flex gap-2" id="form-cupom">
                    @csrf
                    <input type="hidden" name="tipo_pagamento" id="tipo_pagamento_cupom" value="{{ $dados['tipo_pagamento'] ?? 'unico' }}">
                    <input type="hidden" name="equipe_id" value="{{ $dados['equipe_id'] ?? '' }}">
                    @foreach($atletasOrdenados as $atleta)
                        @php $eqId = $dados['equipe_por_atleta'][$atleta->id] ?? $dados['equipe_id'] ?? ''; @endphp
                        <input type="hidden" name="equipes[{{ $atleta->id }}]" value="{{ $eqId ?: '' }}">
                    @endforeach
                    <input type="text" name="codigo_cupom" value="{{ $dados['cupom_codigo'] ?? '' }}" placeholder="Cupom de desconto"
                        class="flex-1 rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                    <button type="submit" class="px-4 py-2.5 bg-slate-700 text-white rounded-lg font-medium hover:bg-slate-800">Aplicar</button>
                </form>

                <form method="POST" action="{{ route('inscricao-grupo.confirmar', $evento) }}" id="form-confirmar">
                    @csrf
                    {{-- Equipe: enviar no POST para garantir que chegue em confirmar (evita depender só da sessão) --}}
                    <input type="hidden" name="equipe_id" value="{{ $dados['equipe_id'] ?? '' }}">
                    @foreach($atletasOrdenados as $atleta)
                        @php
                            $eqId = $dados['equipe_por_atleta'][$atleta->id] ?? $dados['equipe_id'] ?? '';
                        @endphp
                        <input type="hidden" name="equipes[{{ $atleta->id }}]" value="{{ $eqId ?: '' }}">
                    @endforeach
                    <div class="border border-slate-200 rounded-lg p-4 space-y-3">
                        <p class="block text-sm font-bold text-slate-800">Como deseja pagar?</p>
                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                            <input type="radio" name="tipo_pagamento" value="unico" {{ ($dados['tipo_pagamento'] ?? 'unico') === 'unico' ? 'checked' : '' }} class="mt-1 text-orange-600 focus:ring-orange-500">
                            <div class="ml-3">
                                <span class="block font-bold text-slate-900">Pagamento único</span>
                                <span class="block text-xs text-slate-500">Você paga o total agora e todos ficam inscritos.</span>
                            </div>
                        </label>
                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                            <input type="radio" name="tipo_pagamento" value="parcial" {{ ($dados['tipo_pagamento'] ?? '') === 'parcial' ? 'checked' : '' }} class="mt-1 text-orange-600 focus:ring-orange-500" id="radio-parcial">
                            <div class="ml-3">
                                <span class="block font-bold text-slate-900">Pagar por alguns</span>
                                <span class="block text-xs text-slate-500">Escolha quem você paga agora; os demais ficam pendentes e recebem link para pagar.</span>
                            </div>
                        </label>
                        <div class="p-3 border rounded-lg bg-slate-50 border-slate-200" id="bloco-quem-pagar" style="display: none;">
                            <p class="text-sm font-medium text-slate-700 mb-2">Selecione quem você paga agora:</p>
                            <ul class="space-y-2">
                                @foreach($atletasOrdenados as $atleta)
                                    @php $resumo = $resumoPorAtleta[$atleta->id] ?? null; @endphp
                                    <li class="flex items-center gap-3 p-2 rounded bg-white border border-slate-200">
                                        <input type="checkbox" name="atleta_ids_pagar_agora[]" value="{{ $atleta->id }}" id="pagar_{{ $atleta->id }}" {{ in_array($atleta->id, $dados['atleta_ids_pagar_agora'] ?? [], true) ? 'checked' : '' }} class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                                        <label for="pagar_{{ $atleta->id }}" class="flex-1 cursor-pointer">
                                            <span class="font-medium text-slate-800">{{ $atleta->user->name ?? 'Atleta' }}</span>
                                            @if($resumo)
                                                <span class="text-xs text-slate-500 block">R$ {{ number_format($resumo['valor_base'], 2, ',', '.') }}</span>
                                            @endif
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs text-amber-700 mt-2" id="msg-parcial">Marque pelo menos um atleta para pagar agora.</p>
                        </div>
                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                            <input type="radio" name="tipo_pagamento" value="individual" {{ ($dados['tipo_pagamento'] ?? '') === 'individual' ? 'checked' : '' }} class="mt-1 text-orange-600 focus:ring-orange-500">
                            <div class="ml-3">
                                <span class="block font-bold text-slate-900">Cada um paga o seu</span>
                                <span class="block text-xs text-slate-500">Cada atleta receberá um link para pagar a própria inscrição.</span>
                            </div>
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('inscricao-grupo.percurso', $evento) }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">Voltar</a>
                        <button type="submit" class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-lg">
                            <i class="fa-solid fa-lock mr-1"></i> Criar inscrições e ir para pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var formConfirmar = document.getElementById('form-confirmar');
    var formCupom = document.getElementById('form-cupom');
    var blocoPagar = document.getElementById('bloco-quem-pagar');
    var radioParcial = document.getElementById('radio-parcial');

    function atualizarBlocoParcial() {
        if (!blocoPagar) return;
        var parcial = formConfirmar && formConfirmar.querySelector('input[name=tipo_pagamento][value=parcial]:checked');
        blocoPagar.style.display = parcial ? 'block' : 'none';
    }
    formConfirmar && formConfirmar.querySelectorAll('input[name=tipo_pagamento]').forEach(function(r) {
        r.addEventListener('change', atualizarBlocoParcial);
    });
    atualizarBlocoParcial();

    formConfirmar && formConfirmar.addEventListener('submit', function(e) {
        var parcial = formConfirmar.querySelector('input[name=tipo_pagamento][value=parcial]:checked');
        if (parcial) {
            var checks = formConfirmar.querySelectorAll('input[name="atleta_ids_pagar_agora[]"]:checked');
            if (!checks.length) {
                e.preventDefault();
                document.getElementById('msg-parcial').textContent = 'Selecione pelo menos um atleta para pagar agora.';
                blocoPagar && blocoPagar.scrollIntoView({ behavior: 'smooth' });
                return;
            }
        }
    });

    formCupom && formCupom.addEventListener('submit', function() {
        var r = formConfirmar && formConfirmar.querySelector('input[name=tipo_pagamento]:checked');
        if (r) {
            document.getElementById('tipo_pagamento_cupom').value = r.value;
            formCupom.querySelectorAll('input[name="atleta_ids_pagar_agora[]"]').forEach(function(el) { el.remove(); });
            if (r.value === 'parcial' && formConfirmar) {
                formConfirmar.querySelectorAll('input[name="atleta_ids_pagar_agora[]"]:checked').forEach(function(cb) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'atleta_ids_pagar_agora[]';
                    input.value = cb.value;
                    formCupom.appendChild(input);
                });
            }
        }
    });
})();
</script>
@endsection
