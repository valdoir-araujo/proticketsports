<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Categoria;
use App\Models\Cupom;
use App\Models\Equipe;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\LoteInscricao;
use App\Models\LoteInscricaoGeral;
use App\Models\ProdutoOpcional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Mail\InscricaoRecebida;

class InscricaoGrupoController extends Controller
{
    private function getUsuario(): ?User
    {
        if (Auth::check()) {
            return Auth::user();
        }
        if (session()->has('inscricao_user_id')) {
            return User::find(session('inscricao_user_id'));
        }
        return null;
    }

    private function sessionKey(Evento $evento): string
    {
        return 'inscricao_grupo_' . $evento->id;
    }

    private function getDadosGrupo(Evento $evento): array
    {
        $key = $this->sessionKey($evento);
        $data = session($key, []);
        return array_merge([
            'atleta_ids' => [],
            'percurso_id' => null,
            'categoria_id' => null,
            'categoria_por_atleta' => [], // [ atleta_id => categoria_id ]
            'equipe_id' => null, // quando "mesma equipe para todos"
            'equipe_por_atleta' => [], // [ atleta_id => equipe_id ] quando escolha individual
            'produtos' => [],
            'tipo_pagamento' => 'unico',
            'atleta_ids_pagar_agora' => [], // para tipo "parcial": quem você vai pagar agora
            'cupom_codigo' => null,
        ], $data);
    }

    private function setDadosGrupo(Evento $evento, array $data): void
    {
        session([$this->sessionKey($evento) => array_merge($this->getDadosGrupo($evento), $data)]);
        session()->save();
    }

    private function limparGrupo(Evento $evento): void
    {
        session()->forget($this->sessionKey($evento));
        session()->save();
    }

    /**
     * Retorna percursos com categorias válidas para o atleta (idade, gênero, dupla), com valor_atual.
     */
    private function getPercursosParaAtleta(Evento $evento, Atleta $atleta): \Illuminate\Support\Collection
    {
        $evento->load('lotesInscricaoGeral');
        $percursosDoEvento = $evento->percursos()->with(['categorias.lotesInscricao'])->get();
        $anoDoEvento = Carbon::parse($evento->data_evento)->year;
        $anoNascimentoAtleta = $atleta->data_nascimento ? Carbon::parse($atleta->data_nascimento)->year : now()->year;
        $idadeNoEvento = $anoDoEvento - $anoNascimentoAtleta;
        $sexoAtleta = strtolower($atleta->sexo ?? 'masculino');

        return $percursosDoEvento->map(function ($percurso) use ($idadeNoEvento, $sexoAtleta, $evento) {
            $categoriasFiltradas = $percurso->categorias->filter(function ($categoria) use ($idadeNoEvento, $sexoAtleta) {
                $generoCategoria = strtolower($categoria->genero ?? '');
                $nomeCategoria = strtolower($categoria->nome ?? '');
                $isDupla = Str::contains($nomeCategoria, ['dupla', 'mista'], true);
                $generoValido = false;
                if (in_array($generoCategoria, ['unissex', 'misto'])) {
                    $generoValido = true;
                } elseif (Str::contains($nomeCategoria, 'mista', true)) {
                    $generoValido = true;
                } elseif ($isDupla) {
                    if (Str::contains($nomeCategoria, 'feminina') && $sexoAtleta === 'feminino') $generoValido = true;
                    elseif (Str::contains($nomeCategoria, 'masculina') && $sexoAtleta === 'masculino') $generoValido = true;
                    elseif ($generoCategoria === $sexoAtleta) $generoValido = true;
                } elseif ($generoCategoria === $sexoAtleta) {
                    $generoValido = true;
                }
                $idadeValida = true;
                if (!$isDupla) {
                    if ($categoria->idade_minima !== null && $idadeNoEvento < $categoria->idade_minima) $idadeValida = false;
                    if ($categoria->idade_maxima !== null && $idadeNoEvento > $categoria->idade_maxima) $idadeValida = false;
                }
                return $generoValido && $idadeValida;
            })->map(function ($categoria) use ($evento) {
                $loteAtivo = $categoria->lotesInscricao()
                    ->where('data_inicio', '<=', now())
                    ->where('data_fim', '>=', now())
                    ->first();
                if (!$loteAtivo) {
                    $loteAtivo = $evento->getLoteAtivoParaCategoria($categoria);
                }
                $categoria->valor_atual = $loteAtivo ? $loteAtivo->valor : null;
                return $categoria;
            })->filter(fn ($c) => $c->valor_atual !== null);
            $percurso->setRelation('categorias', $categoriasFiltradas);
            return $percurso;
        })->filter(fn ($p) => $p->categorias->isNotEmpty());
    }

