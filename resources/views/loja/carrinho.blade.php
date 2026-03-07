@extends('layouts.public')

@section('title', 'Meu Carrinho')

@section('content')

{{-- Lógica para definir o link de "Voltar para Loja" --}}
@php
    $eventoLink = route('eventos.public.index'); // Link padrão de segurança
    $carrinhoSession = session('carrinho', []);
    
    if (!empty($carrinhoSession)) {
        // Pega o primeiro item para descobrir de qual evento é a loja
        $firstItem = reset($carrinhoSession);
        $prod = \App\Models\ProdutoOpcional::find($firstItem['id']);
        if ($prod && $prod->evento) {
            $eventoLink = route('loja.index', ['evento_id' => $prod->evento->id]);
        }
    } elseif(request()->headers->get('referer')) {
         // Se vazio, tenta voltar para onde estava
         $eventoLink = url()->previous();
    }
@endphp

<div class="bg-gray-50 min-h-screen pb-20 pt-24">
    <div class="container mx-auto px-4 md:px-8 max-w-7xl">
        
        @if(session('carrinho') && count(session('carrinho')) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- COLUNA ESQUERDA: Título, Mensagens e Itens --}}
                <div class="lg:col-span-8 space-y-6">
                    
                    {{-- Título Alinhado com os Produtos --}}
                    <div class="pb-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                        <div>
                            <h1 class="text-3xl font-black text-slate-800 flex items-center gap-3">
                                <i class="fa-solid fa-cart-shopping text-orange-600"></i> Meu Carrinho
                            </h1>
                            <p class="text-slate-500 mt-1 text-sm">Verifique seus itens antes de prosseguir.</p>
                        </div>
                        <span class="bg-orange-50 text-orange-700 text-xs font-bold px-3 py-1 rounded-full border border-orange-100">
                            {{ count(session('carrinho')) }} itens
                        </span>
                    </div>

                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl shadow-sm flex items-center gap-3 animate-fade-in-up">
                            <div class="bg-green-100 p-2 rounded-full shrink-0"><i class="fa-solid fa-check text-green-600"></i></div>
                            <div><span class="font-bold">Sucesso!</span> {{ session('success') }}</div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl shadow-sm flex items-center gap-3 animate-fade-in-up">
                            <div class="bg-red-100 p-2 rounded-full shrink-0"><i class="fa-solid fa-triangle-exclamation text-red-600"></i></div>
                            <div><span class="font-bold">Atenção!</span> {{ session('error') }}</div>
                        </div>
                    @endif

                    {{-- Lista de Itens --}}
                    <div class="space-y-4">
                        @php 
                            $subtotal = 0; 
                            $eventoTaxa = 0; 
                            
                            // Recalcula para garantir consistência na View
                            $carrinhoData = session('carrinho');
                            if (!empty($carrinhoData)) {
                                 $first = reset($carrinhoData);
                                 $pModel = \App\Models\ProdutoOpcional::find($first['id']);
                                 if($pModel && $pModel->evento) {
                                     $eventoTaxa = $pModel->evento->taxaservico ?? 0;
                                 }
                            }
                        @endphp

                        @foreach(session('carrinho') as $id => $details)
                            @php $subtotal += $details['preco'] * $details['quantidade']; @endphp
                            
                            <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-100 flex flex-col sm:flex-row items-center gap-6 transition-all hover:shadow-md hover:border-orange-100 group">
                                {{-- Imagem --}}
                                <div class="w-24 h-24 sm:w-32 sm:h-32 bg-slate-50 rounded-xl overflow-hidden shrink-0 border border-slate-100 relative">
                                    @if(!empty($details['imagem']))
                                        <img src="{{ asset('storage/' . $details['imagem']) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50">
                                            <i class="fa-regular fa-image text-3xl mb-1"></i>
                                            <span class="text-[10px] uppercase font-bold">Sem Foto</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info do Produto --}}
                                <div class="flex-grow text-center sm:text-left w-full">
                                    <h3 class="font-bold text-slate-800 text-lg sm:text-xl leading-tight mb-2">{{ $details['nome'] }}</h3>
                                    <div class="text-sm text-slate-500 font-medium mb-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 justify-center sm:justify-start">
                                        <span class="bg-slate-100 px-2 py-1 rounded text-slate-600 text-xs font-bold uppercase tracking-wide">Produto Oficial</span>
                                        <span class="hidden sm:inline text-slate-300">•</span>
                                        <span>Unitário: <span class="text-slate-800 font-bold">R$ {{ number_format($details['preco'], 2, ',', '.') }}</span></span>
                                    </div>

                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                        {{-- Seletor de Quantidade --}}
                                        <div class="flex items-center bg-slate-50 rounded-lg border border-slate-200 h-10 shadow-sm w-32">
                                            {{-- Botão Menos --}}
                                            <form action="{{ route('carrinho.atualizar') }}" method="POST" class="h-full">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="id" value="{{ $id }}">
                                                <input type="hidden" name="quantidade" value="{{ $details['quantidade'] - 1 }}">
                                                <button type="submit" class="w-10 h-full text-slate-400 hover:text-orange-600 hover:bg-white rounded-l-lg transition-colors flex items-center justify-center border-r border-slate-200" @if($details['quantidade'] <= 1) title="Para remover, use o botão de lixeira" @endif>
                                                    <i class="fa-solid fa-minus text-xs"></i>
                                                </button>
                                            </form>

                                            <div class="flex-1 text-center text-sm font-bold text-slate-800">
                                                {{ $details['quantidade'] }}
                                            </div>

                                            {{-- Botão Mais --}}
                                            <form action="{{ route('carrinho.atualizar') }}" method="POST" class="h-full">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="id" value="{{ $id }}">
                                                <input type="hidden" name="quantidade" value="{{ $details['quantidade'] + 1 }}">
                                                <button type="submit" class="w-10 h-full text-slate-400 hover:text-orange-600 hover:bg-white rounded-r-lg transition-colors flex items-center justify-center border-l border-slate-200">
                                                    <i class="fa-solid fa-plus text-xs"></i>
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Preço Total (Mobile) --}}
                                        <div class="sm:hidden text-lg font-black text-slate-900 w-full text-center my-2">
                                            R$ {{ number_format($details['preco'] * $details['quantidade'], 2, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Preço Total e Delete (Desktop) --}}
                                <div class="text-right hidden sm:block min-w-[120px]">
                                    <span class="block text-xs text-slate-400 font-bold uppercase mb-1">Total Item</span>
                                    <span class="block font-black text-xl text-slate-900 mb-4">
                                        R$ {{ number_format($details['preco'] * $details['quantidade'], 2, ',', '.') }}
                                    </span>
                                    
                                    <form action="{{ route('carrinho.remover') }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $id }}">
                                        <button class="text-slate-400 hover:text-red-600 text-xs font-bold uppercase transition-colors flex items-center gap-2 ml-auto bg-slate-50 hover:bg-red-50 px-3 py-1.5 rounded-lg">
                                            <i class="fa-solid fa-trash-can"></i> Remover
                                        </button>
                                    </form>
                                </div>

                                {{-- Botão Remover (Mobile) --}}
                                <div class="sm:hidden w-full pt-4 border-t border-slate-100 flex justify-center">
                                    <form action="{{ route('carrinho.remover') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $id }}">
                                        <button class="text-red-500 hover:text-red-700 text-sm font-bold flex items-center gap-2">
                                            <i class="fa-solid fa-trash-can"></i> Remover do Carrinho
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Cálculo Final dos Valores --}}
                @php
                    $valorTaxa = $subtotal * ($eventoTaxa / 100);
                    $totalGeral = $subtotal + $valorTaxa;
                @endphp

                {{-- COLUNA DIREITA: Steps e Resumo (Sticky) --}}
                <div class="lg:col-span-4 lg:sticky lg:top-8 space-y-6">
                    
                    {{-- Steps (Progresso) --}}
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            <div class="flex flex-col items-center gap-1 text-orange-600">
                                <div class="w-8 h-8 rounded-full bg-orange-600 text-white flex items-center justify-center shadow-lg shadow-orange-200">1</div>
                                <span>Carrinho</span>
                            </div>
                            <div class="h-0.5 flex-grow bg-slate-100 mx-2"></div>
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-8 h-8 rounded-full border-2 border-slate-200 bg-slate-50 flex items-center justify-center">2</div>
                                <span>Pagamento</span>
                            </div>
                            <div class="h-0.5 flex-grow bg-slate-100 mx-2"></div>
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-8 h-8 rounded-full border-2 border-slate-200 bg-slate-50 flex items-center justify-center">3</div>
                                <span>Fim</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card de Resumo --}}
                    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100">
                        <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center border-b border-slate-100 pb-4">
                            <i class="fa-solid fa-receipt mr-3 text-orange-500"></i> Resumo do Pedido
                        </h3>
                        
                        <div class="space-y-4 mb-6 text-sm">
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Subtotal ({{ count(session('carrinho')) }} itens)</span>
                                <span class="font-bold text-slate-800">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1">
                                    Taxa de Serviço 
                                    <span class="text-xs bg-slate-100 px-1.5 rounded text-slate-500 font-bold" title="Taxa aplicada pelo evento">{{ $eventoTaxa }}%</span>
                                </span>
                                @if($valorTaxa > 0)
                                    <span class="font-bold text-slate-800">R$ {{ number_format($valorTaxa, 2, ',', '.') }}</span>
                                @else
                                    <span class="font-bold text-green-600 uppercase text-xs bg-green-50 px-2 py-1 rounded">Grátis</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="border-t-2 border-dashed border-slate-100 my-6 pt-6">
                            <div class="flex justify-between items-end">
                                <span class="text-lg font-bold text-slate-700">Total a Pagar</span>
                                <span class="text-3xl font-black text-slate-900">
                                    <span class="text-sm text-slate-400 font-bold align-top mt-1 inline-block mr-1">R$</span>{{ number_format($totalGeral, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('loja.checkout') }}" class="w-full py-4 bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 text-white font-bold text-lg rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-3 group mb-4">
                            <span>Ir para Pagamento</span>
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        {{-- Botão Voltar (Link Moderno) --}}
                        <div class="text-center">
                            <a href="{{ $eventoLink }}" class="block w-full py-3 bg-white border-2 border-slate-200 text-slate-500 font-bold text-sm rounded-xl hover:border-orange-500 hover:text-orange-600 transition-all duration-300 group">
                                <i class="fa-solid fa-chevron-left text-xs mr-2 group-hover:-translate-x-1 transition-transform"></i> Continuar Comprando
                            </a>
                        </div>

                        {{-- Ícones de Pagamento --}}
                        <div class="pt-6 mt-6 border-t border-slate-100">
                            <p class="text-xs text-center text-slate-400 font-semibold uppercase tracking-wider mb-3">Aceitamos</p>
                            <div class="flex justify-center gap-4 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                                <i class="fa-brands fa-cc-visa text-2xl" title="Visa"></i>
                                <i class="fa-brands fa-cc-mastercard text-2xl" title="Mastercard"></i>
                                <i class="fa-brands fa-pix text-2xl" title="Pix"></i>
                                <i class="fa-solid fa-barcode text-2xl" title="Boleto"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Card de Segurança --}}
                    <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-2xl flex items-center gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0 shadow-sm text-blue-500">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wide">Compra Segura</h4>
                            <p class="text-[10px] text-blue-700 mt-0.5 leading-tight">
                                Ambiente criptografado e seguro para seus dados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Carrinho Vazio --}}
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-2xl border border-slate-100 mt-8 relative overflow-hidden">
                <div class="w-48 h-48 bg-slate-50 rounded-full flex items-center justify-center mb-8 shadow-inner relative z-10">
                    <i class="fa-solid fa-cart-arrow-down text-7xl text-slate-300 ml-[-10px]"></i>
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-slate-800 mb-4 relative z-10">Seu carrinho está vazio</h2>
                <p class="text-slate-500 text-lg mb-10 max-w-md text-center leading-relaxed relative z-10">
                    Parece que você ainda não adicionou nenhum produto. Que tal conferir as novidades da loja?
                </p>
                <a href="{{ $eventoLink }}" class="inline-flex items-center px-10 py-4 bg-slate-900 hover:bg-orange-600 text-white font-bold rounded-2xl text-lg transition-all shadow-lg hover:shadow-orange-500/30 hover:-translate-y-1 relative z-10">
                    <i class="fa-solid fa-store mr-2"></i>
                    Voltar para a Loja
                </a>
                
                {{-- Elemento decorativo de fundo --}}
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-orange-50 rounded-full opacity-50 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-slate-50 rounded-full opacity-50 blur-3xl"></div>
            </div>
        @endif
    </div>
</div>
@endsection