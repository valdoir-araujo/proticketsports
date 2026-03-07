<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- Token CSRF (Essencial para Pagamentos e WhatsApp via AJAX) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Título Dinâmico e Abrangente (Ajustado) --}}
    <title>@yield('title', 'Proticketsports - O Seu Portal de Eventos Esportivos')</title>

    {{-- Favicon (Logo Proticketsports em SVG) --}}
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ea580c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M13 5L5 7L3 15L11 19M13 5L21 7L19 17L11 19M13 5V19'/%3E%3Cpath d='M16 13L22 13'/%3E%3C/svg%3E" type="image/svg+xml">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Alpine.js --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

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
<body class="bg-gray-100 flex flex-col min-h-screen">

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

                        <a href="{{ route('loja.index') }}" class="px-3 py-2 rounded-md text-sm font-bold transition-colors {{ request()->routeIs('loja.*') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            Loja Oficial
                        </a>
                        <a href="#" class="px-3 py-2 rounded-md text-sm font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            Parceiros
                        </a>
                        <a href="#" class="px-3 py-2 rounded-md text-sm font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                            Contato
                        </a>
                    </div>
                </div>

                {{-- LADO DIREITO: Usuário e Ações --}}
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        {{-- Menu do Usuário Logado (Dropdown Profissional) --}}
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" @click.away="userMenuOpen = false" class="flex items-center gap-3 focus:outline-none group">
                                <div class="text-right hidden lg:block">
                                    <p class="text-sm font-bold text-slate-700 group-hover:text-orange-600 transition-colors">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wide">Minha Conta</p>
                                </div>
                                <div class="h-10 w-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 font-bold overflow-hidden group-hover:border-orange-300 transition-colors shadow-sm uppercase">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 group-hover:text-slate-600 transition-colors"></i>
                            </button>

                            {{-- Dropdown Content --}}
                            <div x-show="userMenuOpen" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50 divide-y divide-gray-100"
                                 style="display: none;">
                                
                                <div class="px-4 py-3 bg-slate-50">
                                    <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Logado como</p>
                                    <p class="text-sm font-medium text-slate-900 truncate" title="{{ Auth::user()->email }}">{{ Auth::user()->email }}</p>
                                </div>

                                <div class="py-1">
                                    <a href="{{ route('dashboard') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                        <i class="fa-solid fa-gauge-high mr-3 text-slate-400 group-hover:text-orange-500 w-4 text-center"></i> Painel Geral
                                    </a>
                                    
                                    @if(Auth::user()->tipo_usuario !== 'admin')
                                        <a href="{{ route('atleta.inscricoes') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                            <i class="fa-solid fa-ticket mr-3 text-slate-400 group-hover:text-orange-500 w-4 text-center"></i> Minhas Inscrições
                                        </a>
                                    @endif

                                    <a href="{{ route('profile.edit') }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                        <i class="fa-solid fa-user-gear mr-3 text-slate-400 group-hover:text-orange-500 w-4 text-center"></i> Meus Dados
                                    </a>
                                </div>

                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="group flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 font-bold transition-colors">
                                            <i class="fa-solid fa-arrow-right-from-bracket mr-3 w-4 text-center"></i> Sair
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

                {{-- Botão Mobile --}}
                <div class="flex items-center md:hidden">
                    <button @click="isOpen = !isOpen" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none transition">
                        <i class="fa-solid fa-bars text-xl" x-show="!isOpen"></i>
                        <i class="fa-solid fa-xmark text-xl" x-show="isOpen" style="display: none;"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Menu Mobile --}}
        <div x-show="isOpen" class="md:hidden bg-white border-t border-slate-100" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('welcome') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('welcome') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Início</a>
                <a href="{{ route('eventos.public.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('eventos.public.*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Eventos</a>
                
                {{-- ⬇️ NOVO LINK: Campeonatos no Mobile adicionado exatamente aqui ⬇️ --}}
                <a href="{{ route('campeonatos.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('campeonatos.*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Campeonatos</a>
                
                <a href="{{ route('loja.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('loja.*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Loja Oficial</a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">Parceiros</a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">Contato</a>
            </div>
            
            <div class="pt-4 pb-4 border-t border-slate-100">
                @auth
                    <div class="flex items-center px-5 mb-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold uppercase">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium leading-none text-slate-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium leading-none text-slate-500 mt-1">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <div class="space-y-1 px-2">
                        <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">Painel Geral</a>
                        <a href="{{ route('atleta.inscricoes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">Minhas Inscrições</a>
                        <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">Meus Dados</a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left rounded-md px-3 py-2 text-base font-medium text-red-600 hover:bg-red-50">Sair</button>
                        </form>
                    </div>
                @else
                    <div class="px-4 space-y-2">
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-bold hover:bg-slate-50">Login</a>
                        <a href="{{ route('register') }}" class="block w-full text-center px-4 py-2 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700">Criar Conta</a>
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