    /** Entrada: identificação (reutiliza fluxo da inscrição individual). */
    public function identificacao(Evento $evento): View|RedirectResponse
    {
        $user = $this->getUsuario();
        if (!$user) {
            session(['inscricao_intent' => 'grupo', 'inscricao_intent_evento_id' => $evento->id]);
            return redirect()->route('inscricao.identificacao', $evento)
                ->with('info', 'Identifique-se para inscrever um grupo.');
        }
        if (!$user->atleta && !$user->hasRole('admin')) {
            return redirect()->route('profile.edit')->with('info', 'Complete seu perfil de atleta para inscrever grupos.');
        }
        $this->limparGrupo($evento);
        return redirect()->route('inscricao-grupo.atletas', $evento);
    }

    /** Etapa 1: montar lista de atletas. Remove da lista quem já está inscrito (evita erro ao voltar ou session antiga). */
    public function atletas(Evento $evento): View|RedirectResponse
    {
        $user = $this->getUsuario();
        if (!$user) {
            return redirect()->route('inscricao-grupo.identificacao', $evento);
        }
        if (!$user->atleta && !$user->hasRole('admin')) {
            return redirect()->route('profile.edit')->with('info', 'Complete seu perfil de atleta.');
        }

        $dados = $this->getDadosGrupo($evento);
        $atletaIds = array_unique(array_filter($dados['atleta_ids'] ?? []));
        $jaInscritos = Inscricao::where('evento_id', $evento->id)
            ->whereIn('atleta_id', $atletaIds)
            ->whereIn('status', ['confirmada', 'aguardando_pagamento', 'pendente'])
            ->pluck('atleta_id')
            ->toArray();
        $atletaIds = array_values(array_diff($atletaIds, $jaInscritos));
        if (count($atletaIds) !== count($dados['atleta_ids'] ?? [])) {
            $this->setDadosGrupo($evento, ['atleta_ids' => $atletaIds]);
        }
        $atletas = Atleta::with('user')->whereIn('id', $atletaIds)->get()->keyBy('id');
        $atletasOrdenados = collect($atletaIds)->map(fn ($id) => $atletas->get($id))->filter();

        $meuAtletaId = $user->atleta?->id;
        $euJaInscrito = false;
        if ($meuAtletaId) {
            $euJaInscrito = Inscricao::where('evento_id', $evento->id)
                ->where('atleta_id', $meuAtletaId)
                ->whereIn('status', ['confirmada', 'aguardando_pagamento', 'pendente'])
                ->exists();
        }
        $meuNome = $user->name ?? null;

        return view('inscricao-grupo.atletas', compact('evento', 'atletasOrdenados', 'meuAtletaId', 'meuNome', 'euJaInscrito'));
    }

