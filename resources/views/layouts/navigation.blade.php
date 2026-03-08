    <header class="bg-white shadow-md sticky top-0 z-40">
        {{-- Lógica para determinar o estado atual do usuário --}}
        @php
            $user = Auth::user();
            $isOrganizador = false;
            $isOrganizerArea = false;

            if ($user) {
                // Verifica se é organizador (pela role ou se tem organizações vinculadas).
                // O Admin NÃO entra aqui, pois ele tem botão próprio.
                $isOrganizador = ($user->isOrganizador() || $user->organizacoes()->exists()) && !$user->isAdmin();
                
                // Verifica se está atualmente em uma rota de organizador (URL começa com /organizador)
                $isOrganizerArea = request()->routeIs('organizador.*');
            }
        @endphp

        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ open: false }">
            <div class="flex h-16 items-center justify-between">
                {{-- Lado Esquerdo: Logo --}}
                <div class="flex items-center">
                    <a href="/" class="flex-shrink-0 font-bold text-xl text-orange-500 hover:text-orange-600 transition-colors">
                        ProTicketSports
                    </a>
                </div>

                {{-- Lado Direito: Menus (Desktop) --}}
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        @auth
                            <div class="flex items-center space-x-3">
                                
                                {{-- 1. BOTÃO DE TROCA DE PERFIL (Para Organizadores que também são Atletas) --}}
                                @if($isOrganizador)
                                    @if($isOrganizerArea)
                                        {{-- Cenario: Está gerindo eventos -> Quer ver suas inscrições --}}
                                        <a href="{{ route('atleta.inscricoes') }}" 
                                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors"
                                        title="Ver minhas inscrições como atleta">
                                            <i class="fa-solid fa-person-running mr-2 text-orange-500"></i> Área do Atleta
                                        </a>
                                    @else
                                        {{-- Cenario: Está como atleta -> Quer gerir seus eventos --}}
                                        <a href="{{ route('organizador.dashboard') }}" 
                                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                                        title="Acessar painel de gestão de eventos">
                                            <i class="fa-solid fa-briefcase mr-2"></i> Painel Organizador
                                        </a>
                                    @endif
                                @endif

                                {{-- 2. BOTÃO DE ADMIN (Exclusivo) --}}
                                @if($user->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 font-bold border border-red-200 transition-colors">
                                        <i class="fa-solid fa-user-shield mr-2"></i> Painel Admin
                                    </a>
                                @endif

                                {{-- 3. LINKS COMUNS --}}
                                {{-- Início: Agora aponta diretamente para o painel do atleta (que é a home do usuário logado) --}}
                                <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors" title="Minhas Inscrições">
                                    Início
                                </a>
                                
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                                    Perfil
                                </a>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}" 
                                    onclick="event.preventDefault(); this.closest('form').submit();" 
                                    class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Sair do sistema">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    </a>
                                </form>
                            </div>
                        @else
                            {{-- VISITANTES --}}
                            <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">Entrar</a>
                            <a href="{{ route('register') }}" class="ml-4 inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 shadow-sm transition-colors">Cadastrar</a>
                        @endauth
                    </div>
                </div>
                
                {{-- Botão "Hamburger" para Mobile (área de toque >= 44px) --}}
                <div class="-mr-2 flex md:hidden">
                    <button @click="open = !open" type="button" :aria-expanded="open" aria-controls="mobile-menu" class="relative inline-flex items-center justify-center rounded-lg p-3 min-w-[44px] min-h-[44px] text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 active:bg-gray-200 transition-colors">
                        <span class="sr-only">Abrir menu principal</span>
                        <i class="fa-solid fa-bars h-6 w-6" :class="{'hidden': open, 'block': !open }"></i>
                        <i class="fa-solid fa-xmark h-6 w-6" :class="{'block': open, 'hidden': !open }"></i>
                    </button>
                </div>
            </div>

            {{-- Painel do menu mobile --}}
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="md:hidden border-t border-gray-100" id="mobile-menu">
                <div class="space-y-1 px-3 pb-4 pt-3 sm:px-4">
                    @auth
                        {{-- BOTÕES MOBILE INTELIGENTES --}}
                        @if($isOrganizador)
                            @if($isOrganizerArea)
                                <a href="{{ route('atleta.inscricoes') }}" class="block text-center rounded-lg px-4 py-3 text-base font-bold text-gray-700 bg-orange-100 hover:bg-orange-200 mb-2 min-h-[44px] flex items-center justify-center">
                                    <i class="fa-solid fa-person-running mr-2"></i> Ir para Área do Atleta
                                </a>
                            @else
                                <a href="{{ route('organizador.dashboard') }}" class="block text-center rounded-lg px-4 py-3 text-base font-bold text-white bg-indigo-600 hover:bg-indigo-700 mb-2 min-h-[44px] flex items-center justify-center">
                                    <i class="fa-solid fa-briefcase mr-2"></i> Acessar Painel Organizador
                                </a>
                            @endif
                        @endif

                        @if($user->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="block text-center rounded-lg px-4 py-3 text-base font-medium text-red-600 bg-red-50 hover:bg-red-100 font-bold border border-red-200 mb-2 min-h-[44px] flex items-center justify-center">
                                <i class="fa-solid fa-user-shield mr-2"></i> Painel Admin
                            </a>
                        @endif

                        {{-- CORREÇÃO AQUI: Link "Início" apontando para a rota correta do atleta --}}
                        <a href="{{ route('atleta.inscricoes') }}" class="block rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-100 min-h-[44px] flex items-center justify-center">Início</a>
                        
                        <a href="{{ route('profile.edit') }}" class="block rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-100 min-h-[44px] flex items-center justify-center">Meu Perfil</a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-gray-100 mt-2">
                            @csrf
                            <a href="{{ route('logout') }}" 
                            onclick="event.preventDefault(); this.closest('form').submit();" 
                            class="block rounded-lg px-4 py-3 text-base font-medium text-red-600 hover:bg-red-50 min-h-[44px] flex items-center justify-center">
                            Sair
                            </a>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-100 text-center min-h-[44px] flex items-center justify-center">Entrar</a>
                        <a href="{{ route('register') }}" class="block rounded-lg px-4 py-3 text-base font-medium text-white bg-orange-500 hover:bg-orange-600 text-center mt-2 min-h-[44px] flex items-center justify-center">Cadastrar</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>