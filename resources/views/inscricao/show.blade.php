<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo da Inscrição - Proticketsports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .error-box { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success-box { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body class="bg-gray-100">

    @include('layouts.public-navigation')

    <div class="container mx-auto p-4 md:p-8">
        <div class="max-w-2xl mx-auto space-y-6">
            
            @if($inscricao->status === 'confirmada')

                {{-- SEÇÃO: INSCRIÇÃO CONFIRMADA --}}
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <div class="mx-auto bg-green-100 text-green-600 w-20 h-20 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-check-double text-5xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-slate-800 mt-6">Inscrição Confirmada!</h1>
                    <p class="text-gray-600 mt-2">Parabéns, {{ $inscricao->atleta->user->name }}! Sua vaga no evento está garantida.</p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md">
                    <div class="text-left bg-gray-50 p-6 rounded-lg border">
                        <h2 class="text-lg font-semibold mb-4 border-b pb-2">Recibo da Inscrição</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Evento:</span><span class="font-medium text-gray-800 text-right">{{ $inscricao->evento->nome }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Categoria:</span><span class="font-medium text-gray-800">{{ $inscricao->categoria->nome }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Código:</span><span class="font-medium text-gray-800 font-mono">{{ $inscricao->codigo_inscricao }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Data do Pagamento:</span><span class="font-medium text-gray-800">{{ $inscricao->data_pagamento ? \Carbon\Carbon::parse($inscricao->data_pagamento)->format('d/m/Y \à\s H:i') : 'N/A' }}</span></div>
                            <div class="flex justify-between border-t pt-3 mt-3"><span class="text-gray-500 font-bold">Valor Pago:</span><span class="font-bold text-lg text-green-700">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span></div>
                            <div class="flex justify-between items-center"><span class="text-gray-500">Status:</span><span class="px-3 py-1 text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><i class="fa-solid fa-check mr-1"></i> Confirmada</span></div>
                        </div>
                    </div>
                </div>
                 <div class="text-center">
                    <a href="{{ route('atleta.inscricoes') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        Ver Todas as Minhas Inscrições
                    </a>
                </div>

            @elseif($inscricao->status === 'aguardando_pagamento')

                {{-- SEÇÃO: INSCRIÇÃO AGUARDANDO PAGAMENTO --}}
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <div class="mx-auto bg-yellow-100 text-yellow-600 w-20 h-20 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-hourglass-half text-4xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-slate-800 mt-6">Pré-inscrição Realizada!</h1>
                    <p class="text-gray-600 mt-2">Obrigado, {{ $inscricao->atleta->user->name }}. O seu registro foi recebido. Agora falta apenas o pagamento para confirmar sua vaga.</p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md">
                    <div class="text-left bg-gray-50 p-6 rounded-lg border">
                        <h2 class="text-lg font-semibold mb-4 border-b pb-2">Detalhes da Inscrição</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Evento:</span><span class="font-medium text-gray-800 text-right">{{ $inscricao->evento->nome }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Categoria:</span><span class="font-medium text-gray-800">{{ $inscricao->categoria->nome }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Código:</span><span class="font-medium text-gray-800 font-mono">{{ $inscricao->codigo_inscricao }}</span></div>
                            
                            {{-- Resumo Financeiro Detalhado --}}
                            <div class="border-t pt-3 mt-3 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Valor da Inscrição:</span>
                                    <span class="font-medium text-gray-800">R$ {{ number_format($inscricao->valor_original, 2, ',', '.') }}</span>
                                </div>
                                
                                @if($inscricao->produtosOpcionais->isNotEmpty())
                                    <div class="pt-2">
                                        <p class="text-gray-500 font-semibold mb-2">Itens Adicionais:</p>
                                        <div class="space-y-2 pl-4">
                                            @foreach ($inscricao->produtosOpcionais as $produto)
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-gray-600">
                                                        {{ $produto->pivot->quantidade }}x {{ $produto->nome }} 
                                                        @if ($produto->pivot->tamanho) ({{ $produto->pivot->tamanho }}) @endif
                                                    </span>
                                                    <span class="font-medium text-gray-800">R$ {{ number_format($produto->pivot->valor_pago_por_item * $produto->pivot->quantidade, 2, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($inscricao->cupom_id && $inscricao->valor_desconto > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span class="font-medium">Desconto ({{ $inscricao->cupom->codigo }}):</span>
                                        <span class="font-medium">- R$ {{ number_format($inscricao->valor_desconto, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Taxa de Serviço:</span>
                                    <span class="font-medium text-gray-800">R$ {{ number_format($inscricao->taxa_plataforma, 2, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between border-t pt-3 mt-3">
                                <span class="text-gray-500 font-bold">Valor Total a Pagar:</span>
                                <span class="font-bold text-lg text-green-700">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Status:</span>
                                <span class="px-3 py-1 text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Aguardando Pagamento</span>
                            </div>
                        </div>
                         @if($inscricao->produtosOpcionais->isNotEmpty())
                            <div class="mt-4 p-3 bg-red-50 border-l-4 border-red-400 text-red-800 text-xs rounded-r-lg">
                                <p><strong>Importante:</strong> A reserva dos seus itens opcionais (camisas, etc.) só será confirmada após a aprovação do pagamento. O estoque é limitado.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-800">Cupom de Desconto</h3>
                    
                    @if(session('cupom_success'))
                        <div class="success-box mt-4">{{ session('cupom_success') }}</div>
                    @endif
                    @if(session('cupom_error'))
                        <div class="error-box mt-4">{{ session('cupom_error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="error-box mt-4">Ocorreu um erro. Por favor, tente novamente.</div>
                    @endif

                    <form action="{{ route('inscricao.cupom.aplicar', $inscricao) }}" method="POST" class="mt-4 flex gap-2">
                        @csrf
                        <input type="text" name="codigo_cupom" placeholder="Digite seu cupom" class="flex-grow block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Aplicar
                        </button>
                    </form>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <h3 class="font-semibold text-xl">Pronto para confirmar sua vaga?</h3>
                    <p class="text-gray-600 mt-2">Clique no botão abaixo para ir para a página de pagamento e finalizar sua inscrição.</p>
                    <a href="{{ route('pagamento.show', $inscricao) }}" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300">
                        <i class="fa-solid fa-shield-halved mr-2"></i> Ir para Pagamento Seguro
                    </a>
                </div>

            @else

                {{-- SEÇÃO: OUTROS STATUS (EX: CANCELADA) --}}
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <div class="mx-auto bg-red-100 text-red-600 w-20 h-20 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-4xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-slate-800 mt-6">Inscrição {{ ucfirst(str_replace('_', ' ', $inscricao->status)) }}</h1>
                    <p class="text-gray-600 mt-2">Esta inscrição não pode mais ser paga ou alterada.</p>
                       <a href="{{ route('atleta.inscricoes') }}" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        Ver Minhas Inscrições
                    </a>
                </div>

            @endif

        </div>
    </div>

</body>
</html>

