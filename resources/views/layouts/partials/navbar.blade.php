{{-- O `x-data` do Alpine.js controla o estado de aberto/fechado do menu mobile --}}
<nav x-data="{ isOpen: false }" class="bg-white shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">

            {{-- ========================================================== --}}
            {{-- ⬇️ NOVA LOGO EM SVG IMPLEMENTADA AQUI ⬇️ --}}
            {{-- ========================================================== --}}
            <a href="{{ route('welcome') }}" class="flex items-center space-x-2" aria-label="Página Inicial Proticketsports">
                {{-- Ícone da Logo --}}
                <svg class="h-8 w-8 text-orange-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20.25 6.75L9.75 17.25L4.5 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15 6.75L9.75 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                
                {{-- Texto da Logo --}}
                <span class="text-2xl font-extrabold text-slate-800 tracking-tight">
                    Pro Ticket Sports
                </span>
            </a>

            {{-- 2. Menu Desktop (escondido em telas pequenas) --}}
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('welcome') }}" class="text-gray-600 hover:text-orange-500 font-semibold transition">Início</a>
                <a href="{{ route('eventos.public.index') }}" class="text-gray-600 hover:text-orange-500 font-semibold transition">Eventos</a>
                
                {{-- NOVO: Link para Campeonatos no Desktop --}}
                <a href="{{ route('campeonatos.index') }}" class="text-gray-600 hover:text-orange-500 font-semibold transition flex items-center gap-1">
                    <i class="fa-solid fa-trophy text-yellow-500 text-sm"></i> Campeonatos
                </a>
                
                {{-- Adicione outros links aqui, como "Calendário", "Resultados", etc. --}}
            </div>

            <div class="hidden md:flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-orange-500 font-semibold transition">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 font-semibold transition">Cadastre-se</a>
                @else
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 text-white rounded-md hover:bg-slate-900 font-semibold transition">Meu Painel</a>
                @endguest
            </div>

            {{-- 3. Botão Hambúrguer (visível apenas em telas pequenas) --}}
            <div class="md:hidden">
                <button @click="isOpen = !isOpen" class="text-gray-600 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- 4. Menu Mobile (painel que desliza) --}}
    <div x-show="isOpen" 
         @click.away="isOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-x-full"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform -translate-x-full"
         class="md:hidden fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-50"
         style="display: none;">

        <div class="p-4">
             <a href="{{ route('welcome') }}" class="text-2xl font-bold text-orange-500 mb-6 block flex items-center gap-2">
                <svg class="h-6 w-6 text-orange-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20.25 6.75L9.75 17.25L4.5 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15 6.75L9.75 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Pro Ticket
            </a>
            <nav class="flex flex-col space-y-4">
                <a href="{{ route('welcome') }}" class="text-gray-600 hover:text-orange-500 font-semibold">Início</a>
                <a href="{{ route('eventos.public.index') }}" class="text-gray-600 hover:text-orange-500 font-semibold">Eventos</a>
                
                {{-- NOVO: Link para Campeonatos no Mobile --}}
                <a href="{{ route('campeonatos.index') }}" class="text-gray-600 hover:text-orange-500 font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-yellow-500"></i> Campeonatos
                </a>
                
                <hr>

                @guest
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-orange-500 font-semibold">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 font-semibold text-center mt-2">Cadastre-se</a>
                @else
                     <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 text-white rounded-md hover:bg-slate-900 font-semibold text-center mt-2">Meu Painel</a>
                @endguest
            </nav>
        </div>
    </div>
</nav>