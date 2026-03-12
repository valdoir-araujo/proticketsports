@extends('layouts.public')

@section('title', 'Contato - ' . config('app.name'))
@section('meta_description', 'Entre em contato com o ' . config('app.name') . '. Dúvidas sobre inscrições, eventos ou parcerias.')
@section('canonical', url()->current())

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    <header class="bg-gray-900 text-white py-16 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight">Fale Conosco</h1>
            <p class="text-lg mt-3 text-gray-300 max-w-2xl mx-auto">Escolha a área desejada e entre em contato com nossa equipe.</p>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-12 md:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($contatos as $item)
                @php
                    $bgClass = match($item->cor ?? 'orange') {
                        'blue' => 'bg-blue-100 text-blue-700',
                        'emerald' => 'bg-emerald-100 text-emerald-700',
                        'violet' => 'bg-violet-100 text-violet-700',
                        default => 'bg-orange-100 text-orange-700',
                    };
                @endphp
                <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden hover:shadow-xl hover:border-slate-200 transition-all duration-300 flex flex-col">
                    {{-- Área da foto em destaque (estilo cartão) --}}
                    <div class="bg-slate-50 border-b border-slate-100 p-6 flex justify-center">
                        @if($item->foto_url)
                            <div class="w-28 h-28 rounded-full overflow-hidden ring-4 ring-white shadow-lg border border-slate-200 shrink-0">
                                <img src="{{ asset('storage/' . $item->foto_url) }}" alt="{{ $item->nome }}" class="w-full h-full object-cover object-center">
                            </div>
                        @else
                            <div class="w-24 h-24 rounded-full {{ $bgClass }} flex items-center justify-center shrink-0 shadow-inner">
                                <i class="{{ $item->icone }} text-4xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ $item->area }}</h3>
                        <p class="text-lg font-bold text-slate-800 mb-2">{{ $item->nome }}</p>
                        @if(!empty($item->email))
                            <div class="mb-2">
                                <a href="mailto:{{ $item->email }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors">
                                    <i class="fa-solid fa-envelope text-sm"></i>
                                    <span class="truncate">{{ $item->email }}</span>
                                </a>
                            </div>
                        @endif
                        @if(!empty($item->telefone))
                            @php
                                $digits = preg_replace('/\D/', '', $item->telefone);
                                $wa = (str_starts_with($digits, '55') ? $digits : '55' . $digits);
                            @endphp
                            <div class="mt-auto pt-3 border-t border-slate-100">
                                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-green-600 hover:text-green-700 transition-colors">
                                    <i class="fa-brands fa-whatsapp text-lg"></i>
                                    {{ $item->telefone }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-12 md:mt-16 p-6 md:p-8 bg-slate-50 rounded-2xl border border-slate-100 text-center">
            <p class="text-slate-600 max-w-2xl mx-auto">
                Prefere nos encontrar nas redes sociais? Acompanhe o <strong class="text-slate-800">ProTicket Sports</strong> para novidades, resultados e dicas de eventos.
            </p>
        </div>
    </div>
@endsection
