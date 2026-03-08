<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- Token CSRF (Essencial para Pagamentos e WhatsApp via AJAX) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name') . ' - ' . config('app.tagline'))</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Alpine.js --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Mercado Pago: SDK V2 + Device ID (obrigatório para qualidade da integração e PCI) --}}
    <script src="https://www.mercadopago.com/v2/security.js" view="checkout"></script>
    <script src="https://sdk.mercadopago.com/js/v2"></script>

    {{-- Font Awesome para Ícones --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        orange: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316', 
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    },
                    animation: {
                        'spin-slow': 'spin 3s linear infinite',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .hero-bg {
             background-image: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4)), url('https://images.unsplash.com/photo-1511994293814-355b26f1e233?q=80&w=2070&auto=format&fit=crop');
        }
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-100 flex flex-col min-h-screen overflow-x-hidden">

    {{-- MENU DE NAVEGAÇÃO PROFISSIONAL --}}
    <nav x-data="{ isOpen: false, userMenuOpen: false }" class="bg-white border-b border-slate-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                
                {{-- LADO ESQUERDO: Logo e Links --}}
                <div class="flex items-center gap-8 lg:gap-12">
                    
                    {{-- Logo --}}
                    <a href="{{ route('welcome') }}" class="flex-shrink-0 flex items-center gap-3 group">
                        <div class="relative w-10 h-10">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-700 rounded-lg -skew-x-12 shadow-lg shadow-orange-500/30 group-hover:skew-x-0 transition-transform duration-300"></div>
                            <svg class="w-full h-full text-white relative z-10 p-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13 5L5 7L3 15L11 19M13 5L21 7L19 17L11 19M13 5V19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 13L22 13" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        {{-- Tipografia com alinhamento à direita --}}
                        <div class="flex flex-col items-end leading-none select-none">
                            <span class="font-black text-xl text-slate-900 tracking-tighter uppercase">
                                Pro<span class="text-orange-600">Ticket</span>
                            </span>
                            <span class="font-bold text-[0.60rem] text-slate-400 uppercase tracking-widest">Sports</span>
                        </div>
                    </a>

                    {{-- Links Desktop --}}
                    <div class="hidden md:flex space-x-1">
                        <a href="{{ route('welcome') }}" class="px-3 py-2 rounded-md text-sm font-bold transition-colors {{ request()->routeIs('welcome') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            Início
                        </a>
                        <a href="{{ route('eventos.public.index') }}" class="px-3 py-2 rounded-md text-sm font-bold transition-colors {{ request()->routeIs('eventos.public.*') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            Eventos
                        </a>
                        
                        {{-- ⬇️ NOVO LINK: Campeonatos adicionado exatamente aqui ⬇️ --}}
                        <a href="{{ route('campeonatos.index') }}" class="px-3 py-2 rounded-md text-sm font-bold transition-colors {{ request()->routeIs('campeonatos.*') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            Campeonatos
                        </a>
                        <a href="{{ route('parceiros.index') }}" class="px-3 py-2 rounded-md text-sm font-bold transition-colors {{ request()->routeIs('parceiros.*') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            Parceiros
                        </a>
                        <a href="{{ route('contato.index') }}" class="px-3 py-2 rounded-md text-sm font-bold transition-colors {{ request()->routeIs('contato.*') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            Contato
                        </a>
                    </div>
                </div>

                {{-- LADO DIREITO: Usuário e Ações --}}
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        {{-- Menu do Usuário Logado (Dropdown) — visual melhorado --}}
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" @click.away="userMenuOpen = false" class="flex items-center gap-3 pl-3 pr-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-orange-50 hover:border-orange-200 transition-all focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:ring-offset-1 group">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white font-bold shadow-md shrink-0 overflow-hidden ring-2 ring-white">
                                    @if(Auth::user()->profile_photo_url ?? false)
                                        <img src="{{ Auth::user()->profile_photo_url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <span class="uppercase">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="text-left hidden lg:block min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate group-hover:text-orange-600 transition-colors" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-medium">Minha Conta</p>
                                </div>
                                <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform shrink-0" :class="{ 'rotate-180': userMenuOpen }"></i>
                            </button>

                            <div x-show="userMenuOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-64 origin-top-right rounded-xl bg-white shadow-xl border border-slate-100 py-1 z-50 overflow-hidden">
                                <div class="px-4 py-3 bg-gradient-to-r from-slate-50 to-slate-100/80 border-b border-slate-100">
                                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-0.5">Logado como</p>
                                    <p class="text-sm text-slate-700 truncate" title="{{ Auth::user()->email }}">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="py-1.5">
                                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-orange-100 group-hover:text-orange-600"><i class="fa-solid fa-gauge-high text-sm"></i></span>
                                        Painel Geral
                                    </a>
                                    @if(Auth::user()->tipo_usuario !== 'admin')
                                        <a href="{{ route('atleta.inscricoes') }}" class="group flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                            <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-orange-100 group-hover:text-orange-600"><i class="fa-solid fa-ticket text-sm"></i></span>
                                            Minhas Inscrições
                                        </a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-orange-100 group-hover:text-orange-600"><i class="fa-solid fa-user-gear text-sm"></i></span>
                                        Meus Dados
                                    </a>
                                </div>
                                <div class="border-t border-slate-100 py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="group flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-semibold transition-colors">
                                            <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500 group-hover:bg-red-100"><i class="fa-solid fa-arrow-right-from-bracket text-sm"></i></span>
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <a href="{{ route('login') }}" class="text-sm font-bold text-slate-600 hover:text-orange-600 transition-colors px-2">Login</a>
                            <a href="{{ route('register') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-lg shadow-sm transition-all transform hover:-translate-y-0.5">Criar Conta</a>
                        </div>
                    @endauth
                </div>

                {{-- Botão Mobile (área de toque >= 44px) --}}
                <div class="flex items-center md:hidden">
                    <button @click="isOpen = !isOpen" type="button" aria-label="Abrir menu" :aria-expanded="isOpen" class="inline-flex items-center justify-center p-3 min-w-[44px] min-h-[44px] rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                        <i class="fa-solid fa-bars text-xl" x-show="!isOpen" x-cloak></i>
                        <i class="fa-solid fa-xmark text-xl" x-show="isOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Menu Mobile --}}
        <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" class="md:hidden bg-white border-t border-slate-100">
            <div class="px-3 pt-3 pb-4 space-y-0.5">
                <a href="{{ route('welcome') }}" class="flex items-center rounded-lg px-4 py-3 min-h-[44px] text-base font-medium {{ request()->routeIs('welcome') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Início</a>
                <a href="{{ route('eventos.public.index') }}" class="flex items-center rounded-lg px-4 py-3 min-h-[44px] text-base font-medium {{ request()->routeIs('eventos.public.*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Eventos</a>
                <a href="{{ route('campeonatos.index') }}" class="flex items-center rounded-lg px-4 py-3 min-h-[44px] text-base font-medium {{ request()->routeIs('campeonatos.*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Campeonatos</a>
                <a href="{{ route('parceiros.index') }}" class="flex items-center rounded-lg px-4 py-3 min-h-[44px] text-base font-medium {{ request()->routeIs('parceiros.*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Parceiros</a>
                <a href="{{ route('contato.index') }}" class="flex items-center rounded-lg px-4 py-3 min-h-[44px] text-base font-medium {{ request()->routeIs('contato.*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Contato</a>
            </div>
            
            <div class="pt-4 pb-4 border-t border-slate-100">
                @auth
                    <div class="flex items-center gap-3 px-4 py-3 mx-2 mb-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white font-bold shrink-0 overflow-hidden ring-2 ring-white shadow">
                            @if(Auth::user()->profile_photo_url ?? false)
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="" class="h-full w-full object-cover">
                            @else
                                <span class="uppercase text-lg">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="space-y-0.5 px-2">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 min-h-[44px] text-base font-medium text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                            <i class="fa-solid fa-gauge-high w-5 text-slate-400 text-center shrink-0"></i> Painel Geral
                        </a>
                        @if(Auth::user()->tipo_usuario !== 'admin')
                            <a href="{{ route('atleta.inscricoes') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 min-h-[44px] text-base font-medium text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                <i class="fa-solid fa-ticket w-5 text-slate-400 text-center shrink-0"></i> Minhas Inscrições
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 min-h-[44px] text-base font-medium text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                            <i class="fa-solid fa-user-gear w-5 text-slate-400 text-center shrink-0"></i> Meus Dados
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full rounded-lg px-4 py-3 min-h-[44px] text-base font-semibold text-red-600 hover:bg-red-50 transition-colors text-left">
                                <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center shrink-0"></i> Sair
                            </button>
                        </form>
                    </div>
                @else
                    <div class="px-4 space-y-2">
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-3 min-h-[44px] flex items-center justify-center border border-slate-300 rounded-lg text-slate-700 font-bold hover:bg-slate-50">Login</a>
                        <a href="{{ route('register') }}" class="block w-full text-center px-4 py-3 min-h-[44px] flex items-center justify-center bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700">Criar Conta</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    {{-- CONTEÚDO PRINCIPAL --}}
    <main class="flex-grow">
        @yield('content')
    </main>

    {{-- RODAPÉ FIXO E SEGURO --}}
    <footer class="bg-slate-900 text-gray-400 text-sm pt-12 pb-8 border-t border-slate-800 mt-12">
        <div class="container mx-auto px-4">
            
            <div class="max-w-7xl mx-auto">
                {{-- Grid Responsivo: 1 col (Mobile), 2 cols (Tablet), 4 cols (Desktop) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 text-center md:text-left">
                    
                    {{-- Coluna 1: Institucional --}}
                    <div>
                        <h3 class="text-base font-bold text-white uppercase mb-4 tracking-wider">Institucional</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="hover:text-orange-500 transition-colors">Sobre Nós</a></li>
                            <li><a href="#" class="hover:text-orange-500 transition-colors">Como funciona</a></li>
                            <li><a href="{{ route('politica.privacidade') }}" class="hover:text-orange-500 transition-colors">Política de Privacidade</a></li>
                            <li><a href="{{ route('politica.privacidade') }}" class="hover:text-orange-500 transition-colors">Termos de Uso</a></li>
                            <li><a href="#" class="hover:text-orange-500 transition-colors">Área do Organizador</a></li>
                        </ul>
                    </div>

                    {{-- Coluna 2: Contato e Social --}}
                    <div>
                        <h3 class="text-base font-bold text-white uppercase mb-4 tracking-wider">Fale Conosco</h3>
                        <ul class="space-y-3">
                            <li class="flex items-center justify-center md:justify-start gap-2">
                                <i class="fa-solid fa-address-card text-orange-500"></i>
                                <a href="{{ route('contato.index') }}" class="hover:text-white transition-colors">Página de contato</a>
                            </li>
                            <li class="flex items-center justify-center md:justify-start gap-2">
                                <i class="fa-solid fa-envelope text-orange-500"></i>
                                <a href="mailto:admin@proticketsports.com.br" class="hover:text-white transition-colors">admin@proticketsports.com.br</a>
                            </li>
                            <li class="flex items-center justify-center md:justify-start gap-2">
                                <i class="fa-brands fa-whatsapp text-green-500 text-lg"></i>
                                <a href="https://wa.me/5546988352725" target="_blank" class="hover:text-white transition-colors">(46) 9 8835-2725</a>
                            </li>
                        </ul>
                        
                        <div class="mt-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Siga-nos</h4>
                            <div class="flex space-x-4 justify-center md:justify-start">
                                <a href="#" class="text-xl text-gray-400 hover:text-blue-500 transition"><i class="fa-brands fa-facebook"></i></a>
                                <a href="https://www.instagram.com/proticketsports" target="_blank" class="text-xl text-gray-400 hover:text-pink-500 transition"><i class="fa-brands fa-instagram"></i></a>
                                <a href="#" class="text-xl text-gray-400 hover:text-red-500 transition"><i class="fa-brands fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 3: Segurança (SSL e Google) --}}
                    <div>
                        <h3 class="text-base font-bold text-white uppercase mb-4 tracking-wider">Segurança</h3>
                        <div class="flex flex-col items-center md:items-start space-y-4">
                            
                            {{-- Selo SSL --}}
                            <div class="flex items-center space-x-3 bg-slate-800 p-3 rounded-lg border border-slate-700 w-full max-w-[200px]">
                                <div class="bg-green-500/20 p-2 rounded-full">
                                    <i class="fa-solid fa-lock text-green-500 text-xl"></i>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs text-gray-300 font-bold uppercase">Site Seguro</p>
                                    <p class="text-[10px] text-gray-500">Certificado SSL Ativo</p>
                                </div>
                            </div>

                            {{-- Link Google Safe Browsing --}}
                            <a href="https://transparencyreport.google.com/safe-browsing/search?url=proticketsports.com.br" target="_blank" class="text-xs text-gray-400 hover:text-green-400 transition flex items-center gap-2 group">
                                <i class="fa-brands fa-google text-gray-500 group-hover:text-green-400 transition"></i>
                                Verificar no Google Safe Browsing
                            </a>

                            <div class="text-xs text-gray-500 mt-1">
                                <i class="fa-solid fa-shield-halved mr-1"></i> Proteção de Dados (LGPD)
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 4: Pagamento --}}
                    <div>
                        <h3 class="text-base font-bold text-white uppercase mb-4 tracking-wider">Pagamento</h3>
                        <p class="text-xs text-gray-500 mb-3">Processado com segurança por:</p>
                        
                        <div class="inline-block bg-white rounded px-3 py-2 mb-4">
                            <span class="text-blue-500 font-bold text-sm flex items-center gap-1">
                                <i class="fa-solid fa-handshake"></i> Mercado Pago
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-3 justify-center md:justify-start text-2xl">
                            <i class="fa-brands fa-pix text-teal-400" title="Pix"></i>
                            <i class="fa-brands fa-cc-mastercard text-white" title="Mastercard"></i>
                            <i class="fa-brands fa-cc-visa text-white" title="Visa"></i>
                            <i class="fa-solid fa-barcode text-white" title="Boleto"></i>
                        </div>
                    </div>

                </div>

                {{-- Rodapé Inferior --}}
                <div class="mt-12 border-t border-slate-700 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-center md:text-left">
                        <p class="text-xs text-gray-500">&copy; {{ date('Y') }} Proticketsports. Todos os direitos reservados.</p>
                        <p class="text-[10px] text-gray-600 mt-1">CNPJ: 22.964.299/0001-37</p>
                    </div>
                    <div class="text-xs text-gray-600 flex items-center gap-1">
                        Desenvolvido com <i class="fa-solid fa-code text-orange-600"></i> e Tecnologia Segura.
                    </div>
                </div>
            </div>

        </div>
    </footer>

    {{-- BOTÃO FLUTUANTE WHATSAPP --}}
    <a href="https://wa.me/5546988352725?text=Olá! Preciso de ajuda no Proticketsports."
        target="_blank"
        class="fixed bottom-6 right-6 z-[9999] flex items-center justify-center w-14 h-14 bg-green-500 rounded-full shadow-lg hover:bg-green-600 hover:scale-110 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-300 group"
        style="box-shadow: 0 4px 14px rgba(0,200,0,0.5);"
        aria-label="Fale conosco no WhatsApp">
        
        {{-- SVG WhatsApp Oficial --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#ffffff">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.506-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.017-1.04 2.535 0 1.52 1.115 2.989 1.264 3.187.149.198 2.178 3.324 5.291 4.607 3.109 1.283 3.736 1.024 4.379.962.645-.065 1.758-.718 2.006-1.413.248-.695.248-1.339.173-1.414z"/>
        </svg>

        {{-- Tooltip --}}
        <span class="absolute right-full mr-3 bg-white text-slate-700 text-xs font-bold px-3 py-1.5 rounded-lg shadow-md opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
            Fale Conosco
        </span>
        
        {{-- Ping effect --}}
        <span class="absolute -top-1 -right-1 flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
    </a>

    {{-- Local para scripts específicos da página --}}
    @stack('scripts')

</body>
</html>