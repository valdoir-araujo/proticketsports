@extends('layouts.public')

@section('title', 'Inscrição em grupo - ' . $evento->nome)

@section('content')
<div class="min-h-screen bg-gradient-to-b from-orange-50/30 via-slate-50 to-white py-10 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <a href="{{ route('eventos.public.show', $evento) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-orange-600 mb-6 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Voltar ao evento
        </a>

        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-white">Inscrição em grupo</h1>
                        <p class="text-orange-100 text-sm mt-1">{{ $evento->nome }}</p>
                        <p class="text-orange-200 text-xs mt-2">Etapa 1 de 3 — Selecione os atletas</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-white/25 text-white border border-white/30 shadow-sm">1</span>
                        <span class="h-0.5 w-8 bg-white/30 rounded"></span>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-white/15 text-white/90 border border-white/20">2</span>
                        <span class="h-0.5 w-8 bg-white/25 rounded"></span>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-white/15 text-white/80 border border-white/20">3</span>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                @if($errors->any())
                    <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                        <div class="flex gap-3">
                            <div class="mt-0.5 text-red-600"><i class="fa-solid fa-circle-exclamation"></i></div>
                            <div class="flex-1">
                                <p class="font-bold">Não foi possível continuar</p>
                                <p class="mt-1">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('inscricao-grupo.atletas.store', $evento) }}" id="form-grupo-atletas">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-3">
                            <div class="rounded-2xl border-2 border-orange-200/80 bg-gradient-to-br from-white to-orange-50/30 p-4 sm:p-5 shadow-md shadow-orange-100/40">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500 text-white text-sm font-bold"><i class="fa-solid fa-user-plus"></i></span>
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">Adicionar atletas</p>
                                        <p class="text-xs text-slate-600">Busque pela equipe ou por nome/CPF do atleta.</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Buscar equipe (opcional)</p>
                                        <div class="relative">
                                            <i class="fa-solid fa-people-group absolute left-3 top-1/2 -translate-y-1/2 text-orange-500"></i>
                                            <input type="text" id="search-equipe" placeholder="Digite o nome da equipe"
                                                class="w-full pl-10 pr-3 rounded-lg border-2 border-slate-300 bg-white py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                                        </div>
                                        <div class="mt-1.5">
                                            <div id="equipe-results" class="hidden border-2 border-slate-200 rounded-lg divide-y divide-slate-100 max-h-56 overflow-y-auto bg-white"></div>
                                            <div id="equipe-status" class="text-xs text-slate-500 mt-1.5"></div>
                                        </div>
                                    </div>

                                    <div class="border-t border-orange-200/60 pt-4">
                                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Buscar atleta</p>
                                        <div class="relative">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-orange-500"></i>
                                            <input type="text" id="search-atleta" placeholder="Nome, e-mail ou CPF (mín. 3 caracteres)"
                                                class="w-full pl-10 pr-3 rounded-lg border-2 border-orange-200 bg-orange-50/50 py-2.5 text-slate-900 placeholder:text-slate-500 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 focus:bg-white">
                                        </div>
                                        <div class="mt-1.5">
                                            <div id="search-results" class="hidden border-2 border-slate-200 rounded-lg divide-y divide-slate-100 max-h-64 overflow-y-auto bg-white"></div>
                                            <p id="search-hint" class="text-xs text-slate-500 mt-1">Dica: cole o CPF completo para achar mais rápido.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($meuAtletaId))
                                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4 sm:p-5">
                                    <label class="flex items-start gap-3">
                                        <input type="checkbox" id="incluir-me" name="incluir_me" value="1"
                                            class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                                            {{ $euJaInscrito ? 'disabled' : '' }}>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-900">Incluir-me no grupo (opcional)</p>
                                            <p class="text-xs text-slate-600 mt-0.5">Útil quando você também vai competir — se não, pode deixar desmarcado.</p>
                                            @if($euJaInscrito)
                                                <div class="mt-2 inline-flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-800">
                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                    <span>Você já está inscrito neste evento, então não pode entrar neste grupo.</span>
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="lg:col-span-2">
                            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 sm:p-5 shadow-md h-full">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-800 text-white text-sm font-bold"><i class="fa-solid fa-users"></i></span>
                                        <div>
                                            <p class="text-sm font-extrabold text-slate-900">Selecionados</p>
                                            <p class="text-xs text-slate-500">Mínimo 1 atleta para continuar.</p>
                                        </div>
                                    </div>
                                    <span id="count-atletas" class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg bg-orange-500 text-white text-sm font-bold px-2">0</span>
                                </div>

                                <ul id="lista-atletas" class="mt-4 space-y-2 min-h-[140px] border-2 border-dashed border-slate-200 rounded-xl p-3 bg-slate-50/50">
                                    @foreach($atletasOrdenados as $atleta)
                                        <li class="flex items-center justify-between gap-3 py-2 px-3 bg-white rounded-lg border-2 border-slate-200 shadow-sm" data-atleta-id="{{ $atleta->id }}">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="h-9 w-9 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-black flex-shrink-0">
                                                    {{ mb_strtoupper(mb_substr(($atleta->user->name ?? 'A'), 0, 1)) }}
                                                </div>
                                                <span class="font-semibold text-slate-800 truncate">{{ $atleta->user->name ?? 'Atleta' }}</span>
                                            </div>
                                            <input type="hidden" name="atleta_ids[]" value="{{ $atleta->id }}">
                                            <button type="button" class="remove-atleta text-slate-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Remover">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div id="empty-state" class="mt-3 text-center py-4 px-3 rounded-xl bg-slate-100/80 border border-slate-200 hidden">
                                    <div class="mx-auto h-12 w-12 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-lg">
                                        <i class="fa-solid fa-user-plus"></i>
                                    </div>
                                    <p class="mt-2 font-bold text-slate-700">Nenhum atleta selecionado</p>
                                    <p class="text-xs text-slate-500 mt-1">Use a busca ao lado para adicionar.</p>
                                </div>

                                <div class="mt-5 rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-slate-100/80 p-3.5 text-xs text-slate-600">
                                    <p class="font-bold text-slate-800 flex items-center gap-1.5"><i class="fa-solid fa-circle-info text-orange-500"></i> Como funciona</p>
                                    <ul class="list-disc list-inside mt-1.5 space-y-0.5">
                                        <li>Selecione os atletas aqui.</li>
                                        <li>Próxima etapa: categoria e equipe.</li>
                                        <li>Por fim: forma de pagamento.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-2 border-t border-slate-200">
                        <a href="{{ route('eventos.public.show', $evento) }}" class="px-5 py-2.5 border-2 border-slate-300 rounded-lg text-slate-700 font-bold hover:bg-slate-50 transition-colors">Cancelar</a>
                        <button type="submit" class="px-6 py-3 bg-orange-500 text-white font-bold rounded-lg hover:bg-orange-600 shadow-lg shadow-orange-200/50 transition-all hover:shadow-xl hover:shadow-orange-200/50">
                            Continuar <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const searchUrl = "{{ url('/api/atletas/search') }}";
    const searchEquipeUrl = "{{ url('/api/equipes/search') }}";
    const equipeAtletasBaseUrl = "{{ url('/api/equipes') }}";
    const lista = document.getElementById('lista-atletas');
    const countEl = document.getElementById('count-atletas');
    const resultsEl = document.getElementById('search-results');
    const searchInput = document.getElementById('search-atleta');
    const btnBuscar = document.getElementById('btn-buscar');
    const searchEquipeInput = document.getElementById('search-equipe');
    const equipeResultsEl = document.getElementById('equipe-results');
    const equipeStatusEl = document.getElementById('equipe-status');
    const incluirMe = document.getElementById('incluir-me');
    const meuAtletaId = {{ (int) ($meuAtletaId ?? 0) }};
    const meuNome = @json($meuNome ?? 'Você');
    const eventoId = {{ (int) $evento->id }};
    const emptyState = document.getElementById('empty-state');
    let lastController = null;
    let debounceTimer = null;
    let equipeController = null;
    let equipeDebounceTimer = null;

    function getIds() {
        return Array.from(lista.querySelectorAll('input[name="atleta_ids[]"]')).map(i => parseInt(i.value, 10));
    }

    function updateCount() {
        const n = getIds().length;
        countEl.textContent = n;
        if (emptyState) emptyState.classList.toggle('hidden', n > 0);
    }

    function addAtleta(id, nome) {
        if (getIds().indexOf(id) >= 0) return;
        const initial = (nome || 'A').trim().substring(0, 1).toUpperCase();
        const li = document.createElement('li');
        li.className = 'flex items-center justify-between gap-3 py-2 px-3 bg-white rounded-lg border-2 border-slate-200 shadow-sm';
        li.dataset.atletaId = id;
        li.innerHTML = '<div class="flex items-center gap-3 min-w-0">' +
            '<div class="h-9 w-9 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-black flex-shrink-0">' + initial + '</div>' +
            '<span class="font-semibold text-slate-800 truncate">' + (nome || 'Atleta') + '</span>' +
            '</div>' +
            '<input type="hidden" name="atleta_ids[]" value="' + id + '">' +
            '<button type="button" class="remove-atleta text-slate-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Remover"><i class="fa-solid fa-xmark"></i></button>';
        li.querySelector('.remove-atleta').addEventListener('click', function() { li.remove(); updateCount(); });
        lista.appendChild(li);
        updateCount();
    }

    function removeAtleta(id) {
        const li = lista.querySelector('li[data-atleta-id="' + id + '"]');
        if (li) li.remove();
        updateCount();
    }

    function doSearch() {
        const q = (searchInput.value || '').trim();
        if (!resultsEl) return;
        // Sempre mostra o painel; a API só retorna com >= 3 caracteres
        resultsEl.classList.remove('hidden');

        if (q.length < 3) {
            if (lastController) lastController.abort();
            const faltam = 3 - q.length;
            resultsEl.innerHTML = '<div class="p-3 text-slate-500 text-sm">Digite mais ' + faltam + ' caractere' + (faltam === 1 ? '' : 's') + ' para buscar.</div>';
            return;
        }

        resultsEl.innerHTML = '<div class="p-3 text-slate-500 text-sm flex items-center gap-2"><i class="fa-solid fa-spinner animate-spin"></i> Buscando...</div>';
        resultsEl.classList.remove('hidden');
        if (lastController) lastController.abort();
        lastController = new AbortController();
        fetch(searchUrl + '?q=' + encodeURIComponent(q), { signal: lastController.signal })
            .then(r => r.json())
            .then(data => {
                resultsEl.innerHTML = '';
                if (!data || data.length === 0) {
                    resultsEl.innerHTML = '<p class="p-3 text-slate-500 text-sm">Nenhum atleta encontrado.</p>';
                } else {
                    data.forEach(function(a) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full text-left px-3 py-2 hover:bg-orange-50 flex justify-between items-center';
                        btn.innerHTML = '<span>' + (a.nome || 'Atleta') + '</span><span class="text-xs text-slate-400">' + (a.cpf || '') + '</span>';
                        btn.addEventListener('click', function() {
                            addAtleta(a.id, a.nome);
                            resultsEl.classList.add('hidden');
                            searchInput.value = '';
                        });
                        resultsEl.appendChild(btn);
                    });
                }
                resultsEl.classList.remove('hidden');
            })
            .catch(() => {
                // ignora abort de busca anterior
                resultsEl.innerHTML = '<p class="p-3 text-red-600 text-sm">Erro na busca.</p>';
                resultsEl.classList.remove('hidden');
            });
    }

    function scheduleSearch() {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(doSearch, 250);
    }

    function scheduleEquipeSearch() {
        if (!searchEquipeInput || !equipeResultsEl) return;
        if (equipeDebounceTimer) clearTimeout(equipeDebounceTimer);
        equipeDebounceTimer = setTimeout(doEquipeSearch, 250);
    }

    function doEquipeSearch() {
        if (!searchEquipeInput || !equipeResultsEl) return;
        const q = (searchEquipeInput.value || '').trim();
        equipeResultsEl.classList.remove('hidden');

        if (q.length < 2) {
            if (equipeController) equipeController.abort();
            const faltam = 2 - q.length;
            equipeResultsEl.innerHTML = '<div class="p-3 text-slate-500 text-sm">Digite mais ' + faltam + ' caractere' + (faltam === 1 ? '' : 's') + ' para buscar equipe.</div>';
            return;
        }

        equipeResultsEl.innerHTML = '<div class="p-3 text-slate-500 text-sm flex items-center gap-2"><i class="fa-solid fa-spinner animate-spin"></i> Buscando equipes...</div>';
        if (equipeController) equipeController.abort();
        equipeController = new AbortController();
        fetch(searchEquipeUrl + '?q=' + encodeURIComponent(q), { signal: equipeController.signal })
            .then(r => r.json())
            .then(data => {
                equipeResultsEl.innerHTML = '';
                if (!data || data.length === 0) {
                    equipeResultsEl.innerHTML = '<p class="p-3 text-slate-500 text-sm">Nenhuma equipe encontrada.</p>';
                } else {
                    data.forEach(function(eq) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full text-left px-3 py-2 hover:bg-orange-50 flex items-center justify-between gap-3';
                        btn.innerHTML = '<span class="font-medium text-slate-800">' + (eq.nome || 'Equipe') + '</span><span class="text-xs text-slate-400">Selecionar</span>';
                        btn.addEventListener('click', function() {
                            carregarAtletasDaEquipe(eq.id, eq.nome);
                            equipeResultsEl.classList.add('hidden');
                            searchEquipeInput.value = (eq.nome || '');
                        });
                        equipeResultsEl.appendChild(btn);
                    });
                }
                equipeResultsEl.classList.remove('hidden');
            })
            .catch(() => {
                equipeResultsEl.innerHTML = '<p class="p-3 text-red-600 text-sm">Erro na busca de equipes.</p>';
                equipeResultsEl.classList.remove('hidden');
            });
    }

    function carregarAtletasDaEquipe(equipeId, equipeNome) {
        if (!equipeStatusEl) return;
        equipeStatusEl.innerHTML = '<span class="inline-flex items-center gap-2"><i class="fa-solid fa-spinner animate-spin"></i> Carregando atletas da equipe...</span>';

        fetch(equipeAtletasBaseUrl + '/' + encodeURIComponent(equipeId) + '/atletas?evento_id=' + encodeURIComponent(eventoId))
            .then(r => r.json())
            .then(data => {
                const atletas = (data && data.atletas) ? data.atletas : [];
                let adicionados = 0;
                let ignorados = 0;
                atletas.forEach(function(a) {
                    if (a.ja_inscrito) {
                        ignorados++;
                        return;
                    }
                    const before = getIds().length;
                    addAtleta(parseInt(a.id, 10), a.nome);
                    const after = getIds().length;
                    if (after > before) adicionados++;
                });

                if (ignorados > 0) {
                    equipeStatusEl.textContent = (adicionados > 0 ? (adicionados + ' atletas adicionados. ') : '') + ignorados + ' já inscritos foram ignorados.';
                } else {
                    equipeStatusEl.textContent = adicionados > 0
                        ? (adicionados + ' atletas adicionados da equipe "' + (equipeNome || '') + '".')
                        : 'Nenhum atleta novo para adicionar nesta equipe.';
                }
            })
            .catch(() => {
                equipeStatusEl.textContent = 'Erro ao carregar atletas da equipe.';
            });
    }

    btnBuscar && btnBuscar.addEventListener('click', function() { doSearch(); });
    searchInput && searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
        if (e.key === 'Escape') { resultsEl && resultsEl.classList.add('hidden'); }
    });
    searchInput && searchInput.addEventListener('focus', function() {
        // ao focar, já mostra dica/resultado se houver
        scheduleSearch();
    });
    searchInput && searchInput.addEventListener('input', function() {
        scheduleSearch();
    });
    document.addEventListener('click', function(e) {
        if (!resultsEl || !searchInput) return;
        const within = resultsEl.contains(e.target) || searchInput.contains(e.target) || (btnBuscar && btnBuscar.contains(e.target));
        if (!within) resultsEl.classList.add('hidden');
    });

    if (searchEquipeInput) {
        searchEquipeInput.addEventListener('input', scheduleEquipeSearch);
        searchEquipeInput.addEventListener('focus', scheduleEquipeSearch);
        searchEquipeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); doEquipeSearch(); }
            if (e.key === 'Escape') { equipeResultsEl && equipeResultsEl.classList.add('hidden'); }
        });
        document.addEventListener('click', function(e) {
            if (!equipeResultsEl || !searchEquipeInput) return;
            const within = equipeResultsEl.contains(e.target) || searchEquipeInput.contains(e.target);
            if (!within) equipeResultsEl.classList.add('hidden');
        });
    }

    lista.querySelectorAll('.remove-atleta').forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.closest('li').remove();
            updateCount();
        });
    });
    updateCount();

    // Incluir-me (opcional): adiciona/remove do grupo no UI
    if (incluirMe && meuAtletaId > 0 && !incluirMe.disabled) {
        incluirMe.addEventListener('change', function() {
            if (incluirMe.checked) addAtleta(meuAtletaId, meuNome);
            else removeAtleta(meuAtletaId);
        });
        // default: não incluir automaticamente
        incluirMe.checked = false;
        removeAtleta(meuAtletaId);
    }
})();
</script>
@endpush
