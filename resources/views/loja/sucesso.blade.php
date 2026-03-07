@extends('layouts.public')

@section('title', 'Pedido Confirmado - #' . $pedido->id)

@section('content')
<div class="bg-gray-50 min-h-screen pt-24 pb-20 font-sans">
    <div class="container mx-auto px-4 md:px-8 max-w-2xl text-center">
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-12">
            {{-- Ícone de Sucesso --}}
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                <i class="fa-solid fa-check text-5xl text-green-600"></i>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-4">Pagamento Confirmado!</h1>
            <p class="text-lg text-slate-600 mb-8">
                Parabéns! Seu pedido <span class="font-bold text-slate-900">#{{ $pedido->id }}</span> foi processado com sucesso.
            </p>
            
            {{-- Detalhes do Pedido --}}
            <div class="bg-slate-50 rounded-xl p-6 mb-8 border border-slate-200 text-left">
                <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-200">
                    <span class="text-slate-500 text-xs uppercase font-bold tracking-wider">Valor Total</span>
                    <span class="text-xl font-black text-green-600">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span>
                </div>
                
                @if($pedido->evento)
                    <div class="mb-2">
                        <span class="text-slate-400 text-xs uppercase font-bold block mb-0.5">Evento</span>
                        <span class="text-slate-800 font-bold">{{ $pedido->evento->nome }}</span>
                    </div>
                @endif

                <div class="mt-2">
                    <span class="text-slate-400 text-xs uppercase font-bold block mb-0.5">Data</span>
                    <span class="text-slate-700 font-medium">{{ $pedido->created_at->format('d/m/Y \à\s H:i') }}</span>
                </div>
            </div>

            <p class="text-slate-500 text-sm mb-8">
                Enviamos os detalhes da sua compra para o seu e-mail.<br>
                Seus produtos estarão disponíveis para retirada no dia do evento.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('loja.index', ['evento_id' => $pedido->evento_id]) }}" class="inline-flex items-center justify-center px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-xl hover:border-orange-500 hover:text-orange-600 transition-all">
                    <i class="fa-solid fa-store mr-2"></i> Voltar para a Loja
                </a>
                
                @auth
                     <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center justify-center px-6 py-3 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 shadow-lg shadow-orange-500/30 transition-all">
                        <i class="fa-solid fa-list-check mr-2"></i> Meus Pedidos
                    </a>
                @else
                    <a href="{{ route('welcome') }}" class="inline-flex items-center justify-center px-6 py-3 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 shadow-lg shadow-orange-500/30 transition-all">
                        <i class="fa-solid fa-home mr-2"></i> Ir para Início
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection