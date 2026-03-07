<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Meu Painel') }}
        </h2>
    </x-slot>

    <style>
        .tab-btn {
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: #6b7280; /* text-gray-500 */
            border-bottom: 3px solid transparent;
            /* Move o botão 1px para cima para alinhar com a borda de baixo do contêiner */
            transform: translateY(1px);
            cursor: pointer;
        }
        .tab-btn.active-tab {
            color: #ea580c; /* text-orange-600 */
            border-bottom-color: #ea580c;
        }
        .tab-content {
            display: none; /* Escondido por defeito */
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- ==== COLUNA DA ESQUERDA (PERFIL) ==== -->
                <div class="lg:col-span-1 space-y-8">
                    
                    {{-- Card Principal: Perfil de Atleta --}}
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 md:p-8">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                                        <i class="fa-solid fa-user fa-2x"></i>
                                    </span>
                                </div>
                                <div class="ml-5">
                                    <h3 class="text-2xl font-bold text-gray-900">Olá, {{ Auth::user()->name }}!</h3>
                                    <p class="mt-1 text-lg text-gray-600">Seu painel de atleta.</p> <h1>fazer testge</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="px-6 md:px-8 pb-6 md:pb-8 pt-6">
                            <p class="text-sm text-gray-500">Este é o seu perfil. Em breve, mais informações sobre seus interesses e estatísticas.</p>
                            
                            <a href="{{ route('atleta.inscricoes') }}" class="w-full inline-flex justify-center items-center px-6 py-3 mt-6 bg-orange-600 text-white font-bold text-sm uppercase tracking-widest rounded-md shadow-lg hover:bg-orange-700 transition-all duration-300 transform hover:-translate-y-0.5 flex-shrink-0">
                                Ver Todas Inscrições
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- ==== COLUNA CENTRAL/DIREITA (CONTEÚDO) ==== -->
                <div class="lg:col-span-2 space-y-8">

                    <div class="flex space-x-2 border-b border-gray-200">
                        <button id="btn-inscricoes" class="tab-btn">Minhas Inscrições</button>
                        <button id="btn-organizador" class="tab-btn active-tab">Meus Eventos (Organizador)</button>
                    </div>

                    <div id="inscricoes-content" class="tab-content">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6 md:p-8">
                                <div class="flex items-center space-x-4 border-b border-gray-200 pb-4">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                                        <i class="fa-solid fa-ticket fa-lg"></i>
                                    </span>
                                    <h3 class="text-xl font-medium text-gray-900">Minhas Próximas Inscrições</h3>
                                </div>
                                
                                <div class="mt-6 space-y-4">
                                    {{-- Adicionada verificação de segurança !empty() --}}
                                    @if(!empty($inscricoes) && $inscricoes->isNotEmpty())
                                        @foreach($inscricoes as $inscricao)
                                            <a href="{{ $inscricao->evento ? route('eventos.public.show', $inscricao->evento->slug) : '#' }}" 
                                               title="Ver detalhes do evento {{ $inscricao->evento->nome ?? 'Evento' }}"
                                               class="block p-4 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-orange-500 transition-all duration-300">
                                                
                                                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                                                    <div>
                                                        <p class="font-semibold text-lg text-gray-800">{{ $inscricao->evento->nome ?? 'Evento não encontrado' }}</p>
                                                        <p class="text-sm text-gray-600">
                                                            @if($inscricao->evento && $inscricao->evento->data_evento)
                                                                {{ $inscricao->evento->data_evento->format('d/m/Y') }} - {{ $inscricao->evento->local ?? 'Local a definir' }}
                                                            @else
                                                                Data a definir
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <span class="text-sm text-right font-semibold px-3 py-1 rounded-full {{ $inscricao->status == 'confirmada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ ucfirst($inscricao->status) }}
                                                    </span>
                                                </div>
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="text-center p-4">
                                            <p class="text-gray-600">Você ainda não tem inscrições para os próximos eventos.</p>
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($inscricoes) && $inscricoes->count() > 0)
                                <div class="mt-6 border-t border-gray-200 pt-4">
                                    <a href="{{ route('atleta.inscricoes') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">
                                        Ver todas as minhas inscrições &rarr;
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div> 
                    
                    <div id="organizador-content" class="tab-content">
                        @if(!empty($isOrganizador) && $isOrganizador)
                            {{-- SE JÁ FOR ORGANIZADOR --}}
                            <div class="bg-gray-800 text-white overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 hover:shadow-2xl">
                                <div class="p-6 md:p-8">                                 
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 border-b border-gray-700 pb-4">
                                        <div class="flex items-center space-x-4">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-indigo-500 text-white">
                                                <i class="fa-solid fa-sitemap fa-lg"></i>
                                            </span>
                                            <h3 class="text-xl font-medium text-white">Minhas Organizações</h3>
                                        </div>
                                        
                                        {{-- 
                                            COMENTÁRIO SOBRE O BOTÃO:
                                            Este link está CORRETO. Ele aponta para 'organizador.organizacao.create'.
                                            Se ele está a redirecionar para o dashboard, o problema NÃO ESTÁ AQUI.
                                            
                                            O problema é o CACHE DE ROTAS ou o seu CONTROLLER.
                                            
                                            SOLUÇÃO: Aceda ao terminal do seu servidor (SSH) e execute:
                                            php artisan route:clear
                                        --}}
                                        <a href="{{ route('organizador.organizacao.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition ease-in-out duration-150 shadow-sm">
                                            <i class="fa-solid fa-plus mr-2"></i>
                                            Cadastrar Organização
                                        </a>
                                    </div> 
                                    <p class="mt-6 text-sm text-gray-300">Selecione a organização que deseja gerir:</p>
                                    
                                    <div class="mt-4 space-y-3">
                                        {{-- Adicionada verificação de segurança !empty() --}}
                                        @if(!empty($organizacoes) && $organizacoes->isNotEmpty())
                                            @foreach($organizacoes as $org)
                                                <a href="{{ route('organizador.dashboard') }}?org_id={{ $org->id }}" 
                                                   class="block p-4 rounded-lg border border-gray-700 bg-gray-700/50 hover:bg-gray-700 hover:border-indigo-500 transition-all duration-300 transform hover:scale-[1.02]">
                                                    <p class="font-semibold text-lg text-white">{{ $org->nome_fantasia ?? $org->nome }}</p> 
                                                </a>
                                            @endforeach
                                        @else
                                            <div class="text-center p-6 bg-gray-700/50 rounded-lg">
                                                <p class="text-gray-300">
                                                    Você ainda não tem nenhuma organização. Comece criando uma!
                                                </D>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- SE AINDA NÃO FOR ORGANIZADOR --}}
                            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-2 border-dashed border-gray-300 hover:border-orange-500 hover:shadow-2xl transition-all duration-300">
                                <div class="p-6 md:p-8 text-center">
                                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 text-gray-500">
                                        <i class="fa-solid fa-plus fa-2x"></i>
                                    </div>
                                    <h3 class="mt-5 text-xl font-bold text-gray-900">Tornar-se Organizador</h3>
                                    <p class="mt-3 text-base text-gray-600 max-w-md mx-auto">Pronto para o próximo nível? Crie a sua própria organização e comece a publicar os seus eventos na plataforma.</p>
                                    
                                    {{-- 
                                        COMENTÁRIO SOBRE O BOTÃO:
                                        Este link também está CORRETO ('organizador.organizacao.create').
                                        
                                        O problema é 100% o CACHE DE ROTAS.
                                        
                                        SOLUÇÃO: Aceda ao terminal do seu servidor (SSH) e execute:
                                        php artisan route:clear
                                    --}}
                                    <a href="{{ route('organizador.organizacao.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 mt-6 bg-gray-800 text-white font-semibold text-sm uppercase tracking-widest rounded-md hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-0.5 shadow-lg">
                                        Começar Agora
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                </div>
                <!-- ==== FIM DA COLUNA CENTRAL/DIREITA ==== -->
                
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnInscricoes = document.getElementById('btn-inscricoes');
        const btnOrganizador = document.getElementById('btn-organizador');
        const contentInscricoes = document.getElementById('inscricoes-content');
        const contentOrganizador = document.getElementById('organizador-content');
        
        // Garante que os elementos existem antes de adicionar eventos
        if (!btnInscricoes || !btnOrganizador || !contentInscricoes || !contentOrganizador) {
            console.error('Elementos das abas não encontrados.');
            return;
        }

        function showTab(tabName) {
            // Esconde todo o conteúdo primeiro
            contentInscricoes.style.display = 'none';
            contentOrganizador.style.display = 'none';

            // Remove a classe ativa de todos os botões
            btnInscricoes.classList.remove('active-tab');
            btnOrganizador.classList.remove('active-tab');

            // Mostra o conteúdo e ativa o botão correto
            if (tabName === 'inscricoes') {
                contentInscricoes.style.display = 'block';
                btnInscricoes.classList.add('active-tab');
            } else if (tabName === 'organizador') {
                contentOrganizador.style.display = 'block';
                btnOrganizador.classList.add('active-tab');
            }
        }

        // Adiciona os eventos de clique
        btnInscricoes.addEventListener('click', () => showTab('inscricoes'));
        btnOrganizador.addEventListener('click', () => showTab('organizador'));

        // Define o estado inicial (mostrar 'Organizador' por defeito)
        showTab('organizador');
    });
    </script>

</x-app-layout>