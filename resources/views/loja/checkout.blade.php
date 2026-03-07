@extends('layouts.public')

@section('title', 'Finalizar Compra')

@section('content')
<div class="bg-gray-50 min-h-screen pt-24 pb-20">
    <div class="container mx-auto px-4 md:px-8 max-w-6xl">
        
        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-800 flex items-center gap-3">
                <i class="fa-regular fa-credit-card text-orange-600"></i> Pagamento
            </h1>
            <p class="text-slate-500">Confirme seus dados e escolha a forma de pagamento.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Coluna Esquerda: Dados e Identificação --}}
            <div class="lg:col-span-8 space-y-6">
                
                {{-- Dados do Comprador --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-user mr-2 text-slate-400"></i> Identificação
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500 text-xs uppercase font-bold">Nome</p>
                            <p class="text-slate-800 font-medium">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 text-xs uppercase font-bold">Email</p>
                            <p class="text-slate-800 font-medium">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 text-xs uppercase font-bold">CPF</p>
                            {{-- CORREÇÃO: Exibe a variável preparada pelo Controller ou busca fallback --}}
                            <p class="text-slate-800 font-medium font-mono">
                                {{ $cpfExibicao ?? $user->cpf ?? 'Não informado' }}
                            </p>
                        </div>
                    </div>

                    {{-- Vínculo com Inscrição --}}
                    @if($inscricao)
                        <div class="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
                            <div class="bg-blue-100 p-2 rounded-full text-blue-600 shrink-0">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900">Vínculo com Inscrição Encontrado</h4>
                                <p class="text-xs text-blue-700 mt-1">
                                    Esta compra será automaticamente vinculada à sua inscrição <strong>#{{ $inscricao->id }}</strong> no evento <strong>{{ $evento->nome }}</strong>.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-start gap-3">
                            <div class="bg-slate-200 p-2 rounded-full text-slate-500 shrink-0">
                                <i class="fa-solid fa-info"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-700">Compra Avulsa</h4>
                                <p class="text-xs text-slate-500 mt-1">
                                    Não encontramos uma inscrição ativa para este evento nesta conta. Os produtos serão processados como uma compra independente.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Resumo dos Itens (Simplificado) --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center">
                        <i class="fa-solid fa-basket-shopping mr-2 text-slate-400"></i> Itens do Pedido
                    </h3>
                    <ul class="space-y-4">
                        @foreach($carrinho as $item)
                            <li class="flex justify-between items-center text-sm border-b border-slate-50 last:border-0 pb-3 last:pb-0">
                                <div class="flex items-center gap-3">
                                    @if(!empty($item['imagem']))
                                        <img src="{{ asset('storage/' . $item['imagem']) }}" class="w-10 h-10 rounded object-cover border border-slate-100">
                                    @else
                                        <div class="w-10 h-10 rounded bg-slate-100 flex items-center justify-center text-slate-400"><i class="fa-solid fa-image"></i></div>
                                    @endif
                                    <div>
                                        <span class="block font-bold text-slate-800">{{ $item['nome'] }}</span>
                                        <span class="text-xs text-slate-500">{{ $item['quantidade'] }}x R$ {{ number_format($item['preco'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                <span class="font-bold text-slate-800">R$ {{ number_format($item['preco'] * $item['quantidade'], 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>

            {{-- Coluna Direita: Totais e Pagamento --}}
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
                
                <div class="bg-white p-6 md:p-8 rounded-3xl shadow-xl border border-slate-100">
                    <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center border-b border-slate-100 pb-4">
                        <i class="fa-solid fa-wallet mr-3 text-orange-500"></i> Total a Pagar
                    </h3>

                    <div class="space-y-3 mb-6 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span>R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Taxas</span>
                            <span>R$ {{ number_format($valorTaxa, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-end border-t border-dashed border-slate-200 pt-4 mb-8">
                        <span class="text-lg font-bold text-slate-700">Total</span>
                        <span class="text-3xl font-black text-orange-600">R$ {{ number_format($totalGeral, 2, ',', '.') }}</span>
                    </div>

                    {{-- FORMULÁRIO DE PROCESSAMENTO --}}
                    {{-- A rota deve ser exata. Se falhar, verifique se está fora do grupo 'auth' no web.php --}}
                    <form action="{{ route('loja.checkout.processar') }}" method="POST">
                        @csrf
                        
                        {{-- Campos Ocultos Essenciais --}}
                        <input type="hidden" name="evento_id" value="{{ $evento->id }}">
                        <input type="hidden" name="total_geral" value="{{ $totalGeral }}">
                        
                        @if($inscricao)
                            <input type="hidden" name="inscricao_id" value="{{ $inscricao->id }}">
                        @endif
                        
                        <button type="submit" class="w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold text-lg rounded-xl shadow-lg shadow-green-200 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3">
                            <i class="fa-solid fa-lock"></i>
                            <span>Pagar Agora</span>
                        </button>
                    </form>
                    
                    <div class="mt-6 text-center">
                        <p class="text-xs text-slate-400 mb-2">Ambiente seguro autenticado</p>
                        <div class="flex justify-center gap-2 opacity-50">
                            <i class="fa-brands fa-cc-visa text-2xl"></i>
                            <i class="fa-brands fa-cc-mastercard text-2xl"></i>
                            <i class="fa-brands fa-pix text-2xl"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection