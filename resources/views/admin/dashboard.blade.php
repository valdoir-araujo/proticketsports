<x-app-layout>
    {{-- Cabeçalho da Página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Painel Administrativo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <a href="{{ route('admin.modalidades.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fa-solid fa-shapes fa-fw mr-2"></i>
                        <span>Modalidades</span>
                    </a>
                </div>
            </div>

            {{-- Grelha de Estatísticas Globais --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Card Total de Usuários --}}
                <div class="bg-white p-6 rounded-lg shadow-sm flex items-center space-x-4">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                        <i class="fa-solid fa-users fa-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total de Usuários</p>
                        <p class="text-2xl font-bold">{{ $totalUsuarios ?? 0 }}</p>
                    </div>
                </div>

                {{-- Card Total de Organizadores --}}
                <div class="bg-white p-6 rounded-lg shadow-sm flex items-center space-x-4">
                    <div class="bg-purple-100 text-purple-600 p-3 rounded-full">
                        <i class="fa-solid fa-user-tie fa-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Organizações</p>
                        <p class="text-2xl font-bold">{{ $totalOrganizacoes ?? 0 }}</p>
                    </div>
                </div>

                {{-- Card Total de Eventos --}}
                <div class="bg-white p-6 rounded-lg shadow-sm flex items-center space-x-4">
                    <div class="bg-orange-100 text-orange-600 p-3 rounded-full">
                        <i class="fa-solid fa-calendar-check fa-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Eventos Ativos</p>
                        <p class="text-2xl font-bold">{{ $totalEventosAtivos ?? 0 }}</p>
                    </div>
                </div>

                {{-- Card de Faturamento Total --}}
                <div class="bg-white p-6 rounded-lg shadow-sm flex items-center space-x-4">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full">
                        <i class="fa-solid fa-hand-holding-dollar fa-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Faturamento Global</p>
                        <p class="text-2xl font-bold">R$ {{ number_format($faturamentoGlobal ?? 0, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Ações Rápidas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-bold text-gray-800">Ações Rápidas</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    
                    {{-- 1. Analisar Visitantes --}}
                    <a href="https://analytics.google.com/" target="_blank" class="p-6 flex flex-col items-center justify-center text-center border-b sm:border-r hover:bg-gray-50 transition">
                        <div class="bg-blue-100 text-blue-600 p-4 rounded-full mb-3">
                            <i class="fa-solid fa-chart-line fa-2x"></i>
                        </div>
                        <p class="font-semibold text-gray-700">Analisar Visitantes</p>
                        <p class="text-sm text-gray-500">Abrir o painel do Google Analytics.</p>
                    </a>

                    {{-- 2. Gerenciar Banners --}}
                    <a href="{{ route('admin.banners.index') }}" class="p-6 flex flex-col items-center justify-center text-center border-b lg:border-r hover:bg-gray-50 transition">
                        <div class="bg-gray-100 text-gray-600 p-4 rounded-full mb-3">
                            <i class="fa-solid fa-images fa-2x"></i>
                        </div>
                        <p class="font-semibold text-gray-700">Gerenciar Banners</p>
                        <p class="text-sm text-gray-500">Controlar os banners da página inicial.</p>
                    </a>
                    
                    {{-- 3. Gerenciar Usuários --}}
                    <a href="{{ route('admin.usuarios.index') }}" class="p-6 flex flex-col items-center justify-center text-center border-b sm:border-r lg:border-r-0 hover:bg-gray-50 transition">
                        <div class="bg-gray-100 text-gray-600 p-4 rounded-full mb-3">
                            <i class="fa-solid fa-users-gear fa-2x"></i>
                        </div>
                        <p class="font-semibold text-gray-700">Gerenciar Usuários</p>
                        <p class="text-sm text-gray-500">Ver e editar todos os usuários.</p>
                    </a>
                    
                    {{-- 4. Supervisionar Eventos --}}
                    <a href="{{ route('admin.eventos.index') }}" class="p-6 flex flex-col items-center justify-center text-center border-b sm:border-b-0 sm:border-r hover:bg-gray-50 transition">
                        <div class="bg-gray-100 text-gray-600 p-4 rounded-full mb-3">
                            <i class="fa-solid fa-calendar-day fa-2x"></i>
                        </div>
                        <p class="font-semibold text-gray-700">Supervisionar Eventos</p>
                        <p class="text-sm text-gray-500">Ver todos os eventos da plataforma.</p>
                    </a>
                    
                    {{-- 5. Relatórios Financeiros --}}
                    <a href="{{ route('admin.relatorios.financeiros.index') }}" class="p-6 flex flex-col items-center justify-center text-center border-b lg:border-b-0 lg:border-r hover:bg-gray-50 transition">
                        <div class="bg-gray-100 text-gray-600 p-4 rounded-full mb-3">
                            <i class="fa-solid fa-chart-pie fa-2x"></i>
                        </div>
                        <p class="font-semibold text-gray-700">Relatórios Financeiros</p>
                        <p class="text-sm text-gray-500">Analisar as finanças da plataforma.</p>
                    </a>

                    {{-- 6. Configurações --}}
                    <a href="{{ route('admin.configuracoes.index') }}" class="p-6 flex flex-col items-center justify-center text-center border-b sm:border-r lg:border-b-0 hover:bg-gray-50 transition">
                        <div class="bg-gray-100 text-gray-600 p-4 rounded-full mb-3">
                            <i class="fa-solid fa-gears fa-2x"></i>
                        </div>
                        <p class="font-semibold text-gray-700">Configurações</p>
                        <p class="text-sm text-gray-500">Definir taxas e parâmetros.</p>
                    </a>

                    {{-- 7. NOVA ROTINA: Direitos de Acesso (ACL) --}}
                    {{-- ATUALIZADO: Agora aponta para a rota específica de Dashboard de ACL --}}
                    <a href="{{ route('admin.acl.dashboard') }}" class="p-6 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition">
                        <div class="bg-indigo-100 text-indigo-600 p-4 rounded-full mb-3">
                            <i class="fa-solid fa-user-shield fa-2x"></i>
                        </div>
                        <p class="font-semibold text-gray-700">Direitos de Acesso</p>
                        <p class="text-sm text-gray-500">Definir permissões e funções (ACL).</p>
                    </a>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>