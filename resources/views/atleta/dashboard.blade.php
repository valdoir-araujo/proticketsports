<x-app-layout>
    {{-- CABEÇALHO HERO (Sem Avatar) --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold text-blue-200 justify-center md:justify-start">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        Área do Atleta
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Olá, {{ explode(' ', $user->name)[0] }}! 👋
                    </h1>
                    <p class="text-blue-100 mt-2 text-lg font-light opacity-90">
                        Prepare-se para seus próximos desafios.
                    </p>
                </div>

                <div class="z-10">
                    <a href="{{ route('eventos.public.index') }}" class="group inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-500 text-white rounded-xl font-bold shadow-lg shadow-orange-900/40 transition-all hover:-translate-y-1 border border-orange-400/50">
                        <i class="fa-solid fa-calendar-plus mr-2 group-hover:rotate-90 transition-transform"></i> 
                        Buscar Novos Eventos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 -mt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- COLUNA LATERAL (PERFIL COM FOTO) --}}
                <div class="lg:col-span-4 space-y-6">
                    
                    {{-- Card de Perfil Profissional --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden relative group">
                        <!-- Capa do Perfil -->
                        <div class="h-32 bg-slate-800 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-800 to-indigo-900"></div>
                            <div class="absolute inset-0 opacity-30" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
                            {{-- Badge de Status --}}
                            <div class="absolute top-4 right-4">
                                <span class="px-2 py-1 rounded-md bg-green-500/20 backdrop-blur-md text-green-100 text-[10px] font-bold uppercase tracking-wide border border-green-400/30 shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-check-circle"></i> Ativo
                                </span>
                            </div>
                        </div>
                        
                        <div class="px-6 pb-6 relative">
                            <!-- Avatar Principal (LOCAL CORRETO E ÚNICO) -->
                            <div class="-mt-16 mb-4 flex justify-center">
                                <div class="h-28 w-28 rounded-2xl border-4 border-white bg-slate-100 shadow-lg overflow-hidden relative group-hover:scale-105 transition-transform duration-300 flex items-center justify-center">
                                    @if(isset($user->atleta) && $user->atleta->profile_photo_url)
                                        <img src="{{ $user->atleta->profile_photo_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover object-center">
                                    @elseif($user->profile_photo_url)
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover object-center">
                                    @else
                                        <span class="text-4xl font-bold text-slate-400">{{ substr($user->name, 0, 1) }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Dados do Usuário -->
                            <div class="text-center mb-6 px-2">
                                <h2 class="text-xl font-extrabold text-slate-800 truncate" title="{{ $user->name }}">{{ $user->name }}</h2>
                                
                                {{-- E-mail --}}
                                <div class="flex items-center justify-center gap-2 mt-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100 shrink-0">
                                        <i class="fa-regular fa-envelope text-xs"></i>
                                    </div>
                                    <span class="text-sm text-slate-500 font-medium truncate" title="{{ $user->email }}">{{ $user->email }}</span>
                                </div>
                            </div>

                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mb-6">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-slate-500 font-medium flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center text-indigo-500 shadow-sm border border-slate-200">
                                            <i class="fa-regular fa-id-card text-xs"></i>
                                        </div>
                                        CPF
                                    </span>
                                    <span class="font-bold text-slate-700 font-mono">
                                        @if(isset($user->atleta->cpf))
                                            {{ preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $user->atleta->cpf) }}
                                        @else
                                            ---
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500 font-medium flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center text-green-500 shadow-sm border border-slate-200">
                                            <i class="fa-brands fa-whatsapp text-xs"></i>
                                        </div>
                                        Celular
                                    </span>
                                    {{-- Ajuste: Busca o telefone da tabela de atletas e aplica máscara --}}
                                    <span class="font-bold text-slate-700 font-mono">
                                        @if(isset($user->atleta->telefone))
                                            {{ preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $user->atleta->telefone) }}
                                        @else
                                            ---
                                        @endif
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Resumo Rápido (Inscrições) -->
                            <div class="mb-6">
                                <div class="bg-indigo-50 rounded-xl p-3 text-center border border-indigo-100">
                                    <span class="block text-2xl font-black text-indigo-600">{{ $inscricoes->total() }}</span>
                                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Inscrições</span>
                                </div>
                            </div>

                            <a href="{{ route('profile.edit') }}" class="block w-full text-center px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm">
                                <i class="fa-solid fa-user-gear mr-2"></i> Editar Meus Dados
                            </a>
                        </div>
                    </div>
                </div>

                {{-- COLUNA PRINCIPAL (CONTEÚDO) --}}
                <div class="lg:col-span-8 space-y-8">

                    {{-- Seção: Inscrições Recentes --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 to-white">
                            <h3 class="font-bold text-slate-800 flex items-center gap-3 text-lg">
                                <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                Histórico de Inscrições
                            </h3>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse($inscricoes as $inscricao)
                                <div class="p-6 hover:bg-slate-50 transition-all duration-200 group relative overflow-hidden">
                                    {{-- Barra lateral de destaque --}}
                                    <div class="absolute left-0 top-0 bottom-0 w-1 transition-colors duration-200 
                                        {{ $inscricao->status === 'confirmada' ? 'bg-green-500' : ($inscricao->status === 'aguardando_pagamento' ? 'bg-yellow-500' : 'bg-slate-300') }}">
                                    </div>

                                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-6 pl-2">
                                        
                                        {{-- Info Evento --}}
                                        <div class="flex items-start gap-5 flex-1">
                                            {{-- Data Box Moderno --}}
                                            <div class="hidden sm:flex flex-col items-center justify-center w-16 h-16 bg-white rounded-2xl border border-slate-200 text-slate-500 flex-shrink-0 shadow-sm group-hover:border-orange-200 group-hover:shadow-orange-100 transition-all">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $inscricao->evento->data_evento->format('M') }}</span>
                                                <span class="text-2xl font-black text-slate-800 group-hover:text-orange-600">{{ $inscricao->evento->data_evento->format('d') }}</span>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    @if($inscricao->status === 'confirmada')
                                                        <span class="px-2 py-0.5 rounded-md bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-wide border border-green-200">Confirmada</span>
                                                    @elseif($inscricao->status === 'aguardando_pagamento')
                                                        <span class="px-2 py-0.5 rounded-md bg-yellow-100 text-yellow-700 text-[10px] font-bold uppercase tracking-wide border border-yellow-200 flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span> Pendente
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wide border border-slate-200">{{ ucfirst($inscricao->status) }}</span>
                                                    @endif
                                                </div>

                                                <h4 class="text-lg font-bold text-slate-800 group-hover:text-indigo-700 transition-colors leading-tight">
                                                    {{ $inscricao->evento->nome }}
                                                </h4>
                                                
                                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2 text-sm text-slate-500">
                                                    <span class="flex items-center gap-1.5 bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                                                        <i class="fa-solid fa-layer-group text-slate-400"></i> {{ $inscricao->categoria->nome }}
                                                    </span>
                                                    @if($inscricao->equipe)
                                                        <span class="flex items-center gap-1.5 bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                                                            <i class="fa-solid fa-people-group text-slate-400"></i> {{ $inscricao->equipe->nome }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Resultados (Se houver) — somente leitura — detalhes em "Ver" --}}
                                                @if($inscricao->resultado)
                                                    <div class="mt-3 flex flex-wrap items-center gap-3">
                                                        @if($inscricao->resultado->posicao_categoria)
                                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold border border-indigo-100 shadow-sm">
                                                                <i class="fa-solid fa-trophy text-yellow-500"></i> {{ $inscricao->resultado->posicao_categoria }}º Lugar
                                                            </span>
                                                        @endif
                                                        <span class="text-xs font-mono font-medium text-slate-500">
                                                            Tempo: {{ $inscricao->resultado->tempo_formatado ?? '—' }}
                                                        </span>
                                                        <a href="{{ route('inscricao.show', $inscricao->id) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Ver resultado completo</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Ações --}}
                                        <div class="flex flex-col items-end gap-3 min-w-[140px]">
                                            <div class="text-right">
                                                <span class="block text-xs text-slate-400 font-bold uppercase">Valor</span>
                                                <span class="block text-lg font-black text-slate-700">R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</span>
                                            </div>
                                            
                                            <div class="flex gap-2 w-full justify-end items-center">
                                                {{-- 📸 BOTÃO AVATAR --}}
                                                @if($inscricao->status === 'confirmada')
                                                    <a href="{{ route('inscricao.avatar', $inscricao->id) }}" class="flex flex-col items-center justify-center px-2 py-1.5 rounded-xl border border-slate-200 text-slate-400 hover:text-purple-600 hover:border-purple-200 hover:bg-purple-50 transition shadow-sm group/avatar" title="Criar Card Social">
                                                        <i class="fa-solid fa-camera text-sm mb-0.5"></i>
                                                        <span class="text-[9px] font-bold uppercase leading-none">Avatar</span>
                                                    </a>
                                                @endif

                                                {{-- ✏️ BOTÃO EDITAR (Estilizado como Avatar) --}}
                                                <a href="{{ route('inscricao.edit', $inscricao->id) }}" class="flex flex-col items-center justify-center px-2 py-1.5 rounded-xl border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition shadow-sm group/edit" title="Editar Inscrição">
                                                    <i class="fa-solid fa-pen-to-square text-sm mb-0.5"></i>
                                                    <span class="text-[9px] font-bold uppercase leading-none">Editar</span>
                                                </a>

                                                {{-- 👁️ BOTÃO VER --}}
                                                <a href="{{ route('inscricao.show', $inscricao->id) }}" class="p-2.5 rounded-xl border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition shadow-sm" title="Ver Detalhes">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                @if($inscricao->status === 'aguardando_pagamento')
                                                    <a href="{{ route('pagamento.show', $inscricao->id) }}" class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-500 text-white text-sm font-bold rounded-xl shadow-md shadow-orange-200 transition transform hover:-translate-y-0.5">
                                                        <i class="fa-regular fa-credit-card"></i> Pagar
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center bg-slate-50">
                                    <div class="mx-auto h-20 w-20 bg-white rounded-full flex items-center justify-center text-slate-300 mb-4 border border-slate-200 shadow-sm">
                                        <i class="fa-regular fa-calendar-xmark text-4xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700">Nenhuma inscrição encontrada</h3>
                                    <p class="text-sm text-slate-500 mb-6">Você ainda não se inscreveu em nenhum evento.</p>
                                    <a href="{{ route('eventos.public.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent shadow-md text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 transition transform hover:-translate-y-0.5">
                                        Ver Calendário de Eventos
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        {{-- Paginação --}}
                        @if($inscricoes->hasPages())
                            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                                {{ $inscricoes->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>