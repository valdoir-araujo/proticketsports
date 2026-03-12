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
use App\Models\Estado;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreInscricaoRequest;
use App\Http\Requests\UpdateInscricaoRequest;
use App\Mail\InscricaoRecebida;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InscricaoController extends Controller
{
    /**
     * Tela de identificação por CPF/email + data de nascimento (igual à loja).
     * Se já estiver logado com atleta, redireciona direto para o formulário de inscrição.
     */
    public function identificacao(Evento $evento): View|RedirectResponse
    {
        $user = $this->getInscricaoUser();
        if ($user && $user->atleta) {
            return redirect()->route('inscricao.create', $evento);
        }
        if ($user && !$user->atleta && !$user->isAdmin()) {
            return redirect()->route('profile.edit')->with('info', 'Complete seu perfil de atleta para se inscrever.');
        }
        $evento->load('eventoContatos');
        return view('inscricao.identificacao', compact('evento'));
    }

    /**
     * Valida CPF/email + data de nascimento. Se ok, grava session e redireciona para o formulário.
     * Se não encontrar, redireciona para cadastro com sugestão.
     */
    public function verificarIdentificacao(Request $request, Evento $evento): RedirectResponse
    {
        $request->validate([
            'identificacao' => 'required',
            'nascimento'    => 'required|date',
        ]);

        $input = $request->input('identificacao');
        $nascimentoInput = $request->input('nascimento');
        $user = null;

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $input)->first();
        } else {
            $cpfLimpo = preg_replace('/\D/', '', $input);
            $atleta = Atleta::where('cpf', $cpfLimpo)->first();
            if ($atleta) {
                $user = $atleta->user;
            } else {
                $user = User::query();
                if (\Schema::hasColumn('users', 'cpf')) {
                    $user->where('cpf', $cpfLimpo);
                } elseif (\Schema::hasColumn('users', 'documento')) {
                    $user->where('documento', $cpfLimpo);
                } else {
                    $user = null;
                }
                $user = $user instanceof \Illuminate\Database\Eloquent\Builder ? $user->first() : $user;
            }
        }

        if ($user) {
            $formatarData = fn($d) => $d ? Carbon::parse($d)->format('Y-m-d') : null;
            $dataInput = $formatarData($nascimentoInput);
            $dataUser = $formatarData($user->data_nascimento ?? null);
            if (!$dataUser) {
                $atleta = Atleta::where('user_id', $user->id)->first();
                $dataUser = $atleta ? $formatarData($atleta->data_nascimento) : null;
            }
            if ($dataUser && $dataUser === $dataInput) {
                $atleta = $user->atleta;
                if (!$atleta && !$user->hasRole('admin')) {
                    return redirect()->route('profile.edit')->with('info', 'Complete seu perfil de atleta para se inscrever.');
                }
                session(['inscricao_user_id' => $user->id]);
                session()->save();
                if (session('inscricao_intent') === 'grupo' && (int) session('inscricao_intent_evento_id') === (int) $evento->id) {
                    session()->forget(['inscricao_intent', 'inscricao_intent_evento_id']);
                    return redirect()->route('inscricao-grupo.atletas', $evento);
                }
                return redirect()->route('inscricao.create', $evento);
            }
            return back()->withInput()->withErrors(['identificacao' => 'Os dados informados não conferem.']);
        }

        return redirect()->route('register')
            ->withInput(['email' => str_contains($input, '@') ? $input : '', 'cpf' => !str_contains($input, '@') ? $input : ''])
            ->with('warning', 'Não encontramos seu cadastro. Crie sua conta para continuar e se inscrever no evento.');
    }

    /**
     * Retorna o usuário atual: logado ou identificado pela session (após tela de CPF/email).
     */
    private function getInscricaoUser(): ?User
    {
        if (Auth::check()) {
            return Auth::user();
        }
        if (session()->has('inscricao_user_id')) {
            return User::find(session('inscricao_user_id'));
        }
        return null;
    }

    public function create(Evento $evento): View|RedirectResponse
    {
        $user = $this->getInscricaoUser();
        if (!$user) {
            return redirect()->route('inscricao.identificacao', $evento);
        }
        if (!$user->atleta && !$user->isAdmin()) {
            return redirect()->route('profile.edit')->with('info', 'Complete seu perfil de atleta.');
        }
        if (!$user->atleta && $user->isAdmin()) {
            $user->setRelation('atleta', null);
        }

        $atleta = $user->atleta;
        
        // Cria objeto atleta dummy se não existir (para admins)
        if (!$atleta) {
            $atleta = new Atleta();
            $atleta->user = $user;
            $atleta->id = 0;
            $atleta->data_nascimento = now()->subYears(25);
            $atleta->sexo = 'masculino';
        }

        $inscricaoExistente = null;
        if ($atleta->id > 0) {
             $inscricaoExistente = Inscricao::where('atleta_id', $atleta->id)
                                            ->where('evento_id', $evento->id)
                                            ->first();
        }
        
        $evento->load('lotesInscricaoGeral');
        $percursosDoEvento = $evento->percursos()->with(['categorias.lotesInscricao'])->get();
        
        $anoDoEvento = Carbon::parse($evento->data_evento)->year;
        $anoNascimentoAtleta = $atleta->data_nascimento ? Carbon::parse($atleta->data_nascimento)->year : now()->year;
        $idadeNoEvento = $anoDoEvento - $anoNascimentoAtleta;
        
        $sexoAtleta = strtolower($atleta->sexo ?? 'unissex');
        $percursosFiltrados = $this->filtrarPercursosPorAtleta($percursosDoEvento, $idadeNoEvento, $sexoAtleta, $evento);

        $equipes = Equipe::orderBy('nome')->get();
        
        // --- CÁLCULO DA TAXA ---
        $taxaPercentual = $this->getTaxaServico($evento);
        // -----------------------
        
        // Lógica de visualização de produtos
        $produtosOpcionais = $evento->produtosOpcionais()->where('ativo', true)->get();
        foreach ($produtosOpcionais as $produto) {
            $temGratuidade = false;
            if ($produto->quantidade_gratuidade > 0) {
                try {
                    $qtdConsumida = DB::table('inscricao_produto')
                        ->join('inscricoes', 'inscricao_produto.inscricao_id', '=', 'inscricoes.id')
                        ->where('inscricao_produto.produto_opcional_id', $produto->id)
                        ->where('inscricao_produto.valor_pago_por_item', 0)
                        ->whereIn('inscricoes.status', ['confirmada', 'aguardando_pagamento', 'pendente'])
                        ->sum('inscricao_produto.quantidade');

                    if ($qtdConsumida < $produto->quantidade_gratuidade) {
                        $temGratuidade = true;
                    }
                } catch (\Exception $e) { $temGratuidade = false; }
            }
            if ($temGratuidade) {
                $produto->nome_original = $produto->nome; 
                $produto->valor_original_visual = $produto->valor; 
                $produto->valor = 0.00; 
                $produto->nome .= " (OFERTA: ITEM GRATUITO)";
            }
        }
        
        $estados = Estado::orderBy('nome')->get();

        return view('inscricao.create', compact(
            'evento', 'atleta', 'percursosFiltrados', 'equipes', 
            'inscricaoExistente', 'taxaPercentual', 'produtosOpcionais', 'estados'
        ));
    }

    public function pesquisarAtleta(Request $request): JsonResponse
    {
        $termo = $request->get('q');
        $user = $this->getInscricaoUser();
        if (!$user) {
            return response()->json([]);
        }
        $atletaLogadoId = $user->atleta ? $user->atleta->id : 0;

        if (strlen($termo) < 3) return response()->json([]);
        
        $termoLimpo = preg_replace('/[^0-9]/', '', $termo);

        $atletas = DB::table('atletas')
            ->join('users', 'atletas.user_id', '=', 'users.id')
            ->select('atletas.id', 'atletas.cpf', 'users.name as nome', 'atletas.data_nascimento', 'atletas.sexo')
            ->where('atletas.id', '!=', $atletaLogadoId)
            ->where(function($query) use ($termo, $termoLimpo) {
                $query->where('users.name', 'LIKE', "%{$termo}%")
                      ->orWhere('users.email', 'LIKE', "%{$termo}%")
                      ->orWhere('atletas.cpf', 'LIKE', "%{$termo}%");
                if (!empty($termoLimpo)) {
                     $query->orWhere('atletas.cpf', 'LIKE', "%{$termoLimpo}%");
                }
            })
            ->limit(10)
            ->get()
            ->map(function ($atleta) {
                $cpfLimpo = preg_replace('/[^0-9]/', '', $atleta->cpf);
                if (strlen($cpfLimpo) === 11) {
                    $atleta->cpf = substr($cpfLimpo, 0, 3) . '.***.***-' . substr($cpfLimpo, -2);
                } else {
                    $atleta->cpf = '***.***.***-**';
                }
                return $atleta;
            });

        return response()->json($atletas);
    }

    public function pesquisarEquipe(Request $request): JsonResponse
    {
        $termo = (string) $request->get('q', '');
        $user = $this->getInscricaoUser();
        if (!$user) {
            return response()->json([]);
        }
        $termo = trim($termo);
        if (mb_strlen($termo) < 2) {
            return response()->json([]);
        }

        $equipes = Equipe::query()
            ->select(['id', 'nome'])
            ->where('nome', 'like', "%{$termo}%")
            ->orderBy('nome')
            ->limit(10)
            ->get();

        return response()->json($equipes);
    }

    public function atletasDaEquipe(Request $request, Equipe $equipe): JsonResponse
    {
        $user = $this->getInscricaoUser();
        if (!$user) {
            return response()->json(['equipe' => ['id' => $equipe->id, 'nome' => $equipe->nome], 'atletas' => []]);
        }

        $eventoId = (int) $request->query('evento_id', 0);
        $jaInscritos = [];
        if ($eventoId > 0) {
            $jaInscritos = Inscricao::where('evento_id', $eventoId)
                ->whereIn('status', ['confirmada', 'aguardando_pagamento', 'pendente'])
                ->pluck('atleta_id')
                ->toArray();
        }

        $atletas = Atleta::with('user')
            ->where('equipe_id', $equipe->id)
            ->orderBy('id', 'desc')
            ->limit(250)
            ->get()
            ->map(function (Atleta $a) use ($jaInscritos) {
                $cpf = (string) ($a->cpf ?? '');
                $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
                if (strlen($cpfLimpo) === 11) {
                    $cpf = substr($cpfLimpo, 0, 3) . '.***.***-' . substr($cpfLimpo, -2);
                } else {
                    $cpf = $cpf ? '***.***.***-**' : '';
                }

                return [
                    'id' => $a->id,
                    'nome' => $a->user?->name ?? 'Atleta',
                    'cpf' => $cpf,
                    'ja_inscrito' => in_array($a->id, $jaInscritos, true),
                ];
            })
            ->values();

        return response()->json([
            'equipe' => ['id' => $equipe->id, 'nome' => $equipe->nome],
            'atletas' => $atletas,
        ]);
    }

    public function store(StoreInscricaoRequest $request): RedirectResponse
    {
        $user = $this->getInscricaoUser();
        if (!$user) {
            $evento = Evento::find($request->input('evento_id'));
            if (!$evento) {
                abort(404);
            }
            return redirect()->route('inscricao.identificacao', $evento);
        }
        $atletaPrincipal = $user->atleta;
        if (!$atletaPrincipal) {
            return redirect()->route('profile.edit')->with('info', 'Complete seu perfil de atleta.');
        }

        $produtosInput = $request->input('produtos', []);
        $produtosFiltrados = is_array($produtosInput) ? array_filter($produtosInput, fn($p) => isset($p['id'])) : [];
        $request->merge(['produtos' => $produtosFiltrados]);

        $dadosValidados = $request->validated();

        if (Inscricao::where('atleta_id', $atletaPrincipal->id)->where('evento_id', $dadosValidados['evento_id'])->exists()) {
             return back()->withErrors(['msg' => 'Você já está inscrito.']);
        }

        $inscricaoPrincipal = null;
        $inscricaoParceiro = null;

        try {
            DB::transaction(function () use ($request, $atletaPrincipal, $dadosValidados, &$inscricaoPrincipal, &$inscricaoParceiro) {
                $categoria = Categoria::with('percurso.evento')->findOrFail($dadosValidados['categoria_id']);
                $evento = $categoria->percurso->evento;
                
                if ($evento->id != $dadosValidados['evento_id']) {
                    throw new \Exception('Categoria inválida para este evento.');
                }

                // --- LÓGICA DE DUPLA ---
                $isDupla = Str::contains($categoria->nome, ['Dupla', 'Mista', 'Duples'], true);
                $parceiro = null;
                $codigoDupla = null;

                if ($isDupla) {
                    if (empty($dadosValidados['parceiro_id'])) {
                        throw new \Exception('Selecione um parceiro cadastrado.');
                    }
                    $parceiro = Atleta::find($dadosValidados['parceiro_id']);
                    
                    if (!$parceiro || $parceiro->id === $atletaPrincipal->id) { 
                        throw new \Exception('Parceiro inválido.'); 
                    }
                    if (Inscricao::where('atleta_id', $parceiro->id)->where('evento_id', $evento->id)->exists()) {
                        throw new \Exception("O atleta {$parceiro->user->name} já está inscrito neste evento.");
                    }

                    if (Str::contains($categoria->nome, 'Mista', true)) {
                        if ($parceiro->sexo === $atletaPrincipal->sexo) {
                            throw new \Exception("Dupla Mista exige atletas de sexos opostos.");
                        }
                    }
                    elseif (Str::contains($categoria->nome, 'Feminina', true) && $parceiro->sexo !== 'feminino') {
                        throw new \Exception("Esta categoria exige parceira feminina.");
                    }
                    elseif (Str::contains($categoria->nome, 'Masculina', true) && $parceiro->sexo !== 'masculino') {
                        throw new \Exception("Esta categoria exige parceiro masculino.");
                    }

                    $codigoDupla = Str::uuid()->toString();
                }

                // --- LOTE (PRIORIDADE CATEGORIA) ---
                // 1. Tenta buscar lote específico da categoria
                $loteAtivo = $categoria->lotesInscricao()
                    ->where('data_inicio', '<=', now())
                    ->where('data_fim', '>=', now())
                    ->first();

                // 2. Se não achar, busca geral
                if (!$loteAtivo) {
                    $loteAtivo = $evento->getLoteAtivoParaCategoria($categoria);
                }

                if (!$loteAtivo) throw new \Exception('Nenhum lote de inscrição disponível no momento.');
                
                $valorBase = $loteAtivo->valor;
                
                $loteEspecificoId = null;
                $loteGeralId = null;

                if ($loteAtivo instanceof \App\Models\LoteInscricao) {
                    $loteEspecificoId = $loteAtivo->id;
                } elseif ($loteAtivo instanceof \App\Models\LoteInscricaoGeral) {
                    $loteGeralId = $loteAtivo->id;
                }

                // --- CÁLCULO DA TAXA ---
                $pctTaxa = $this->getTaxaServico($evento);
                // -----------------------

                // --- PRODUTOS ---
                $totalProdutos = 0;
                $produtosSync = [];
    
                if (isset($dadosValidados['produtos'])) {
                    foreach ($dadosValidados['produtos'] as $pData) {
                        $prod = ProdutoOpcional::lockForUpdate()->find($pData['id']);
                        
                        if (!$prod || !$prod->ativo) throw new \Exception("Produto indisponível.");
                        
                        $qtd = (int)$pData['quantidade'];
                        $valorItem = $prod->valor; 

                        if ($prod->quantidade_gratuidade > 0) {
                            $qtdJaResgatada = DB::table('inscricao_produto')
                                ->join('inscricoes', 'inscricao_produto.inscricao_id', '=', 'inscricoes.id')
                                ->where('produto_opcional_id', $prod->id)
                                ->where('valor_pago_por_item', 0)
                                ->whereIn('inscricoes.status', ['confirmada', 'aguardando_pagamento', 'pendente'])
                                ->lockForUpdate() 
                                ->sum('inscricao_produto.quantidade');

                            if (($qtdJaResgatada + $qtd) <= $prod->quantidade_gratuidade) {
                                $valorItem = 0;
                            } else {
                                $valorItem = $prod->valor;
                            }
                        }

                        $totalProdutos += $qtd * $valorItem;
                        $produtosSync[$prod->id] = [
                            'quantidade' => $qtd, 
                            'valor_pago_por_item' => $valorItem, 
                            'tamanho' => $pData['tamanho'] ?? null
                        ];
                    }
                }
                
                // --- TOTAIS ---
                $tipoPagamento = $request->input('tipo_pagamento_dupla', 'individual');
                
                $valBasePrinc = $valorBase;
                $valBaseParc = $isDupla ? $valorBase : 0;

                if ($isDupla && $tipoPagamento === 'unico') {
                    $valBasePrinc += $valorBase; 
                    $valBaseParc = 0;           
                }

                $taxaPrinc = ($valBasePrinc + $totalProdutos) * ($pctTaxa / 100);
                $finalPrinc = ($valBasePrinc + $totalProdutos) + $taxaPrinc;

                $taxaParc = $valBaseParc * ($pctTaxa / 100);
                $finalParc = $valBaseParc + $taxaParc;

                // --- Corrida: ritmo previsto e pelotão (opcionais) ---
                $corridaExtra = [];
                if ($evento->isCorrida()) {
                    $corridaExtra['ritmo_previsto'] = $dadosValidados['ritmo_previsto'] ?? null;
                    $corridaExtra['pelotao_largada'] = $dadosValidados['pelotao_largada'] ?? null;
                }

                // --- SALVAR ---
                
                $inscricaoPrincipal = Inscricao::create(array_merge([
                    'atleta_id' => $atletaPrincipal->id,
                    'evento_id' => $dadosValidados['evento_id'],
                    'categoria_id' => $categoria->id,
                    'lote_inscricao_id' => $loteEspecificoId,
                    'lote_inscricao_geral_id' => $loteGeralId,
                    'equipe_id' => $dadosValidados['equipe_id'],
                    'valor_original' => $valBasePrinc,
                    'taxa_plataforma' => $taxaPrinc,
                    'valor_pago' => $finalPrinc,
                    'status' => 'aguardando_pagamento',
                    'codigo_inscricao' => 'PRTK-' . Str::upper(Str::random(8)),
                    'codigo_dupla' => $codigoDupla,
                ], $corridaExtra));
    
                if (!empty($produtosSync)) {
                    $inscricaoPrincipal->produtosOpcionais()->attach($produtosSync);
                }

                if ($isDupla && $parceiro) {
                    $inscricaoParceiro = Inscricao::create(array_merge([
                        'atleta_id' => $parceiro->id,
                        'evento_id' => $dadosValidados['evento_id'],
                        'categoria_id' => $categoria->id,
                        'lote_inscricao_id' => $loteEspecificoId,
                        'lote_inscricao_geral_id' => $loteGeralId,
                        'equipe_id' => $dadosValidados['equipe_id'],
                        'valor_original' => $valBaseParc,
                        'taxa_plataforma' => $taxaParc,
                        'valor_pago' => $finalParc,
                        'status' => 'aguardando_pagamento',
                        'codigo_inscricao' => 'PRTK-' . Str::upper(Str::random(8)),
                        'codigo_dupla' => $codigoDupla,
                    ], $corridaExtra));
                }
            });

            try {
                if ($inscricaoPrincipal) {
                    $inscricaoPrincipal->load(['atleta.user', 'evento', 'categoria', 'produtosOpcionais']);
                    Mail::to($inscricaoPrincipal->atleta->user->email)
                        ->send(new InscricaoRecebida($inscricaoPrincipal));
                }

                if ($inscricaoParceiro) {
                    $inscricaoParceiro->load(['atleta.user', 'evento', 'categoria', 'produtosOpcionais']);
                    Mail::to($inscricaoParceiro->atleta->user->email)
                        ->send(new InscricaoRecebida($inscricaoParceiro));
                }
            } catch (\Exception $mailException) {
                // Log de erro de email
            }

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'Não foi possível concluir a inscrição: ' . $e->getMessage()]);
        }

        return redirect()->route('inscricao.show', $inscricaoPrincipal)->with('sucesso', 'Inscrição realizada com sucesso!');
    }

    public function show(Inscricao $inscricao): View
    {
        if ($inscricao->atleta->user_id !== auth()->id()) { abort(403); }
        $inscricao->load('evento.eventoContatos', 'categoria', 'loteInscricao', 'produtosOpcionais', 'resultado');
        return view('inscricao.show', compact('inscricao'));
    }

    /**
     * Recibo de confirmação de pagamento (mobile-first). Inclui QR Code com codigo_inscricao para check-in.
     */
    public function recibo(Inscricao $inscricao): View
    {
        if ($inscricao->atleta->user_id !== auth()->id()) {
            abort(403);
        }
        $inscricao->load([
            'evento',
            'categoria.percurso',
            'loteInscricao',
            'equipe',
            'produtosOpcionais',
            'atleta.user',
        ]);
        $qrBase64 = null;
        try {
            $qrBase64 = base64_encode(
                QrCode::format('png')->size(280)->margin(2)->generate($inscricao->codigo_inscricao)
            );
        } catch (\Throwable $e) {
            // Se o pacote falhar, recibo ainda exibe sem QR
        }
        return view('inscricao.recibo', compact('inscricao', 'qrBase64'));
    }

    public function avatar(Inscricao $inscricao): View
    {
        if ($inscricao->atleta->user_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $inscricao->load(['evento', 'atleta.user', 'atleta.cidade.estado', 'equipe']);

        return view('atleta.inscricoes.avatar', compact('inscricao'));
    }

    public function edit(Inscricao $inscricao): View
    {
        $podeEditar = $inscricao->atleta->user_id === Auth::id();
        if (! $podeEditar) {
            $organizacao = $inscricao->evento->organizacao ?? null;
            $podeEditar = $organizacao && Auth::user()->organizacoes->contains($organizacao);
        }
        if (! $podeEditar) {
            abort(403);
        }

        $evento = $inscricao->evento;
        $evento->load('lotesInscricaoGeral');
        $atleta = $inscricao->atleta;

        $percursosDoEvento = $evento->percursos()->with(['categorias.lotesInscricao'])->get();
        $anoDoEvento = Carbon::parse($evento->data_evento)->year;
        $anoNascimentoAtleta = $atleta->data_nascimento ? Carbon::parse($atleta->data_nascimento)->year : now()->year;
        $idadeNoEvento = $anoDoEvento - $anoNascimentoAtleta;
        $sexoAtleta = strtolower($atleta->sexo ?? 'unissex');

        $percursosFiltrados = $this->filtrarPercursosPorAtleta($percursosDoEvento, $idadeNoEvento, $sexoAtleta, $evento);

        $produtosOpcionais = $evento->produtosOpcionais()->where('ativo', true)->get();
        $equipes = Equipe::orderBy('nome')->get();
        $inscricao->load('produtosOpcionais');

        return view('inscricao.edit', compact('inscricao', 'evento', 'percursosFiltrados', 'produtosOpcionais', 'equipes'));
    }

    public function update(UpdateInscricaoRequest $request, Inscricao $inscricao): RedirectResponse
    {
        $validated = $request->validated();

        $inscricao->update(['equipe_id' => $validated['equipe_id'] ?? null]);

        if (isset($validated['produtos'])) {
            $produtosSync = [];
            foreach ($validated['produtos'] as $pData) {
                if ($pData['quantidade'] > 0) {
                     $prod = ProdutoOpcional::find($pData['id']);
                     $produtosSync[$pData['id']] = [
                        'quantidade' => $pData['quantidade'],
                        'valor_pago_por_item' => $prod->valor,
                        'tamanho' => $pData['tamanho'] ?? null
                     ];
                }
            }
            $inscricao->produtosOpcionais()->sync($produtosSync);
        }

        return redirect()->route('inscricao.show', $inscricao)->with('sucesso', 'Inscrição atualizada.');
    }

    public function aplicarCupom(Request $request, Inscricao $inscricao): RedirectResponse 
    {
        if ($inscricao->atleta->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['codigo_cupom' => 'required|string|max:50']);
        $codigo = $request->input('codigo_cupom');

        // 🟢 CORRIGIDO: Usa 'data_validade' para combinar com seu banco
        $cupom = Cupom::where('codigo', $codigo)
            ->where('evento_id', $inscricao->evento_id)
            ->where('ativo', true)
            ->where(function($q) {
                $q->whereNull('data_validade')->orWhere('data_validade', '>=', now());
            })
            ->first();

        if (!$cupom) {
            return back()->withErrors(['cupom' => 'Cupom inválido ou expirado.']);
        }

        if ($cupom->limite_uso !== null && (int) $cupom->usos >= (int) $cupom->limite_uso) {
            return back()->withErrors(['cupom' => 'Este cupom atingiu o limite de usos.']);
        }

        $desconto = 0;
        
        // 🟢 CORRIGIDO: Usa 'tipo_desconto' (enum) e 'percentual'
        if ($cupom->tipo_desconto === 'percentual') {
            $desconto = $inscricao->valor_original * ($cupom->valor / 100);
        } else {
            $desconto = min((float) $cupom->valor, (float) $inscricao->valor_original);
        }

        $novoValor = max(0, $inscricao->valor_original - $desconto) + $inscricao->taxa_plataforma;
        
        $inscricao->update([
            'valor_pago' => $novoValor,
            'cupom_id' => $cupom->id,
            'valor_desconto' => $desconto 
        ]);

        return back()->with('sucesso', 'Cupom aplicado com sucesso!');
    }

    /**
     * Define a taxa de serviço de forma estrita e segura:
     * 1. Tenta acessar 'taxaservico' (minúsculo - padrão do DB).
     * 2. Tenta acessar 'TaxaServico' (PascalCase - fallback).
     * 3. Retorna 0.0 se não encontrar.
     */
    private function getTaxaServico(Evento $evento): float
    {
        if (!is_null($evento->taxaservico)) {
            return (float) $evento->taxaservico;
        }

        if (!is_null($evento->TaxaServico)) {
            return (float) $evento->TaxaServico;
        }

        return 0.0;
    }

    /**
     * Filtra percursos do evento por idade/sexo do atleta e adiciona valor_atual às categorias.
     */
    private function filtrarPercursosPorAtleta($percursosDoEvento, int $idadeNoEvento, string $sexoAtleta, Evento $evento)
    {
        return $percursosDoEvento->map(function ($percurso) use ($idadeNoEvento, $sexoAtleta, $evento) {
            $categoriasFiltradas = $percurso->categorias->filter(function ($categoria) use ($idadeNoEvento, $sexoAtleta) {
                $generoCategoria = strtolower($categoria->genero ?? 'unissex');
                $nomeCategoria = strtolower($categoria->nome ?? '');
                $isDupla = Str::contains($nomeCategoria, ['dupla', 'mista'], true);
                $generoValido = false;
                if (in_array($generoCategoria, ['unissex', 'misto'])) {
                    $generoValido = true;
                } elseif (Str::contains($nomeCategoria, 'mista')) {
                    $generoValido = true;
                } elseif ($isDupla) {
                    if (Str::contains($nomeCategoria, 'feminina') && $sexoAtleta === 'feminino') {
                        $generoValido = true;
                    } elseif (Str::contains($nomeCategoria, 'masculina') && $sexoAtleta === 'masculino') {
                        $generoValido = true;
                    } elseif ($generoCategoria === $sexoAtleta) {
                        $generoValido = true;
                    }
                } elseif ($generoCategoria === $sexoAtleta) {
                    $generoValido = true;
                }
                $idadeValida = true;
                if (! $isDupla) {
                    if ($categoria->idade_minima !== null && $idadeNoEvento < $categoria->idade_minima) {
                        $idadeValida = false;
                    }
                    if ($categoria->idade_maxima !== null && $idadeNoEvento > $categoria->idade_maxima) {
                        $idadeValida = false;
                    }
                }
                return $generoValido && $idadeValida;
            })->map(function ($categoria) use ($evento) {
                $loteAtivo = $categoria->lotesInscricao()
                    ->where('data_inicio', '<=', now())
                    ->where('data_fim', '>=', now())
                    ->first();
                if (! $loteAtivo) {
                    $loteAtivo = $evento->lotesInscricaoGeral()
                        ->where('data_inicio', '<=', now())
                        ->where('data_fim', '>=', now())
                        ->first();
                }
                $categoria->valor_atual = $loteAtivo ? $loteAtivo->valor : null;
                return $categoria;
            })->filter(function ($categoria) {
                return $categoria->valor_atual !== null;
            });
            $percurso->setRelation('categorias', $categoriasFiltradas);
            return $percurso;
        })->filter(function ($percurso) {
            return $percurso->categorias->isNotEmpty();
        });
    }
}