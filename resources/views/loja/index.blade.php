@extends('layouts.public')

@section('title', 'Loja Oficial - ' . $evento->nome)

@section('content')

    {{-- Cabeçalho Moderno e Compacto (Estilo Padrão do Evento) --}}
    <section class="relative bg-cover bg-center py-12 md:py-16">
        {{-- Imagem de fundo --}}
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $evento->banner_url ? asset('storage/' . $evento->banner_url) : 'https://images.unsplash.com/photo-1573521193826-58c7a24275a7?q=80&w=1974&auto=format&fit=crop' }}');"></div>
        {{-- Overlay escuro para legibilidade --}}
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-[2px]"></div>

        {{-- Conteúdo Alinhado --}}
        <div class="container relative mx-auto px-4">
            <div class="max-w-7xl mx-auto px-4 md:px-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                    {{-- Coluna Esquerda: Título e Info --}}
                    <div class="md:col-span-2 text-center md:text-left">
                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-2">
                            <span class="text-orange-400 font-bold text-xs uppercase tracking-wider border border-orange-400/30 px-2 py-0.5 rounded">
                                <i class="fa-solid fa-store mr-1"></i> Loja Oficial
                            </span>
                        </div>
                        <h1 class="text-xl md:text-3xl font-extrabold text-white leading-tight drop-shadow-sm">{{ $evento->nome }}</h1>
                        <p class="text-slate-400 text-sm mt-2 font-light hidden md:block">
                            Produtos exclusivos oficiais do evento.
                        </p>
                    </div>

                    {{-- Coluna Direita: Data/Hora (Estilo Stats) --}}
                    <div class="md:col-span-1 flex justify-center md:justify-end">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 flex items-center gap-x-6 text-white text-center border border-white/10 shadow-lg">
                            <div>
                                <span class="text-3xl font-bold block">{{ $evento->data_evento->format('d') }}</span>
                                <p class="text-[10px] uppercase tracking-wider text-slate-300">{{ $evento->data_evento->translatedFormat('M') }}</p>
                            </div>
                            <div class="border-l border-white/20 h-10"></div>
                            <div>
                                <span class="text-3xl font-bold block">{{ $evento->data_evento->format('H:i') }}</span>
                                <p class="text-[10px] uppercase tracking-wider text-slate-300">Horas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="bg-gray-50 min-h-screen pb-20 relative z-20">
        {{-- Removido margem negativa (-mt) para não sobrepor o cabeçalho --}}
        <div class="container mx-auto px-4 md:px-8 max-w-7xl mt-8">

            {{-- Feedback Messages --}}
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative shadow-sm z-30 flex items-center" role="alert">
                    <i class="fa-solid fa-check-circle mr-2 text-xl"></i>
                    <div>
                        <strong class="font-bold">Sucesso!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative shadow-sm z-30 flex items-center" role="alert">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-xl"></i>
                    <div>
                        <strong class="font-bold">Atenção!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- Barra de Navegação Flutuante --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 mb-10 flex flex-col sm:flex-row justify-between items-center gap-4 relative z-30">
                <a href="{{ route('eventos.public.show', $evento->slug) }}" class="inline-flex items-center px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg transition-all shadow-md group">
                    <i class="fa-solid fa-arrow-left text-sm mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    Voltar para Inscrição
                </a>

                <div class="flex items-center gap-6">
                    {{-- Resumo Carrinho (Dinâmico) --}}
                    <div class="flex items-center gap-3 pl-6 border-l border-gray-100">
                        <div class="text-right hidden sm:block">
                            <span class="block text-xs text-slate-400 font-bold uppercase tracking-wider">Seu Carrinho</span>
                            {{-- VALOR TOTAL DO CARRINHO --}}
                            <span class="block text-sm font-black text-slate-800">R$ {{ number_format($totalCarrinho ?? 0, 2, ',', '.') }}</span>
                        </div>
                        
                        {{-- ÁREA DO ÍCONE E BOTÃO IR PAGAMENTO --}}
                        <div class="flex flex-col items-center justify-center">
                            
                            {{-- Ícone do Carrinho --}}
                            <a href="{{ route('carrinho.index') }}" class="relative w-12 h-12 rounded-full bg-slate-900 text-white hover:bg-orange-600 transition-colors shadow-lg flex items-center justify-center cursor-pointer group mb-1" title="Visualizar Carrinho">
                                <i class="fa-solid fa-bag-shopping text-lg group-hover:scale-110 transition-transform"></i>
                                {{-- QTD TOTAL DO CARRINHO --}}
                                @if(($qtdCarrinho ?? 0) > 0)
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-white">{{ $qtdCarrinho }}</span>
                                @else
                                    <span class="absolute -top-1 -right-1 bg-slate-500 text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-white">0</span>
                                @endif
                            </a>

                            {{-- Link "Ir Pagamento" Moderno --}}
                            @if(($qtdCarrinho ?? 0) > 0)
                                <a href="{{ route('loja.checkout') }}" class="px-3 py-1 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white text-[10px] font-bold uppercase tracking-wide rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 whitespace-nowrap flex items-center gap-1">
                                    <span>Pagar</span> <i class="fa-solid fa-chevron-right text-[8px]"></i>
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- Grid de Produtos --}}
            @if($produtos->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @foreach($produtos as $produto)
                        @php
                            $precoProduto = $produto->valor ?? $produto->preco ?? 0;
                            $estoqueProduto = $produto->quantidade ?? $produto->estoque ?? $produto->limite_estoque ?? 999;
                            
                            $pathImagem = $produto->imagem ?? $produto->image ?? $produto->foto ?? $produto->imagem_url ?? null;
                            $temImagem = !empty($pathImagem);
                            $imagemUrl = null;

                            if ($temImagem) {
                                if (filter_var($pathImagem, FILTER_VALIDATE_URL)) {
                                    $imagemUrl = $pathImagem;
                                } else {
                                    $imagemUrl = asset('storage/' . $pathImagem);
                                }
                            }
                        @endphp

                        {{-- Card com Alpine.js --}}
                        <div class="group bg-slate-100 rounded-2xl shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-slate-200 overflow-hidden flex flex-col h-full" x-data="{ qty: 1 }">
                            
                            {{-- Área da Imagem --}}
                            <div class="relative aspect-square overflow-hidden bg-white">
                                @if($imagemUrl)
                                    <img src="{{ $imagemUrl }}" alt="{{ $produto->nome }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-white pattern-dots">
                                        <i class="fa-regular fa-image text-4xl mb-2 opacity-50"></i>
                                        <span class="text-xs font-bold uppercase tracking-widest opacity-60">Sem Foto</span>
                                    </div>
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                                    @if($estoqueProduto <= 0)
                                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-lg shadow-sm backdrop-blur-md">Esgotado</span>
                                    @elseif($estoqueProduto < 10)
                                        <span class="bg-amber-400 text-amber-900 text-xs font-bold px-3 py-1 rounded-lg shadow-sm backdrop-blur-md flex items-center gap-1">
                                            <i class="fa-solid fa-fire text-[10px]"></i> Restam {{ $estoqueProduto }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Corpo do Card --}}
                            <div class="p-5 flex flex-col flex-grow relative">
                                <div class="mb-4">
                                    <h3 class="font-bold text-lg text-slate-800 leading-tight mb-2 group-hover:text-orange-600 transition-colors line-clamp-2" title="{{ $produto->nome }}">
                                        {{ $produto->nome ?? $produto->titulo }}
                                    </h3>
                                    <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed h-10 overflow-hidden">
                                        {{ $produto->descricao }}
                                    </p>
                                </div>

                                <div class="mt-auto pt-4 border-t border-dashed border-slate-300/50">
                                    <div class="flex items-end justify-between mb-4">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Preço Unitário</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-xs font-medium text-slate-600">R$</span>
                                                <span class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($precoProduto, 2, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Controles de Ação --}}
                                    @if($estoqueProduto > 0)
                                        <div class="flex gap-2">
                                            {{-- Seletor de Quantidade --}}
                                            <div class="flex items-center bg-white rounded-lg border border-slate-300 h-10 w-28 shrink-0 shadow-sm">
                                                <button type="button" @click="if(qty > 1) qty--" class="w-9 h-full text-slate-500 hover:text-orange-600 hover:bg-slate-50 rounded-l-lg transition-colors flex items-center justify-center border-r border-slate-100">
                                                    <i class="fa-solid fa-minus text-[10px]"></i>
                                                </button>
                                                <input type="text" x-model="qty" readonly class="w-full h-full text-center text-sm font-bold text-slate-800 bg-transparent border-none focus:ring-0 p-0">
                                                <button type="button" @click="if(qty < {{ $estoqueProduto }}) qty++" class="w-9 h-full text-slate-500 hover:text-orange-600 hover:bg-slate-50 rounded-r-lg transition-colors flex items-center justify-center border-l border-slate-100">
                                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                                </button>
                                            </div>

                                            {{-- Botão Adicionar --}}
                                            <form action="{{ route('carrinho.adicionar', $produto->id) }}" method="POST" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="quantidade" x-model="qty">
                                                <button type="submit" class="w-full h-10 bg-slate-900 hover:bg-orange-600 text-white font-bold rounded-lg text-sm transition-all shadow-md hover:shadow-lg hover:shadow-orange-500/30 flex items-center justify-center gap-2 group-hover:bg-orange-600">
                                                    <span>Adicionar</span>
                                                    <i class="fa-solid fa-cart-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <button disabled class="w-full h-10 bg-gray-200 text-gray-400 font-bold rounded-lg text-sm cursor-not-allowed flex items-center justify-center gap-2 border border-gray-300">
                                            <i class="fa-solid fa-ban"></i> Indisponível
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Estado Vazio (Empty State) --}}
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-40 h-40 bg-white rounded-full flex items-center justify-center mb-6 shadow-xl shadow-slate-200/50">
                        <i class="fa-solid fa-store-slash text-6xl text-slate-200"></i>
                    </div>
                    <h2 class="text-3xl font-black text-slate-800 mb-3">Loja em breve!</h2>
                    <p class="text-slate-500 max-w-md mx-auto mb-8 text-lg">
                        O organizador ainda está preparando os produtos oficiais deste evento.
                    </p>
                    <a href="{{ route('eventos.public.show', $evento->slug) }}" class="inline-flex items-center px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-orange-500/30 hover:-translate-y-1">
                        <i class="fa-solid fa-ticket mr-2"></i>
                        Voltar para Inscrições
                    </a>
                </div>
            @endif

        </div>
    </div>
@endsection