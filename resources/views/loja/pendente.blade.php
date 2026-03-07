@extends('layouts.public')

@section('title', 'Pagamento Pendente - Pedido #' . $pedido->id)

@section('content')
<div class="bg-gray-50 min-h-screen pt-24 pb-20 font-sans">
    <div class="container mx-auto px-4 md:px-8 max-w-2xl text-center">
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-12">
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-clock text-4xl text-yellow-600"></i>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-4">Pagamento em Análise</h1>
            <p class="text-lg text-slate-600 mb-8">
                Recebemos seu pedido <span class="font-bold text-slate-900">#{{ $pedido->id }}</span>, mas o pagamento ainda está sendo processado.
            </p>
            
            <div class="bg-yellow-50 text-yellow-800 p-5 rounded-xl border border-yellow-200 mb-8 text-sm text-left">
                <p class="mb-2"><strong><i class="fa-solid fa-circle-info mr-1"></i> O que fazer agora?</strong></p>
                <ul class="list-disc list-inside space-y-1 ml-1">
                    <li>Se você pagou via <strong>PIX</strong>, a confirmação ocorre em poucos segundos. Atualize a página em breve.</li>
                    <li>Se foi via <strong>Cartão</strong>, pode levar alguns minutos para análise de segurança do banco.</li>
                    <li>Se não pagou ainda, clique no botão abaixo.</li>
                </ul>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('loja.pedido.pagamento', $pedido->id) }}" class="inline-flex items-center justify-center px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-xl hover:border-blue-500 hover:text-blue-600 transition-all">
                    <i class="fa-regular fa-credit-card mr-2"></i> Tentar Pagar Novamente
                </a>
                 <a href="{{ route('loja.index', ['evento_id' => $pedido->evento_id]) }}" class="inline-flex items-center justify-center px-6 py-3 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 shadow-lg transition-all">
                    <i class="fa-solid fa-store mr-2"></i> Voltar para Loja
                </a>
            </div>
        </div>
    </div>
</div>
@endsection