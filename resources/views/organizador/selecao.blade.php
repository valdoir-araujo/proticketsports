<x-app-layout>
    {{-- CABEÇALHO HERO (Padrão Moderno) --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
        {{-- Efeitos de Fundo --}}
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold text-orange-200 justify-center md:justify-start">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                        Painel do Organizador
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Minhas Organizações
                    </h1>
                    <p class="text-slate-300 mt-2 text-lg font-light opacity-90">
                        Selecione uma organização para gerenciar seus eventos.
                    </p>
                </div>

                <div class="z-10">
                    <a href="{{ route('organizador.organizacao.create') }}" class="group inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-500 text-white rounded-xl font-bold shadow-lg shadow-orange-900/40 transition-all hover:-translate-y-1 border border-orange-400/50">
                        <i class="fa-solid fa-plus mr-2 group-hover:rotate-90 transition-transform"></i> 
                        Nova Organização
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL (Sobreposto) --}}
    <div class="relative z-20 -mt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                
                {{-- Cabeçalho da Lista --}}
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="font-bold text-slate-800 flex items-center gap-3 text-lg">
                        {{-- Ícone do Painel alterado para Laranja --}}
                        <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                            <i class="fa-solid fa-building-user"></i>
                        </div>
                        Organizações Disponíveis
                    </h3>
                    <div class="hidden sm:flex items-center gap-2 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-bold text-slate-600">{{ $organizacoes->count() }} Ativas</span>
                    </div>
                </div>

                {{-- Lista de Organizações --}}
                <div class="divide-y divide-slate-100">
                    @forelse($organizacoes as $org)
                        <div class="p-6 hover:bg-orange-50/40 transition-all duration-200 group relative overflow-hidden">
                            {{-- Barra lateral de destaque (Laranja) --}}
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500 transition-all duration-200 group-hover:w-1.5"></div>

                            <div class="flex flex-col md:flex-row justify-between md:items-center gap-6 pl-2">
                                
                                {{-- Informações da Organização --}}
                                <div class="flex items-start gap-5 flex-1">
                                    
                                    {{-- Logo Box (Hover laranja) --}}
                                    <div class="hidden sm:flex flex-col items-center justify-center w-20 h-20 bg-white rounded-2xl border border-slate-200 text-slate-300 flex-shrink-0 shadow-sm group-hover:border-orange-200 group-hover:shadow-orange-100 transition-all overflow-hidden relative">
                                        @if(!empty($org->logo_url))
                                            <img src="{{ asset('storage/'.$org->logo_url) }}" alt="{{ $org->nome }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="flex flex-col items-center justify-center w-full h-full bg-slate-50">
                                                <i class="fa-regular fa-image text-2xl mb-1 group-hover:text-orange-400 transition-colors"></i>
                                                <span class="text-[9px] font-bold uppercase text-slate-400 group-hover:text-orange-300 leading-tight">Sem Logo</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0 py-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($org->is_admin ?? false)
                                                 <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wide border border-amber-200 shadow-sm">
                                                    <i class="fa-solid fa-crown text-[9px] mr-1"></i> Admin
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wide border border-slate-200">
                                                    Membro
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="text-xl font-bold text-slate-800 group-hover:text-orange-700 transition-colors leading-tight truncate">
                                            {{ $org->nome_fantasia ?? $org->nome }}
                                        </h4>
                                        
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2 text-sm text-slate-500">
                                            <span class="flex items-center gap-1.5 group-hover:text-slate-700 transition-colors">
                                                <i class="fa-solid fa-location-dot text-slate-400 group-hover:text-orange-500 transition-colors"></i> 
                                                {{ $org->cidade->nome ?? $org->cidade ?? 'Local não informado' }} 
                                                <span class="text-slate-300">/</span> 
                                                {{ $org->cidade->estado->sigla ?? $org->estado ?? 'UF' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Botões de Ação --}}
                                <div class="flex flex-col items-end gap-2 min-w-[150px]">
                                    {{-- Botão Principal: Dashboard --}}
                                    <a href="{{ route('organizador.dashboard') }}?org_id={{ $org->id }}" 
                                       class="group/btn flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 font-bold hover:bg-orange-600 hover:text-white hover:border-orange-600 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5">
                                        Acessar Painel
                                        <i class="fa-solid fa-arrow-right-long text-slate-400 group-hover/btn:text-white transition-colors"></i>
                                    </a>

                                    {{-- Botão Secundário: Editar (NOVO) --}}
                                    <a href="{{ route('organizador.organizacao.edit', $org->id) }}" 
                                       class="flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-orange-500 transition-colors py-1 px-2 rounded hover:bg-orange-50">
                                        <i class="fa-solid fa-gear"></i> Editar Dados
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Estado Vazio (Laranja) --}}
                        <div class="p-16 text-center bg-slate-50">
                            <div class="relative w-24 h-24 mx-auto mb-6">
                                <div class="absolute inset-0 bg-orange-100 rounded-full animate-pulse opacity-50"></div>
                                <div class="relative w-24 h-24 bg-white rounded-full flex items-center justify-center text-orange-300 border border-slate-200 shadow-sm">
                                    <i class="fa-solid fa-folder-plus text-4xl"></i>
                                </div>
                            </div>
                            
                            <h3 class="text-xl font-bold text-slate-700">Comece sua jornada</h3>
                            <p class="text-slate-500 mt-2 mb-8 max-w-md mx-auto">Você ainda não possui nenhuma organização. Crie uma agora para começar a gerenciar seus eventos e campeonatos.</p>
                            
                            <a href="{{ route('organizador.organizacao.create') }}" class="inline-flex items-center px-8 py-3 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 transition shadow-lg shadow-orange-200 hover:-translate-y-1">
                                <i class="fa-solid fa-plus mr-2"></i> Criar Primeira Organização
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Botão Voltar --}}
            <div class="mt-12 text-center">
                 <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center text-sm font-bold text-slate-400 hover:text-orange-600 transition-colors px-6 py-3 rounded-full hover:bg-white hover:shadow-sm bg-slate-200/50">
                    <i class="fa-solid fa-arrow-left-long mr-2"></i> 
                    Voltar para Área do Atleta
                 </a>
            </div>
        </div>
    </div>
</x-app-layout>