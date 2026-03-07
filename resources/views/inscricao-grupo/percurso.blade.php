@extends('layouts.public')

@section('title', 'Percurso e categoria - Inscrição em grupo')

@section('content')
<div class="min-h-screen bg-slate-50 py-8 sm:py-12 px-3 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <a href="{{ route('inscricao-grupo.atletas', $evento) }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 mb-4 sm:mb-6">
            <i class="fa-solid fa-arrow-left"></i> Voltar (atletas)
        </a>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 px-4 sm:px-6 py-4">
                <h1 class="text-lg sm:text-xl font-black text-white">Percurso e categoria</h1>
                <p class="text-orange-100 text-sm mt-1">{{ $evento->nome }}</p>
                <p class="text-orange-200 text-xs mt-2">Etapa 2 de 3 — Selecione percurso e categoria para cada atleta (respeitando idade e gênero)</p>
            </div>

            <div class="p-4 sm:p-6">
                @if(session('info'))
                    <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-3 rounded-r text-sm text-blue-800">
                        {{ session('info') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-r text-sm text-red-700">
                        <p class="font-semibold">Corrija os erros abaixo:</p>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $equipeSelecionada = (int)($dados['equipe_id'] ?? 0);
                    $usarEquipePorAtleta = !empty($dados['equipe_por_atleta']);
                @endphp
                <form method="POST" action="{{ route('inscricao-grupo.percurso.store', $evento) }}" id="form-percurso" x-data="{ mesmaEquipe: {{ $usarEquipePorAtleta ? 'false' : 'true' }} }">
                    @csrf
                    <input type="hidden" name="mesma_equipe" id="mesma_equipe_hidden" value="{{ $usarEquipePorAtleta ? '0' : '1' }}">
                    {{-- Hidden sempre enviado: select pode ficar disabled por Alpine; hidden espelha o valor --}}
                    <input type="hidden" name="equipe_id" id="equipe_id_hidden" value="{{ $equipeSelecionada ?: '' }}">

                    <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="mesma_equipe_cb" x-model="mesmaEquipe" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                            <span class="font-bold text-slate-800">Mesma equipe para todos</span>
                        </label>
                        <p class="text-xs text-slate-500 mt-1 ml-7" x-show="mesmaEquipe">Ao marcar, a equipe escolhida abaixo será aplicada a todos os atletas do grupo. Pode deixar em &quot;Nenhuma&quot; se não houver equipe.</p>
                        <p class="text-xs text-slate-600 mt-1 ml-7" x-show="!mesmaEquipe" x-cloak>Desmarque para escolher a equipe de cada atleta individualmente (exceções).</p>
                        <div class="mt-3 ml-7 flex flex-wrap items-center gap-3" x-show="mesmaEquipe" x-transition>
                            <select id="equipe_id_sel" class="w-full max-w-md rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200" :disabled="!mesmaEquipe">
                                <option value="">Nenhuma (sem equipe)</option>
                                @foreach($equipes as $equipe)
                                    <option value="{{ $equipe->id }}" {{ $equipeSelecionada === $equipe->id ? 'selected' : '' }}>{{ $equipe->nome }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('equipes.create') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-medium text-orange-600 hover:text-orange-700">
                                <i class="fa-solid fa-plus"></i> Cadastrar equipe
                            </a>
                        </div>
                    </div>
                    {{-- Tabela (desktop) --}}
                    <div class="overflow-x-auto -mx-2 sm:mx-0">
                        <table class="w-full border-collapse text-left min-w-[500px] hidden sm:table">
                            <thead>
                                <tr class="border-b-2 border-slate-200 bg-slate-50">
                                    <th class="py-3 px-3 font-bold text-slate-700 uppercase text-sm w-1/4">Atleta</th>
                                    <th class="py-3 px-3 font-bold text-slate-700 uppercase text-sm">Categoria (que se encaixa)</th>
                                    <th class="py-3 px-3 font-bold text-slate-700 uppercase text-sm w-48" x-show="!mesmaEquipe" x-cloak>Equipe (individual)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($atletasOrdenados as $atleta)
                                    @php
                                        $percursosAtleta = $percursosPorAtleta[$atleta->id] ?? collect();
                                        $categoriaSelecionada = (int)($categoriaPorAtleta[$atleta->id] ?? 0);
                                        $equipeAtleta = (int)($equipePorAtleta[$atleta->id] ?? 0);
                                    @endphp
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/50 align-top">
                                        <td class="py-3 px-3">
                                            <span class="font-medium text-slate-800">{{ $atleta->user->name ?? 'Atleta' }}</span>
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($percursosAtleta->isEmpty())
                                                <p class="text-sm text-amber-700 bg-amber-50 p-2 rounded inline-block">Nenhuma categoria disponível (idade/gênero).</p>
                                            @else
                                                <select name="categorias[{{ $atleta->id }}]" required data-categoria-desktop
                                                    class="w-full min-w-[220px] rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                                                    <option value="">Selecione...</option>
                                                    @foreach($percursosAtleta as $percurso)
                                                        <optgroup label="{{ $percurso->descricao }}">
                                                            @foreach($percurso->categorias as $categoria)
                                                                <option value="{{ $categoria->id }}" {{ $categoriaSelecionada === $categoria->id ? 'selected' : '' }}>
                                                                    {{ $categoria->nome }} — R$ {{ number_format($categoria->valor_atual ?? 0, 2, ',', '.') }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3" x-show="!mesmaEquipe" x-cloak>
                                            <select name="equipes[{{ $atleta->id }}]" class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 disabled:opacity-60" :disabled="mesmaEquipe">
                                                <option value="">Nenhuma</option>
                                                @foreach($equipes as $equipe)
                                                    <option value="{{ $equipe->id }}" {{ $equipeAtleta === $equipe->id ? 'selected' : '' }}>{{ $equipe->nome }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile: cards --}}
                    <div class="space-y-4 sm:hidden">
                        @foreach($atletasOrdenados as $atleta)
                            @php
                                $percursosAtleta = $percursosPorAtleta[$atleta->id] ?? collect();
                                $categoriaSelecionada = (int)($categoriaPorAtleta[$atleta->id] ?? 0);
                                $equipeAtleta = (int)($equipePorAtleta[$atleta->id] ?? 0);
                            @endphp
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                                <p class="font-bold text-slate-800 mb-3">{{ $atleta->user->name ?? 'Atleta' }}</p>
                                @if($percursosAtleta->isEmpty())
                                    <p class="text-sm text-amber-700 bg-amber-50 p-2 rounded">Nenhuma categoria disponível (idade/gênero).</p>
                                @else
                                    <label class="block text-xs font-medium text-slate-500 mb-1">Categoria</label>
                                    <select name="categorias[{{ $atleta->id }}]" required data-categoria-mobile
                                        class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 mb-3">
                                        <option value="">Selecione...</option>
                                        @foreach($percursosAtleta as $percurso)
                                            <optgroup label="{{ $percurso->descricao }}">
                                                @foreach($percurso->categorias as $categoria)
                                                    <option value="{{ $categoria->id }}" {{ $categoriaSelecionada === $categoria->id ? 'selected' : '' }}>
                                                        {{ $categoria->nome }} — R$ {{ number_format($categoria->valor_atual ?? 0, 2, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                @endif
                                <div x-show="!mesmaEquipe" x-cloak>
                                    <label class="block text-xs font-medium text-slate-500 mb-1">Equipe</label>
                                    <select name="equipes[{{ $atleta->id }}]" class="w-full rounded-lg border-2 border-slate-300 bg-white py-2.5 px-3 text-slate-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 disabled:opacity-60" :disabled="mesmaEquipe">
                                        <option value="">Nenhuma</option>
                                        @foreach($equipes as $equipe)
                                            <option value="{{ $equipe->id }}" {{ $equipeAtleta === $equipe->id ? 'selected' : '' }}>{{ $equipe->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($produtosOpcionais->isNotEmpty())
                        <div class="mt-8 pt-6 border-t border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-3">Itens opcionais (quantidade por atleta)</label>
                            <div class="space-y-3">
                                @foreach($produtosOpcionais as $produto)
                                    @php
                                        $oldQtd = collect($dados['produtos'] ?? [])->firstWhere('id', $produto->id);
                                        $qtd = is_array($oldQtd) ? ($oldQtd['quantidade'] ?? 0) : 0;
                                    @endphp
                                    <div class="flex flex-wrap items-center gap-3 p-3 border border-slate-200 rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <span class="font-medium text-slate-800">{{ $produto->nome }}</span>
                                            <span class="block text-sm text-slate-500">R$ {{ number_format($produto->valor, 2, ',', '.') }} / un.</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-slate-600">Qtd:</label>
                                            <input type="number" name="produtos[{{ $loop->index }}][quantidade]" value="{{ $qtd }}" min="0" class="w-20 rounded border-slate-300 text-center">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][id]" value="{{ $produto->id }}">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][tamanho]" value="">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-8 flex flex-col-reverse sm:flex-row justify-end gap-3">
                        <a href="{{ route('inscricao-grupo.atletas', $evento) }}" class="px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 text-center">Voltar</a>
                        <button type="button" id="btn-continuar-pagamento" class="px-6 py-2.5 bg-orange-500 text-white font-bold rounded-lg hover:bg-orange-600 shadow-lg">
                            Continuar para pagamento <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                    <script>
                    (function() {
                        var form = document.getElementById('form-percurso');
                        var btn = document.getElementById('btn-continuar-pagamento');
                        var mesmaEquipeCb = document.getElementById('mesma_equipe_cb');
                        var mesmaEquipeHidden = document.getElementById('mesma_equipe_hidden');
                        var equipeIdSel = document.getElementById('equipe_id_sel');
                        var equipeIdHidden = document.getElementById('equipe_id_hidden');
                        function syncHidden() {
                            if (mesmaEquipeHidden) mesmaEquipeHidden.value = (mesmaEquipeCb && mesmaEquipeCb.checked) ? '1' : '0';
                            // Sincronizar equipe: se mesma equipe, copiar valor do select para o hidden (sempre enviado)
                            if (equipeIdHidden && equipeIdSel && mesmaEquipeCb && mesmaEquipeCb.checked) {
                                equipeIdHidden.value = equipeIdSel.value || '';
                            }
                        }
                        function syncCategoriaDisabled() {
                            var isDesktop = window.matchMedia('(min-width: 640px)').matches;
                            form.querySelectorAll('[data-categoria-desktop]').forEach(function(el) { el.disabled = !isDesktop; });
                            form.querySelectorAll('[data-categoria-mobile]').forEach(function(el) { el.disabled = isDesktop; });
                        }
                        if (mesmaEquipeCb) mesmaEquipeCb.addEventListener('change', syncHidden);
                        if (equipeIdSel) {
                            equipeIdSel.addEventListener('change', function() {
                                if (equipeIdHidden) equipeIdHidden.value = this.value || '';
                                syncHidden();
                            });
                        }
                        syncCategoriaDisabled();
                        window.addEventListener('resize', syncCategoriaDisabled);
                        if (btn && form) {
                            btn.addEventListener('click', function() {
                                syncHidden();
                                syncCategoriaDisabled();
                                form.submit();
                            });
                        }
                    })();
                    </script>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
