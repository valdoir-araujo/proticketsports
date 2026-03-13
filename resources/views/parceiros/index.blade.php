@extends('layouts.public')

@section('title', 'Parceiros - ' . config('app.name'))
@section('meta_description', 'Conheça os parceiros do ' . config('app.name') . '. Empresas e profissionais que apoiam o esporte.')
@section('canonical', url()->current())

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .parceiro-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.12), 0 0 0 1px rgba(249,115,22,0.08); }
        .parceiro-card .logo-wrap::after { content: ''; position: absolute; inset-0; background: linear-gradient(180deg, transparent 50%, rgba(15,23,42,0.03) 100%); pointer-events: none; }
    </style>
@endpush

@section('content')
    {{-- Cabeçalho baixo, preto, com fundo de ícones (troféu, medalha, mídia) --}}
    <header class="relative bg-black py-8 md:py-10 overflow-hidden">
        {{-- Padrão de ícones transparentes: troféu, medalha, mídia, personalização --}}
        <div class="absolute inset-0 grid grid-cols-6 md:grid-cols-8 gap-4 md:gap-6 place-items-center opacity-[0.07] pointer-events-none" aria-hidden="true">
            @php $icons = ['fa-trophy', 'fa-medal', 'fa-video', 'fa-microphone', 'fa-camera', 'fa-shirt']; @endphp
            @foreach(range(1, 48) as $i)
                <i class="fa-solid {{ $icons[($i - 1) % 6] }} text-2xl md:text-3xl text-white"></i>
            @endforeach
        </div>
        <div class="max-w-6xl mx-auto px-4 text-center relative z-10">
            <span class="inline-block px-3 py-1 rounded-full bg-white/10 text-orange-300 text-xs font-semibold uppercase tracking-wider border border-white/20 mb-3">Parcerias</span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Nossos Parceiros</h1>
            <p class="text-slate-400 text-sm mt-2 max-w-xl mx-auto">Empresas e profissionais que apoiam o esporte e nossos eventos.</p>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-10 md:py-14 min-h-screen" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 120px);">
        @php $listaParceiros = $parceiros ?? collect(); @endphp

        @if($listaParceiros->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                @foreach($listaParceiros as $parceiro)
                    <article class="parceiro-card relative bg-white rounded-2xl overflow-hidden flex flex-col group transition-all duration-300 shadow-lg shadow-slate-200/50 border border-slate-200/60" style="border-top: 3px solid rgb(249 115 22);">
                        <a href="{{ $parceiro->site_url ?: '#' }}" {{ $parceiro->site_url ? 'target="_blank" rel="noopener"' : '' }} class="logo-wrap relative block overflow-hidden h-56 bg-gradient-to-br from-slate-100 to-slate-50 group-hover:absolute group-hover:inset-0 group-hover:h-full group-hover:z-10 transition-all duration-300">
                            @if($parceiro->logo_url)
                                <img class="w-full h-full object-contain p-5 group-hover:object-cover group-hover:p-0 group-hover:scale-105 transition-all duration-300"
                                     src="{{ asset('storage/' . $parceiro->logo_url) }}"
                                     alt="Logo {{ $parceiro->nome }}"
                                     onerror="this.onerror=null; this.src='https://placehold.co/400x200/e2e8f0/64748b?text=Parceiro'; this.classList.add('object-cover'); this.classList.remove('object-contain');">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fa-solid fa-handshake text-5xl text-orange-400/80"></i>
                                </div>
                            @endif
                            <span class="absolute top-3 right-3 z-20 px-3 py-1.5 rounded-lg bg-orange-500 text-xs font-bold text-white shadow-md">{{ $parceiro->tipo_label }}</span>
                        </a>
                        <div class="p-6 flex flex-col flex-grow relative z-0">
                            <h3 class="text-lg font-bold text-slate-800 leading-tight mb-3 group-hover:text-orange-600 transition-colors">
                                @if($parceiro->site_url)
                                    <a href="{{ $parceiro->site_url }}" target="_blank" rel="noopener" class="block">{{ $parceiro->nome }}</a>
                                @else
                                    <span class="block">{{ $parceiro->nome }}</span>
                                @endif
                            </h3>
                            @if($parceiro->descricao)
                                <div class="parceiro-descricao text-sm text-slate-600 mb-4 max-h-40 overflow-y-auto leading-relaxed [&_ul]:list-disc [&_ul]:pl-4 [&_ol]:list-decimal [&_ol]:pl-4 [&_p]:mb-1 [&_a]:text-orange-600 [&_a]:hover:underline">
                                    {!! $parceiro->descricao !!}
                                </div>
                            @endif
                            <div class="mt-auto pt-4 border-t border-slate-100 space-y-2">
                                @if($parceiro->site_url)
                                    <a href="{{ $parceiro->site_url }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-sm text-slate-600 hover:text-orange-600 transition-colors py-1 rounded-lg hover:bg-orange-50/80 -mx-1 px-1">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 group-hover/link:bg-orange-100"><i class="fa-solid fa-globe text-xs"></i></span>
                                        <span class="truncate font-medium text-orange-600/90 hover:underline">{{ parse_url($parceiro->site_url, PHP_URL_HOST) ?? $parceiro->site_url }}</span>
                                    </a>
                                @endif
                                @if($parceiro->instagram)
                                    <a href="{{ $parceiro->instagram_url }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-sm text-slate-600 hover:text-orange-600 transition-colors py-1 rounded-lg hover:bg-orange-50/80 -mx-1 px-1">
                                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center text-pink-600 shrink-0"><i class="fa-brands fa-instagram text-xs"></i></span>
                                        <span class="truncate">{{ $parceiro->instagram_usuario ? '@' . $parceiro->instagram_usuario : 'Instagram' }}</span>
                                    </a>
                                @endif
                                @if($parceiro->telefone)
                                    @php
                                        $digits = preg_replace('/\D/', '', $parceiro->telefone);
                                        if (str_starts_with($digits, '55') && strlen($digits) > 11) { $digits = substr($digits, 2); }
                                        $whatsappNumber = '55' . $digits;
                                        $telefoneExibir = strlen($digits) === 11
                                            ? '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 5) . '-' . substr($digits, 7)
                                            : (strlen($digits) === 10 ? '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 4) . '-' . substr($digits, 6) : $parceiro->telefone);
                                    @endphp
                                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-sm text-slate-600 hover:text-green-600 transition-colors py-1 rounded-lg hover:bg-green-50/80 -mx-1 px-1">
                                        <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600 shrink-0"><i class="fa-brands fa-whatsapp text-xs"></i></span>
                                        <span class="truncate">{{ $telefoneExibir }}</span>
                                    </a>
                                @endif
                                @if($parceiro->email)
                                    <a href="mailto:{{ $parceiro->email }}" class="flex items-center gap-2.5 text-sm text-slate-600 hover:text-orange-600 transition-colors py-1 rounded-lg hover:bg-orange-50/80 -mx-1 px-1">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0"><i class="fa-regular fa-envelope text-xs"></i></span>
                                        <span class="truncate">{{ $parceiro->email }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="py-16 text-center rounded-2xl border-2 border-dashed border-slate-200 bg-white/60 shadow-sm">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-orange-100 text-orange-500 mb-4">
                    <i class="fa-solid fa-handshake text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Nenhum parceiro publicado</h3>
                <p class="text-sm text-slate-500 mt-2">Em breve divulgaremos nossos parceiros aqui.</p>
                <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 transition-colors">Voltar ao início</a>
            </div>
        @endif
    </div>
@endsection
