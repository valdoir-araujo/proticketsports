<div class="space-y-6">
    <div class="flex items-center gap-3 text-slate-700">
        <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 shrink-0">
            <i class="fa-solid fa-address-card text-xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">Contatos do Organizador</h3>
            <p class="text-sm text-slate-500">Cadastre os contatos que serão exibidos na página de inscrição (nome, telefone e cargo).</p>
        </div>
    </div>

    {{-- Formulário: adicionar ou editar --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-4">
            @if(isset($editandoContato))
                <i class="fa-solid fa-pen mr-2 text-teal-600"></i> Editar contato
            @else
                <i class="fa-solid fa-plus mr-2 text-teal-600"></i> Novo contato
            @endif
        </h4>
        @if(isset($editandoContato))
            <form action="{{ route('organizador.eventos.contatos.update', [$evento, $editandoContato]) }}" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
                @method('PATCH')
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1 w-full sm:max-w-3xl">
                    <div>
                        <x-input-label for="nome_edit" value="Nome" />
                        <x-text-input id="nome_edit" name="nome" type="text" class="mt-1 block w-full" placeholder="Ex: João Silva" value="{{ old('nome', $editandoContato->nome) }}" required />
                    </div>
                    <div>
                        <x-input-label for="telefone_edit" value="Telefone / WhatsApp" />
                        <x-text-input id="telefone_edit" name="telefone" type="text" class="mt-1 block w-full" placeholder="(00) 00000-0000" value="{{ old('telefone', $editandoContato->telefone) }}" />
                    </div>
                    <div>
                        <x-input-label for="cargo_edit" value="Cargo / Função" />
                        <x-text-input id="cargo_edit" name="cargo" type="text" class="mt-1 block w-full" placeholder="Ex: Coordenador" value="{{ old('cargo', $editandoContato->cargo) }}" />
                    </div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-lg text-sm">
                        <i class="fa-solid fa-check mr-2"></i> Atualizar
                    </button>
                    <a href="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'contatos']) }}" class="inline-flex items-center px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg text-sm">Cancelar</a>
                </div>
            </form>
        @else
            <form action="{{ route('organizador.eventos.contatos.store', $evento) }}" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1 w-full sm:max-w-3xl">
                    <div>
                        <x-input-label for="nome" value="Nome" />
                        <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" placeholder="Ex: João Silva" value="{{ old('nome') }}" required />
                    </div>
                    <div>
                        <x-input-label for="telefone" value="Telefone / WhatsApp" />
                        <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" placeholder="(00) 00000-0000" value="{{ old('telefone') }}" />
                    </div>
                    <div>
                        <x-input-label for="cargo" value="Cargo / Função" />
                        <x-text-input id="cargo" name="cargo" type="text" class="mt-1 block w-full" placeholder="Ex: Coordenador" value="{{ old('cargo') }}" />
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-lg text-sm shrink-0">
                    <i class="fa-solid fa-plus mr-2"></i> Adicionar contato
                </button>
            </form>
        @endif
    </div>

    {{-- Lista de contatos cadastrados --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide">
                <i class="fa-solid fa-list mr-2 text-slate-500"></i> Contatos cadastrados
                <span class="text-slate-400 font-normal normal-case ml-1">({{ $evento->eventoContatos->count() }})</span>
            </h4>
        </div>
        @if($evento->eventoContatos->isEmpty())
            <div class="p-8 text-center text-slate-500 text-sm">
                <i class="fa-solid fa-address-book text-3xl text-slate-200 mb-3 block"></i>
                Nenhum contato cadastrado. Use o formulário acima para adicionar.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-left text-slate-600 font-semibold">
                            <th class="py-3 px-4 w-10">#</th>
                            <th class="py-3 px-4">Nome</th>
                            <th class="py-3 px-4">Telefone / WhatsApp</th>
                            <th class="py-3 px-4">Cargo</th>
                            <th class="py-3 px-4 w-28 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evento->eventoContatos as $index => $c)
                            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-slate-400 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-medium text-slate-800">{{ $c->nome }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $c->telefone ?? '—' }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $c->cargo ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'contatos', 'editar_contato' => $c->id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-teal-50 transition" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('organizador.eventos.contatos.destroy', [$evento, $c]) }}" method="POST" class="inline" onsubmit="return confirm('Remover este contato?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
