<nav class="bg-white shadow-md">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            {{-- Logótipo ou Nome do Site --}}
            <div class="flex-shrink-0">
                <a href="/" class="text-xl font-bold text-slate-800">ProTicketSports</a>
            </div>

            {{-- Links de Navegação --}}
            <div class="flex items-center space-x-4">
                @auth
                    {{-- Se o utilizador estiver autenticado (VERSÃO SIMPLIFICADA) --}}
                    <span class="text-sm text-gray-600 hidden sm:block">Olá, {{ Auth::user()->name }}</span>

                    @if(Auth::user()->isAtleta())
                        <a href="{{ route('atleta.inscricoes') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Minhas Inscrições</a>
                    @endif

                    <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Meu Perfil</a>

                    <!-- Formulário de Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); this.closest('form').submit();"
                           class="text-sm font-medium text-red-600 hover:text-red-800">
                            Sair
                        </a>
                    </form>
                @else
                    {{-- Se o utilizador for um visitante --}}
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Login</a>
                    <a href="{{ route('register') }}" class="text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded-md">Registar</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
