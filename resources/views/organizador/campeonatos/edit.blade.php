<x-app-layout>
    {{-- Hero no padrão do site --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-24 overflow-hidden shadow-xl">
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-600/20 blur-3xl pointer-events-none mix-blend-screen"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="text-white z-10">
                    <div class="flex items-center gap-2 mb-2 text-blue-200 text-sm font-medium">
                        <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="hover:text-white transition-colors">Campeonato</a>
                        <i class="fa-solid fa-chevron-right text-xs opacity-50"></i>
                        <span class="text-white">Editar</span>
                    </div>
                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold backdrop-blur-md">
                        <i class="fa-solid fa-trophy text-orange-400"></i>
                        {{ $campeonato->nome }}
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Editar Campeonato
                    </h1>
                    <p class="text-blue-100 mt-2 text-lg">Altere nome, ano, descrição ou logo do circuito.</p>
                </div>

                <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl font-bold text-sm text-white backdrop-blur-md transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-12 relative z-10">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="p-6 md:p-8">
                <form method="POST" action="{{ route('organizador.campeonatos.update', $campeonato) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Nome e Ano --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label for="nome" class="block text-sm font-bold text-slate-700 mb-1.5">Nome do Campeonato</label>
                            <input id="nome" name="nome" type="text" value="{{ old('nome', $campeonato->nome) }}" required autofocus
                                class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                            @error('nome')
                                <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="ano" class="block text-sm font-bold text-slate-700 mb-1.5">Ano / Temporada</label>
                            <input id="ano" name="ano" type="number" value="{{ old('ano', $campeonato->ano) }}" required min="2024"
                                class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">
                            @error('ano')
                                <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div>
                        <label for="descricao" class="block text-sm font-bold text-slate-700 mb-1.5">Descrição <span class="text-slate-400 font-normal">(opcional)</span></label>
                        <textarea id="descricao" name="descricao" rows="4" placeholder="Regulamento, critérios de pontuação, etc."
                            class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-900">{{ old('descricao', $campeonato->descricao) }}</textarea>
                        @error('descricao')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Logo --}}
                    <div>
                        <label for="logo" class="block text-sm font-bold text-slate-700 mb-1.5">Logo do Campeonato <span class="text-slate-400 font-normal">(opcional)</span></label>
                        @if($campeonato->logo_url)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ asset('storage/' . $campeonato->logo_url) }}" alt="Logo atual" class="h-20 w-20 rounded-xl object-cover border border-slate-200 shadow-sm">
                                <p class="text-sm text-slate-500">Logo atual. Envie um novo arquivo para substituir.</p>
                            </div>
                        @endif
                        <input id="logo" name="logo" type="file" accept="image/*"
                            class="block w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-bold file:cursor-pointer hover:file:bg-indigo-100 file:transition-colors rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1.5 text-xs text-slate-500">Formatos: JPG, PNG, GIF, SVG ou WebP. Máx. 2 MB.</p>
                        @error('logo')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botões --}}
                    <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-slate-100">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all">
                            <i class="fa-solid fa-check"></i> Atualizar Campeonato
                        </button>
                        <a href="{{ route('organizador.campeonatos.show', $campeonato) }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-semibold text-sm transition-colors">
                            <i class="fa-solid fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
