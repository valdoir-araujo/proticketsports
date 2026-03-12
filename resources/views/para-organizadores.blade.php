@extends('layouts.public')

@section('title', 'Plataforma de Inscrição para Eventos Esportivos - ' . config('app.name'))
@section('meta_description', 'Plataforma completa para gestão de inscrições em corridas, ciclismo e eventos esportivos. Inscrições online, pagamento PIX e cartão, check-in e resultados. Para organizadores.')
@section('canonical', url()->current())

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
<header class="bg-slate-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
        <span class="text-orange-400 font-bold tracking-widest uppercase text-xs">Para organizadores</span>
        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight mt-2">Plataforma de Inscrição para Eventos Esportivos</h1>
        <p class="text-lg mt-4 text-slate-300 max-w-2xl mx-auto">Gestão completa: inscrições online, pagamento automático, check-in no dia e resultados. Corridas, ciclismo, triathlon e mais.</p>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 py-12 md:py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100 hover:shadow-xl transition-shadow">
            <div class="w-14 h-14 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center mb-6">
                <i class="fa-solid fa-clipboard-list text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Inscrições online</h2>
            <p class="text-slate-600">Página do evento com categorias, lotes e valores. Atleta se inscreve sozinho, com ou sem login. Suporte a duplas e grupos.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100 hover:shadow-xl transition-shadow">
            <div class="w-14 h-14 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6">
                <i class="fa-brands fa-pix text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Pagamento PIX e cartão</h2>
            <p class="text-slate-600">Pagamento seguro integrado. Baixa automática das inscrições. Comprovante e recibo com QR Code para check-in.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100 hover:shadow-xl transition-shadow">
            <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-6">
                <i class="fa-solid fa-chart-pie text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Financeiro e repasse</h2>
            <p class="text-slate-600">Acompanhe receita, taxa e valor a receber. Solicite repasses e acompanhe o status. Relatórios por evento e organizador.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100 hover:shadow-xl transition-shadow">
            <div class="w-14 h-14 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-6">
                <i class="fa-solid fa-clipboard-check text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Check-in no dia</h2>
            <p class="text-slate-600">Sistema de entrega de kit por código ou QR Code. Lista de inscritos e controle de presença.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100 hover:shadow-xl transition-shadow">
            <div class="w-14 h-14 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-6">
                <i class="fa-solid fa-trophy text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Resultados e ranking</h2>
            <p class="text-slate-600">Publique resultados por categoria. Campeonatos com etapas e ranking de pontos. Numeração de peito configurável.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-100 hover:shadow-xl transition-shadow">
            <div class="w-14 h-14 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-6">
                <i class="fa-solid fa-person-running text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Focado em corrida</h2>
            <p class="text-slate-600">Percursos 5K, 10K, 21K com um clique. Campos de ritmo previsto e pelotão na inscrição. Camiseta com tamanho.</p>
        </div>
    </div>

    <div class="bg-gradient-to-r from-orange-600 to-orange-700 rounded-3xl p-8 md:p-12 text-white text-center shadow-xl">
        <h2 class="text-2xl md:text-3xl font-black mb-4">Pronto para organizar seu evento?</h2>
        <p class="text-orange-100 mb-8 max-w-xl mx-auto">Crie sua conta, cadastre sua organização e publique seu primeiro evento em minutos.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @auth
                @if(Auth::user()->organizacoes()->exists())
                    <a href="{{ route('organizador.dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-orange-600 font-bold rounded-xl hover:bg-orange-50 transition shadow-lg">
                        <i class="fa-solid fa-gauge-high mr-2"></i> Ir para o painel
                    </a>
                @else
                    <a href="{{ route('organizador.organizacao.create') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-orange-600 font-bold rounded-xl hover:bg-orange-50 transition shadow-lg">
                        <i class="fa-solid fa-building mr-2"></i> Cadastrar organização
                    </a>
                @endif
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-orange-600 font-bold rounded-xl hover:bg-orange-50 transition shadow-lg">
                    <i class="fa-solid fa-user-plus mr-2"></i> Criar conta grátis
                </a>
            @endauth
            <a href="{{ route('contato.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white/10 border-2 border-white text-white font-bold rounded-xl hover:bg-white/20 transition">
                <i class="fa-solid fa-envelope mr-2"></i> Falar com a equipe
            </a>
        </div>
    </div>
</div>
@endsection
