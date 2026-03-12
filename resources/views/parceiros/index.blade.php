@extends('layouts.public')

@section('title', 'Parceiros - ' . config('app.name'))
@section('meta_description', 'Conheça os parceiros do ' . config('app.name') . '. Empresas e profissionais que apoiam o esporte.')
@section('canonical', url()->current())

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    <header class="bg-gray-900 text-white py-16 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight">Nossos Parceiros</h1>
            <p class="text-lg mt-3 text-gray-300 max-w-2xl mx-auto">Empresas e profissionais que apoiam o esporte e nossos eventos.</p>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-4 md:p-8 min-h-screen">
        @php $listaParceiros = $parceiros ?? collect(); @endphp

        @if($listaParceiros->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($listaParceiros as $parceiro)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-orange-200 transition-all duration-300 flex flex-col group">
                        <a href="{{ $parceiro->site_url ?: '#' }}" {{ $parceiro->site_url ? 'target="_blank" rel="noopener"' : '' }} class="relative block overflow-hidden h-48 bg-slate-100">
                            <div class="absolute inset-0 bg-slate-900/5 group-hover:bg-transparent transition-colors z-10"></div>
                            @if($parceiro->logo_url)
                                <img class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
                                     src="{{ asset('storage/' . $parceiro->logo_url) }}"
                                     alt="Logo {{ $parceiro->nome }}"
                                     onerror="this.onerror=null; this.src='https://placehold.co/400x200/e2e8f0/64748b?text=Parceiro'; this.classList.add('object-cover'); this.classList.remove('object-contain');">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fa-solid fa-handshake text-6xl text-orange-400"></i>
                                </div>
                            @endif
                            <span class="absolute top-3 right-3 px-2 py-0.5 rounded bg-white/90 text-xs font-bold text-gray-700 shadow-sm">{{ $parceiro->tipo_label }}</span>
                        </a>
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-lg font-bold text-slate-900 leading-tight mb-2 group-hover:text-orange-600 transition-colors">
                                @if($parceiro->site_url)
                                    <a href="{{ $parceiro->site_url }}" target="_blank" rel="noopener" class="block">{{ $parceiro->nome }}</a>
                                @else
                                    <span class="block">{{ $parceiro->nome }}</span>
                                @endif
                            </h3>
                            @if($parceiro->descricao)
                                <div class="parceiro-descricao text-sm text-gray-600 mb-3 max-h-28 overflow-y-auto [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-1 [&_a]:text-orange-600 [&_a]:hover:underline">
                                    {!! $parceiro->descricao !!}
                                </div>
                            @endif
                            {{-- Site, Rede Social e WhatsApp — completos --}}
                            <div class="mt-auto pt-4 border-t border-gray-100 space-y-2">
                                @if($parceiro->site_url)
                                    <a href="{{ $parceiro->site_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-slate-700 hover:text-orange-600 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0"><i class="fa-solid fa-globe"></i></span>
                                        <span class="font-medium">Site:</span>
                                        <span class="truncate text-orange-600 hover:underline">{{ parse_url($parceiro->site_url, PHP_URL_HOST) ?? $parceiro->site_url }}</span>
                                    </a>
                                @endif
                                @if($parceiro->instagram)
                                    <a href="{{ $parceiro->instagram_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-slate-700 hover:text-orange-600 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center text-pink-600 shrink-0"><i class="fa-brands fa-instagram"></i></span>
                                        <span class="font-medium">Rede Social:</span>
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
                                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-slate-700 hover:text-orange-600 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600 shrink-0"><i class="fa-brands fa-whatsapp"></i></span>
                                        <span class="font-medium">WhatsApp:</span>
                                        <span class="truncate">{{ $telefoneExibir }}</span>
                                    </a>
                                @endif
                                @if($parceiro->email)
                                    <a href="mailto:{{ $parceiro->email }}" class="flex items-center gap-2 text-sm text-slate-700 hover:text-orange-600 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0"><i class="fa-regular fa-envelope"></i></span>
                                        <span class="font-medium">E-mail:</span>
                                        <span class="truncate">{{ $parceiro->email }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="col-span-full py-16 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <i class="fa-solid fa-handshake text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Nenhum parceiro publicado</h3>
                <p class="text-gray-500 mt-1">Em breve divulgaremos nossos parceiros aqui.</p>
                <a href="{{ route('welcome') }}" class="inline-block mt-4 text-orange-600 font-bold hover:underline">Voltar ao início</a>
            </div>
        @endif
    </div>
@endsection