    /** POST: adicionar atletas e ir para percurso. */
    public function atletasStore(Request $request, Evento $evento): RedirectResponse
    {
        $user = $this->getUsuario();
        if (!$user) {
            return redirect()->route('inscricao-grupo.identificacao', $evento);
        }
        if (!$user->atleta && !$user->hasRole('admin')) {
            return redirect()->route('profile.edit')->with('info', 'Complete seu perfil de atleta.');
        }

        $ids = $request->input('atleta_ids', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        // Não incluir automaticamente o usuário logado no grupo.
        // Se ele quiser, marca "incluir_me" (e só entra se tiver atleta e não estiver já inscrito).
        $incluirMe = (bool) $request->boolean('incluir_me');
        $meuAtletaId = $user->atleta?->id;
        $euJaInscrito = false;
        if ($meuAtletaId) {
            $euJaInscrito = Inscricao::where('evento_id', $evento->id)
                ->where('atleta_id', $meuAtletaId)
                ->whereIn('status', ['confirmada', 'aguardando_pagamento', 'pendente'])
                ->exists();
        }
        if ($meuAtletaId) {
            if ($euJaInscrito) {
                // Garante que ele não entre (evita erro de "já inscrito").
                $ids = array_values(array_diff($ids, [$meuAtletaId]));
            } elseif ($incluirMe && !in_array($meuAtletaId, $ids, true)) {
                array_unshift($ids, $meuAtletaId);
            } elseif (!$incluirMe && in_array($meuAtletaId, $ids, true)) {
                $ids = array_values(array_diff($ids, [$meuAtletaId]));
            }
        }
        if (count($ids) < 1) {
            return back()->withErrors(['atleta_ids' => 'Adicione pelo menos 1 atleta para continuar.']);
        }

        $existentes = Inscricao::where('evento_id', $evento->id)
            ->whereIn('atleta_id', $ids)
            ->whereIn('status', ['confirmada', 'aguardando_pagamento', 'pendente'])
            ->pluck('atleta_id')
            ->toArray();
        if (!empty($existentes)) {
            $nomes = Atleta::with('user')->whereIn('id', $existentes)->get()->pluck('user.name')->implode(', ');
            return back()->withErrors(['atleta_ids' => "Já inscritos neste evento: {$nomes}. Remova-os da lista ou escolha outros atletas."]);
        }

        $this->setDadosGrupo($evento, ['atleta_ids' => $ids]);
        return redirect()->route('inscricao-grupo.percurso', $evento);
    }

    /** Etapa 2: percurso e categoria por atleta (respeitando idade/gênero), produtos. */
    public function percurso(Evento $evento): View|RedirectResponse
    {
        $user = $this->getUsuario();
        if (!$user) {
            return redirect()->route('inscricao-grupo.identificacao', $evento);
        }
        $dados = $this->getDadosGrupo($evento);
        $atletaIds = $dados['atleta_ids'] ?? [];
        if (count($atletaIds) < 1) {
            return redirect()->route('inscricao-grupo.atletas', $evento)->with('info', 'Selecione pelo menos 1 atleta.');
        }

        $atletas = Atleta::with('user')->whereIn('id', $atletaIds)->get()->keyBy('id');
        $atletasOrdenados = collect($atletaIds)->map(fn ($id) => $atletas->get($id))->filter();
        $categoriaPorAtleta = $dados['categoria_por_atleta'] ?? [];
        $equipePorAtleta = $dados['equipe_por_atleta'] ?? [];

        $percursosPorAtleta = [];
        foreach ($atletasOrdenados as $atleta) {
            $percursosPorAtleta[$atleta->id] = $this->getPercursosParaAtleta($evento, $atleta);
        }

        $produtosOpcionais = $evento->produtosOpcionais()->where('ativo', true)->get();
        $equipes = Equipe::orderBy('nome')->get();

        return view('inscricao-grupo.percurso', compact('evento', 'atletasOrdenados', 'percursosPorAtleta', 'categoriaPorAtleta', 'equipePorAtleta', 'produtosOpcionais', 'equipes', 'dados'));
    }

    /** POST: salvar categoria por atleta, equipe e produtos. */
    public function percursoStore(Request $request, Evento $evento): RedirectResponse
    {
        $user = $this->getUsuario();
        if (!$user) {
            return redirect()->route('inscricao-grupo.identificacao', $evento);
        }
        $dados = $this->getDadosGrupo($evento);
        $atletaIds = $dados['atleta_ids'] ?? [];
        if (count($atletaIds) < 1) {
            return redirect()->route('inscricao-grupo.atletas', $evento);
        }

        $categoriasInput = $request->input('categorias', []);
        if (!is_array($categoriasInput)) {
            $categoriasInput = [];
        }
        $categoriaPorAtleta = [];
        $atletas = Atleta::with('user')->whereIn('id', $atletaIds)->get()->keyBy('id');
        foreach ($atletaIds as $atletaId) {
            $raw = $categoriasInput[$atletaId] ?? $categoriasInput[(string) $atletaId] ?? 0;
            $catId = (int) (is_array($raw) ? (reset($raw) ?: 0) : $raw);
            if ($catId <= 0) {
                $nome = $atletas->get($atletaId)?->user?->name ?? 'Atleta';
                return back()->withErrors(['categorias' => "Selecione percurso e categoria para {$nome}."]);
            }
            $categoria = Categoria::with('percurso')->find($catId);
            if (!$categoria || $categoria->percurso->evento_id != $evento->id) {
                return back()->withErrors(['categorias' => 'Categoria inválida para este evento.']);
            }
            $atleta = $atletas->get($atletaId);
            if ($atleta) {
                $percursosAtleta = $this->getPercursosParaAtleta($evento, $atleta);
                $categoriaIdsValidos = $percursosAtleta->pluck('categorias')->flatten()->pluck('id')->toArray();
                if (!in_array($catId, $categoriaIdsValidos, true)) {
                    $nome = $atleta->user->name ?? 'Atleta';
                    return back()->withErrors(['categorias' => "A categoria escolhida não é válida para {$nome} (idade ou gênero)."]);
                }
            }
            $categoriaPorAtleta[$atletaId] = $catId;
        }

        // Equipe é opcional: normalizar vazios (sem validação que bloqueie o fluxo)
        $mesmaEquipe = $request->input('mesma_equipe', '1') === '1';
        $equipeIdInput = $request->input('equipe_id');
        if ($equipeIdInput === '' || $equipeIdInput === null) {
            $request->merge(['equipe_id' => null]);
        }
        $equipesInput = $request->input('equipes', []);
        if (is_array($equipesInput)) {
            $normalized = [];
            foreach ($equipesInput as $k => $v) {
                $normalized[$k] = ($v === '' || $v === null) ? null : (int) $v;
            }
            $request->merge(['equipes' => $normalized]);
        }

        // Validar produtos só quando vierem no request (evento com itens opcionais)
        $produtos = $request->input('produtos', []);
        if (is_array($produtos) && count($produtos) > 0) {
            $request->validate([
                'produtos' => 'array',
                'produtos.*.id' => 'required|integer|exists:produtos_opcionais,id',
                'produtos.*.quantidade' => 'required|integer|min:0',
                'produtos.*.tamanho' => 'nullable|string',
            ]);
        }
        $produtosFiltrados = is_array($produtos) ? array_filter($produtos, fn ($p) => isset($p['id']) && (int)($p['quantidade'] ?? 0) > 0) : [];

        $equipeId = null;
        $equipePorAtleta = [];
        $mesmaEquipe = $request->input('mesma_equipe', '1') === '1';
        if ($mesmaEquipe) {
            $equipeId = $request->equipe_id ? (int) $request->equipe_id : null;
        } else {
            $equipesInput = $request->input('equipes', []);
            foreach ($atletaIds as $aid) {
                $eq = (int) ($equipesInput[$aid] ?? 0);
                $equipePorAtleta[$aid] = $eq ?: null;
            }
        }

        $this->setDadosGrupo($evento, [
            'categoria_por_atleta' => $categoriaPorAtleta,
            'equipe_id' => $equipeId,
            'equipe_por_atleta' => $equipePorAtleta,
            'produtos' => $produtosFiltrados,
        ]);

        // Contorno: se a sessão não persistir no próximo request, a página de pagamento pode restaurar os dados via token
        $token = Str::random(64);
        $payload = [
            'atleta_ids' => $atletaIds,
            'categoria_por_atleta' => $categoriaPorAtleta,
            'equipe_id' => $equipeId,
            'equipe_por_atleta' => $equipePorAtleta,
            'produtos' => $produtosFiltrados,
            'evento_id' => $evento->id,
        ];
        Cache::put('inscricao_grupo_tk_' . $token, $payload, now()->addMinutes(5));

        $urlPagamento = route('inscricao-grupo.pagamento', $evento);
        return redirect($urlPagamento . '?_tk=' . urlencode($token));
    }

    /** Etapa 3: resumo (por atleta pode ter categoria/valor diferente), tipo pagamento, cupom. */
    public function pagamento(Evento $evento): View|RedirectResponse
    {
        $user = $this->getUsuario();
        if (!$user) {
            return redirect()->route('inscricao-grupo.identificacao', $evento);
        }

        // Dados do grupo: priorizar token (evita perda de equipe quando sessão não persiste)
        $dados = $this->getDadosGrupo($evento);
        $tk = request()->query('_tk');
        if (is_string($tk) && strlen($tk) > 0) {
            $payload = Cache::get('inscricao_grupo_tk_' . $tk);
            if (is_array($payload) && (int) ($payload['evento_id'] ?? 0) === (int) $evento->id) {
                $dados = array_merge($dados, [
                    'atleta_ids' => $payload['atleta_ids'] ?? [],
                    'categoria_por_atleta' => $payload['categoria_por_atleta'] ?? [],
                    'equipe_id' => $payload['equipe_id'] ?? null,
                    'equipe_por_atleta' => $payload['equipe_por_atleta'] ?? [],
                    'produtos' => $payload['produtos'] ?? [],
                ]);
                $this->setDadosGrupo($evento, $dados);
                Cache::forget('inscricao_grupo_tk_' . $tk);
            }
        }
        $atletaIds = $dados['atleta_ids'] ?? [];
        $categoriaPorAtleta = $dados['categoria_por_atleta'] ?? [];
        if (count($atletaIds) < 1 || count($categoriaPorAtleta) < count($atletaIds)) {
            return redirect()->route('inscricao-grupo.percurso', $evento)
                ->with('info', 'Selecione percurso e categoria para cada atleta antes de continuar.');
        }

        $pctTaxa = (float) ($evento->taxaservico ?? $evento->TaxaServico ?? 0);
        $numAtletas = count($atletaIds);
        $equipes = Equipe::orderBy('nome')->get()->keyBy('id');
        $valorInscricoes = 0;
        $resumoPorAtleta = [];
        foreach ($atletaIds as $atletaId) {
            $catId = (int) ($categoriaPorAtleta[$atletaId] ?? 0);
            $categoria = Categoria::with('percurso')->find($catId);
            if (!$categoria || $categoria->percurso->evento_id != $evento->id) {
                return redirect()->route('inscricao-grupo.percurso', $evento);
            }
            $loteAtivo = $categoria->lotesInscricao()
                ->where('data_inicio', '<=', now())
                ->where('data_fim', '>=', now())
                ->first();
            if (!$loteAtivo) {
                $loteAtivo = $evento->getLoteAtivoParaCategoria($categoria);
            }
            if (!$loteAtivo) {
                return redirect()->route('inscricao-grupo.percurso', $evento)->withErrors(['msg' => 'Nenhum lote disponível.']);
            }
            $valorBase = (float) $loteAtivo->valor;
            $valorInscricoes += $valorBase;
            $eqId = $dados['equipe_por_atleta'][$atletaId] ?? $dados['equipe_id'] ?? null;
            $equipeNome = ($eqId && isset($equipes[$eqId])) ? $equipes[$eqId]->nome : null;
            $resumoPorAtleta[$atletaId] = ['categoria' => $categoria, 'valor_base' => $valorBase, 'equipe_nome' => $equipeNome];
        }

        $totalProdutos = 0;
        $produtosDetalhes = [];
        foreach ($dados['produtos'] ?? [] as $p) {
            $prod = ProdutoOpcional::find($p['id'] ?? 0);
            if ($prod && $prod->ativo) {
                $qtd = (int) ($p['quantidade'] ?? 0);
                $valorItem = (float) $prod->valor;
                $totalProdutos += $qtd * $valorItem * $numAtletas;
                $produtosDetalhes[] = ['produto' => $prod, 'quantidade' => $qtd, 'valor_unit' => $valorItem];
            }
        }
        $subtotal = $valorInscricoes + $totalProdutos;
        $taxa = $subtotal * ($pctTaxa / 100);
        $totalGeral = $subtotal + $taxa;

        $cupom = null;
        $descontoCupom = 0;
        if (!empty($dados['cupom_codigo'])) {
            $cupom = Cupom::where('evento_id', $evento->id)
                ->where('codigo', $dados['cupom_codigo'])
                ->where('ativo', true)
                ->where(function ($q) {
                    $q->whereNull('data_validade')->orWhere('data_validade', '>=', now());
                })
                ->first();
            if ($cupom && ($cupom->limite_uso === null || (int) $cupom->usos < (int) $cupom->limite_uso)) {
                $descontoCupom = $cupom->tipo_desconto === 'percentual'
                    ? $totalGeral * ($cupom->valor / 100)
                    : min((float) $cupom->valor, $totalGeral);
            } else {
                $cupom = null;
            }
        }
        $totalComDesconto = max(0, $totalGeral - $descontoCupom);

        $atletas = Atleta::with('user')->whereIn('id', $atletaIds)->get()->keyBy('id');
        $atletasOrdenados = collect($atletaIds)->map(fn ($id) => $atletas->get($id))->filter();

        return view('inscricao-grupo.pagamento', compact(
            'evento', 'dados', 'pctTaxa', 'numAtletas', 'resumoPorAtleta',
            'valorInscricoes', 'totalProdutos', 'taxa', 'totalGeral', 'cupom', 'descontoCupom', 'totalComDesconto',
            'produtosDetalhes', 'atletasOrdenados', 'equipes'
        ));
    }

    /** POST: aplicar cupom (e opcionalmente salvar tipo_pagamento). */
    public function aplicarCupom(Request $request, Evento $evento): RedirectResponse
    {
        $request->validate([
            'codigo_cupom' => 'nullable|string|max:50',
            'tipo_pagamento' => 'nullable|in:unico,individual,parcial',
            'atleta_ids_pagar_agora' => 'nullable|array',
            'atleta_ids_pagar_agora.*' => 'integer',
        ]);
        $codigo = trim($request->input('codigo_cupom', ''));
        $tipo = $request->input('tipo_pagamento');
        $idsPagar = $request->input('atleta_ids_pagar_agora', []);
        $eqGeral = $request->input('equipe_id');
        $eqGeral = ($eqGeral !== null && $eqGeral !== '' && (int) $eqGeral > 0) ? (int) $eqGeral : null;
        $equipesReq = $request->input('equipes', []);
        $equipePorAtleta = [];
        foreach (is_array($equipesReq) ? $equipesReq : [] as $k => $v) {
            $eq = ($v !== null && $v !== '' && (int) $v > 0) ? (int) $v : null;
            $equipePorAtleta[(int) $k] = $eq;
        }
        $mergeData = [
            'cupom_codigo' => $codigo ?: null,
            'tipo_pagamento' => in_array($tipo, ['unico', 'individual', 'parcial'], true) ? $tipo : null,
            'atleta_ids_pagar_agora' => is_array($idsPagar) ? array_values(array_filter(array_map('intval', $idsPagar))) : [],
        ];
        if ($eqGeral !== null || count(array_filter($equipePorAtleta)) > 0) {
            $mergeData['equipe_id'] = $eqGeral;
            $mergeData['equipe_por_atleta'] = $equipePorAtleta;
        }
        $this->setDadosGrupo($evento, $mergeData);
        return redirect()->route('inscricao-grupo.pagamento', $evento);
    }

    /** POST: confirmar e criar N inscrições (cada atleta com sua categoria). */
    public function confirmar(Request $request, Evento $evento): RedirectResponse
    {
        $user = $this->getUsuario();
        if (!$user) {
            return redirect()->route('inscricao-grupo.identificacao', $evento);
        }
        $request->validate([
            'tipo_pagamento' => 'required|in:unico,individual,parcial',
            'atleta_ids_pagar_agora' => 'nullable|array',
            'atleta_ids_pagar_agora.*' => 'integer',
        ]);
        $tipoPagamento = $request->input('tipo_pagamento');
        $dados = $this->getDadosGrupo($evento);
        $atletaIds = $dados['atleta_ids'] ?? [];
        $categoriaPorAtleta = $dados['categoria_por_atleta'] ?? [];
        $idsPagarAgora = $tipoPagamento === 'parcial' ? array_values(array_filter(array_map('intval', $request->input('atleta_ids_pagar_agora', [])))) : [];
        if (count($atletaIds) < 1 || count($categoriaPorAtleta) < count($atletaIds)) {
            return redirect()->route('inscricao-grupo.atletas', $evento);
        }
        if ($tipoPagamento === 'parcial' && count($idsPagarAgora) === 0) {
            return back()->withErrors(['atleta_ids_pagar_agora' => 'Selecione pelo menos um atleta para pagar agora.']);
        }
        if ($tipoPagamento === 'parcial') {
            $idsPagarAgora = array_values(array_intersect($idsPagarAgora, $atletaIds));
            if (count($idsPagarAgora) === 0) {
                return back()->withErrors(['atleta_ids_pagar_agora' => 'Selecione pelo menos um atleta do grupo.']);
            }
        }

        $pctTaxa = (float) ($evento->taxaservico ?? $evento->TaxaServico ?? 0);
        $cupom = null;
        if (!empty($dados['cupom_codigo'])) {
            $cupom = Cupom::where('evento_id', $evento->id)
                ->where('codigo', $dados['cupom_codigo'])
                ->where('ativo', true)
                ->where(function ($q) {
                    $q->whereNull('data_validade')->orWhere('data_validade', '>=', now());
                })
                ->first();
            if ($cupom && $cupom->limite_uso !== null && (int) $cupom->usos >= (int) $cupom->limite_uso) {
                $cupom = null;
            }
        }

        $produtosInput = $dados['produtos'] ?? [];
        $totalProdutosGrupo = 0;
        $produtosSyncTemplate = [];
        foreach ($produtosInput as $p) {
            $prod = ProdutoOpcional::find($p['id'] ?? 0);
            if ($prod && $prod->ativo) {
                $qtd = (int) ($p['quantidade'] ?? 0);
                if ($qtd > 0) {
                    $totalProdutosGrupo += $qtd * (float) $prod->valor;
                    $produtosSyncTemplate[$prod->id] = [
                        'quantidade' => $qtd,
                        'valor_pago_por_item' => (float) $prod->valor,
                        'tamanho' => $p['tamanho'] ?? null,
                    ];
                }
            }
        }

        // Normalizar equipe: priorizar request (form pagamento) sobre sessão (garantir gravação)
        $equipeGeralRaw = $request->input('equipe_id') ?? $dados['equipe_id'] ?? null;
        $equipeGeral = ($equipeGeralRaw !== null && $equipeGeralRaw !== '' && (int) $equipeGeralRaw > 0) ? (int) $equipeGeralRaw : null;
        $equipesFromRequest = $request->input('equipes', []);
        $equipePorAtletaRaw = is_array($equipesFromRequest) && count($equipesFromRequest) > 0
            ? $equipesFromRequest
            : ($dados['equipe_por_atleta'] ?? []);
        $equipePorAtleta = [];
        foreach (is_array($equipePorAtletaRaw) ? $equipePorAtletaRaw : [] as $k => $v) {
            $eq = ($v !== null && $v !== '' && (int) $v > 0) ? (int) $v : null;
            $equipePorAtleta[(int) $k] = $eq;
        }

        $codigoGrupo = Str::uuid()->toString();
        $numAtletas = count($atletaIds);
        $linhas = [];
        foreach ($atletaIds as $atletaId) {
            $catId = (int) ($categoriaPorAtleta[$atletaId] ?? $categoriaPorAtleta[(string) $atletaId] ?? 0);
            $categoria = Categoria::with('percurso')->findOrFail($catId);
            if ($categoria->percurso->evento_id != $evento->id) {
                return redirect()->route('inscricao-grupo.percurso', $evento);
            }
            $loteAtivo = $categoria->lotesInscricao()
                ->where('data_inicio', '<=', now())
                ->where('data_fim', '>=', now())
                ->first();
            if (!$loteAtivo) {
                $loteAtivo = $evento->getLoteAtivoParaCategoria($categoria);
            }
            if (!$loteAtivo) {
                return redirect()->route('inscricao-grupo.percurso', $evento)->withErrors(['msg' => 'Lote indisponível.']);
            }
            $valorBase = (float) $loteAtivo->valor;
            $produtosPorAtleta = $totalProdutosGrupo;
            $taxaAtleta = ($valorBase + $produtosPorAtleta) * ($pctTaxa / 100);
            $valorPagoAtleta = $valorBase + $produtosPorAtleta + $taxaAtleta;
            $equipeId = $equipeGeral !== null ? $equipeGeral : ($equipePorAtleta[(int) $atletaId] ?? $equipePorAtleta[(string) $atletaId] ?? null);
            $linhas[] = [
                'atleta_id' => $atletaId,
                'categoria' => $categoria,
                'lote_inscricao_id' => $loteAtivo instanceof LoteInscricao ? $loteAtivo->id : null,
                'lote_inscricao_geral_id' => $loteAtivo instanceof LoteInscricaoGeral ? $loteAtivo->id : null,
                'equipe_id' => $equipeId,
                'valor_original' => $valorBase + $produtosPorAtleta,
                'taxa_plataforma' => $taxaAtleta,
                'valor_pago' => $valorPagoAtleta,
            ];
        }

        $totalBruto = array_sum(array_column($linhas, 'valor_pago'));
        $descontoTotal = 0;
        if ($cupom) {
            $descontoTotal = $cupom->tipo_desconto === 'percentual'
                ? $totalBruto * ($cupom->valor / 100)
                : min((float) $cupom->valor, $totalBruto);
        }
        $totalGeral = max(0, $totalBruto - $descontoTotal);
        $descontoPorInscricao = $numAtletas > 0 ? ($descontoTotal / $numAtletas) : 0;
        $valorPorInscricaoIndividual = $numAtletas > 0 ? ($totalGeral / $numAtletas) : 0;
        $valorPrimeiraInscricao = $tipoPagamento === 'unico' ? $totalGeral : $valorPorInscricaoIndividual;

        $codigoGrupoParcial = null;
        $totalParcial = 0;
        if ($tipoPagamento === 'parcial' && count($idsPagarAgora) > 0) {
            $codigoGrupoParcial = Str::uuid()->toString();
            foreach ($linhas as $linha) {
                if (in_array($linha['atleta_id'], $idsPagarAgora, true)) {
                    $desconto = $cupom ? $descontoPorInscricao : 0;
                    $totalParcial += max(0, $linha['valor_pago'] - $desconto);
                }
            }
        }

        try {
            $inscricoesCriadas = [];
            DB::transaction(function () use (
                $evento, $codigoGrupo, $codigoGrupoParcial, $tipoPagamento, $idsPagarAgora, $cupom,
                $linhas, $descontoPorInscricao, $valorPrimeiraInscricao, $totalParcial,
                $produtosSyncTemplate, &$inscricoesCriadas
            ) {
                $primeiroUnico = true;
                $primeiroParcial = true;
                foreach ($linhas as $linha) {
                    $desconto = $cupom ? $descontoPorInscricao : 0;
                    $valorPago = max(0, $linha['valor_pago'] - $desconto);
                    $codigoParcial = null;
                    if ($tipoPagamento === 'unico') {
                        $valorPago = $primeiroUnico ? $valorPrimeiraInscricao : 0;
                        $primeiroUnico = false;
                    } elseif ($tipoPagamento === 'parcial' && in_array($linha['atleta_id'], $idsPagarAgora, true)) {
                        $codigoParcial = $codigoGrupoParcial;
                        $valorPago = $primeiroParcial ? $totalParcial : 0;
                        $primeiroParcial = false;
                    }
                    $inscricao = Inscricao::create([
                        'atleta_id' => $linha['atleta_id'],
                        'evento_id' => $evento->id,
                        'categoria_id' => $linha['categoria']->id,
                        'lote_inscricao_id' => $linha['lote_inscricao_id'],
                        'lote_inscricao_geral_id' => $linha['lote_inscricao_geral_id'],
                        'equipe_id' => ($linha['equipe_id'] !== null && (int) $linha['equipe_id'] > 0) ? (int) $linha['equipe_id'] : null,
                        'cupom_id' => $cupom?->id,
                        'valor_original' => $linha['valor_original'],
                        'valor_desconto' => $desconto,
                        'taxa_plataforma' => $linha['taxa_plataforma'],
                        'valor_pago' => $valorPago,
                        'status' => 'aguardando_pagamento',
                        'codigo_inscricao' => 'PRTK-' . Str::upper(Str::random(8)),
                        'codigo_grupo' => $codigoGrupo,
                        'tipo_pagamento_grupo' => $tipoPagamento,
                        'codigo_grupo_parcial' => $codigoParcial,
                    ]);
                    if (!empty($produtosSyncTemplate)) {
                        $inscricao->produtosOpcionais()->attach($produtosSyncTemplate);
                    }
                    $inscricoesCriadas[] = $inscricao;
                }
            });

            $candidatas = $tipoPagamento === 'parcial' && count($idsPagarAgora) > 0
                ? collect($inscricoesCriadas)->filter(fn ($i) => in_array($i->atleta_id, $idsPagarAgora, true))->values()->all()
                : $inscricoesCriadas;
            // Redirecionar para a inscrição que tem valor a pagar (evita confirmar sem pagamento quando valor_pago=0 em outras do grupo)
            $inscricaoComValor = collect($candidatas)->first(fn ($i) => (float) $i->valor_pago > 0);
            $userId = $this->getUsuario()?->id;
            $primeiraInscricao = $inscricaoComValor ?? collect($candidatas)->first(fn ($i) => $i->atleta->user_id === $userId) ?? $candidatas[0];
            $this->limparGrupo($evento);

            // Garantir auth para a página de pagamento (rotas de pagamento exigem auth)
            if (session()->has('inscricao_user_id') && !Auth::check()) {
                Auth::loginUsingId(session('inscricao_user_id'));
            }

            foreach ($inscricoesCriadas as $insc) {
                try {
                    $insc->load(['atleta.user', 'evento', 'categoria', 'produtosOpcionais']);
                    Mail::to($insc->atleta->user->email)->send(new InscricaoRecebida($insc));
                } catch (\Throwable $e) {
                    // log
                }
            }

            if ($tipoPagamento === 'unico') {
                return redirect()->route('pagamento.show', $primeiraInscricao)
                    ->with('sucesso', 'Inscrições do grupo criadas. Efetue o pagamento único para confirmar todos.');
            }
            if ($tipoPagamento === 'parcial') {
                return redirect()->route('pagamento.show', $primeiraInscricao)
                    ->with('sucesso', 'Inscrições criadas. Efetue o pagamento dos selecionados. Os demais receberão e-mail com link para pagar (ficam pendentes).');
            }
            return redirect()->route('pagamento.show', $primeiraInscricao)
                ->with('sucesso', 'Inscrições criadas. Pague a sua e enviamos o link para os demais atletas.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Erro ao criar inscrições: ' . $e->getMessage()]);
        }
    }
}
