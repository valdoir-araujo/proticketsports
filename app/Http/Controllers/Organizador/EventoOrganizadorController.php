<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Campeonato;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Resultado;
use App\Models\DadoBancario;
use App\Models\ProdutoOpcional;
use App\Models\Cupom;
use App\Models\Percurso;
use App\Models\LancamentoFinanceiro;
use App\Models\LoteInscricaoGeral;
use App\Models\Modalidade;
use App\Models\EventoContato;
use App\Models\PercursoModelo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreEventoRequest;
use App\Http\Requests\UpdateEventoRequest;
use Mews\Purifier\Facades\Purifier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

// Importações do Intervention Image (Versão 3)
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class EventoOrganizadorController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $organizacao = $request->user()->organizacoes()->first();
        $eventos = $organizacao ? $organizacao->eventos()->latest()->paginate(10) : collect();
        return view('organizador.eventos.index', compact('eventos'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        $orgId = $request->query('org_id');
        $organizacao = null;

        if ($orgId) {
            $organizacao = $user->organizacoes()->find($orgId);
        }

        if (!$organizacao) {
            $organizacao = $user->organizacoes()->first();
        }

        if (!$organizacao) {
            return redirect()->route('organizador.organizacao.create')
                ->with('warning', 'Você precisa de uma organização para criar eventos.');
        }

        $selectedCampeonatoId = $request->query('campeonato_id');

        $campeonatos = $organizacao->campeonatos()->orderBy('ano', 'desc')->get();
        $estados = Estado::orderBy('nome')->get();
        $modalidades = Modalidade::orderBy('nome')->get();

        return view('organizador.eventos.create', compact('organizacao', 'campeonatos', 'estados', 'modalidades', 'selectedCampeonatoId'));
    }

    public function store(StoreEventoRequest $request)
    {
        $user = Auth::user();

        if (!$request->has('organizacao_id') && $user->organizacoes()->count() > 0) {
            $request->merge(['organizacao_id' => $user->organizacoes()->first()->id]);
        }

        if (!$user->organizacoes()->where('organizacoes.id', $request->organizacao_id)->exists()) {
            return back()->withErrors(['organizacao_id' => 'Você não tem permissão nesta organização.']);
        }

        $dadosValidados = $request->validated();

        // --- LÓGICA DE COMPRESSÃO DE IMAGEM (INTERVENTION V3) ---
        $manager = new ImageManager(new Driver());

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $filename = 'eventos/banners/' . uniqid() . '.jpg'; // Força extensão JPG
            
            // Lê, Redimensiona (Max 1920px width) e Comprime (80%)
            $image = $manager->read($file);
            $image->scale(width: 1920); 
            $encoded = $image->toJpeg(80);
            
            // Salva no disco 'public'
            Storage::disk('public')->put($filename, (string) $encoded);
            $dadosValidados['banner_url'] = $filename;
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = 'eventos/thumbnails/' . uniqid() . '.jpg';
            
            // Lê, Redimensiona (Max 800px width) e Comprime (80%)
            $image = $manager->read($file);
            $image->scale(width: 800);
            $encoded = $image->toJpeg(80);
            
            Storage::disk('public')->put($filename, (string) $encoded);
            $dadosValidados['thumbnail_url'] = $filename;
        }
        // --------------------------------------------------------

        $dadosValidados['slug'] = Str::slug($dadosValidados['nome']) . '-' . Str::random(6);
        $dadosValidados['status'] = 'rascunho';
        $dadosValidados['descricao_completa'] = Purifier::clean($request->descricao_completa);
        $dadosValidados['lista_inscritos_publica'] = 0;

        $evento = Evento::create($dadosValidados);

        return redirect()->route('organizador.dashboard', ['org_id' => $request->organizacao_id])
                         ->with('sucesso', 'Evento criado com sucesso!');
    }

    public function show(Request $request, Evento $evento): View
    {
        $this->authorize('view', $evento);

        $user = Auth::user();
        $organizacao = $user->organizacoes()->where('organizacoes.id', $evento->organizacao_id)->first();

        if (!$organizacao && !$user->isAdmin()) {
            abort(403, 'Acesso negado.');
        }
        if (!$organizacao && $user->isAdmin()) {
            $organizacao = $evento->organizacao;
        }

        // Uma única query para todas as estatísticas de inscrições (evita 5 queries)
        $inscricoesStats = $evento->inscricoes()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'aguardando_pagamento' THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = 'confirmada' THEN 1 ELSE 0 END) as confirmados,
                COALESCE(SUM(CASE WHEN status = 'confirmada' THEN valor_pago ELSE 0 END), 0) as arrecadado,
                COALESCE(SUM(CASE WHEN status = 'confirmada' THEN taxa_plataforma ELSE 0 END), 0) as taxa
            ")->first();

        $totalInscritos = (int) $inscricoesStats->total;
        $totalPendentes = (int) $inscricoesStats->pendentes;
        $totalConfirmados = (int) $inscricoesStats->confirmados;
        $totalArrecadado = (float) $inscricoesStats->arrecadado;
        $taxaPlataforma = (float) $inscricoesStats->taxa;

        $totalRepassado = 0;
        if (method_exists($evento, 'repasses')) {
            $totalRepassado = $evento->repasses()->where('status', 'Realizado')->sum('valor_total_repassado');
        }
        $valorAReceber = $totalArrecadado - $taxaPlataforma - $totalRepassado;

        // Uma query para totais de lançamentos + paginate separado (evita 2 sum queries)
        $lancamentosSums = $evento->lancamentosFinanceiros()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as receitas,
                COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as despesas
            ")->first();

        $totalReceitasManuais = (float) $lancamentosSums->receitas;
        $totalDespesas = (float) $lancamentosSums->despesas;
        $valorTotalRecebido = $totalArrecadado;
        $totalReceitas = $totalReceitasManuais + $valorTotalRecebido;
        $saldoFinal = $totalReceitas - $totalDespesas;

        $lancamentosFinanceiros = $evento->lancamentosFinanceiros()->orderBy('data', 'desc')->paginate(12, ['*'], 'lancamentosPage');

        // Eager load para evitar N+1: campeonato na view; percursos com categorias
        $evento->load('campeonato');
        $inscricoesQuery = $evento->inscricoes()->with(['atleta.user', 'atleta.cidade.estado', 'categoria', 'equipe'])->orderBy('created_at', 'desc');
        $filtroCampo = $request->filled('filtro_campo') ? $request->filtro_campo : 'atleta';
        $filtroValor = $request->filled('filtro_valor') ? trim($request->filtro_valor) : '';
        if ($filtroValor !== '') {
            if ($filtroCampo === 'atleta') {
                $inscricoesQuery->whereHas('atleta.user', fn ($q) => $q->where('name', 'like', '%' . $filtroValor . '%'));
            } elseif ($filtroCampo === 'equipe') {
                $inscricoesQuery->whereHas('equipe', fn ($q) => $q->where('nome', 'like', '%' . $filtroValor . '%'));
            } elseif ($filtroCampo === 'cidade') {
                $inscricoesQuery->whereHas('atleta.cidade', fn ($q) => $q->where('nome', 'like', '%' . $filtroValor . '%'));
            } elseif ($filtroCampo === 'status') {
                $inscricoesQuery->where('status', $filtroValor);
            } elseif ($filtroCampo === 'tipo') {
                if (strtolower($filtroValor) === 'cortesia') {
                    $inscricoesQuery->where('metodo_pagamento', 'Cortesia');
                } else {
                    $inscricoesQuery->where(function ($q) use ($filtroValor) {
                        $q->whereNull('metodo_pagamento')->orWhere('metodo_pagamento', '!=', 'Cortesia');
                    });
                }
            }
        }
        $inscricoes = $inscricoesQuery->paginate(10, ['*'], 'inscritosPage');
        $percursos = $evento->percursos()->with('categorias')->orderBy('id')->get();
        $repasses = method_exists($evento, 'repasses') ? $evento->repasses()->orderBy('data_repassado', 'desc')->paginate(10, ['*'], 'repassesPage') : collect();
        $percursoModelos = $organizacao->percursoModelos()->orderBy('descricao')->get();

        $editandoContato = null;
        if ($request->filled('editar_contato')) {
            $editandoContato = $evento->eventoContatos()->find($request->editar_contato);
        }

        return view('organizador.eventos.show', compact(
            'evento', 'organizacao', 'totalInscritos', 'totalPendentes', 'totalConfirmados', 'valorTotalRecebido',
            'lancamentosFinanceiros', 'totalReceitas', 'totalDespesas', 'saldoFinal',
            'inscricoes', 'percursos',
            'totalArrecadado', 'taxaPlataforma', 'totalRepassado', 'valorAReceber',
            'repasses',
            'percursoModelos',
            'editandoContato'
        ));
    }
    
    public function edit(Request $request, Evento $evento): View
    {
        $this->authorize('update', $evento);
        $organizacao = $request->user()->organizacoes->where('id', $evento->organizacao_id)->first();
        $campeonatos = $organizacao->campeonatos()->orderBy('ano', 'desc')->get();
        $estados = Estado::orderBy('nome')->get();
        $modalidades = Modalidade::orderBy('nome')->get();

        return view('organizador.eventos.edit', compact('evento', 'campeonatos', 'estados', 'modalidades'));
    }

    public function update(UpdateEventoRequest $request, Evento $evento): RedirectResponse
    {
        $dadosValidados = $request->validated();

        if (isset($dadosValidados['descricao_completa'])) {
            $dadosValidados['descricao_completa'] = Purifier::clean($request->descricao_completa);
        }

        // --- LÓGICA DE COMPRESSÃO DE IMAGEM (UPDATE) ---
        $manager = new ImageManager(new Driver());

        if ($request->hasFile('banner')) {
            // Remove antigo
            if ($evento->banner_url) {
                Storage::disk('public')->delete($evento->banner_url);
            }

            // Processa novo
            $file = $request->file('banner');
            $filename = 'eventos/banners/' . uniqid() . '.jpg';
            
            $image = $manager->read($file);
            $image->scale(width: 1920);
            $encoded = $image->toJpeg(80);
            
            Storage::disk('public')->put($filename, (string) $encoded);
            $dadosValidados['banner_url'] = $filename;
        }

        if ($request->hasFile('thumbnail')) {
            if ($evento->thumbnail_url) {
                Storage::disk('public')->delete($evento->thumbnail_url);
            }

            $file = $request->file('thumbnail');
            $filename = 'eventos/thumbnails/' . uniqid() . '.jpg';
            
            $image = $manager->read($file);
            $image->scale(width: 800);
            $encoded = $image->toJpeg(80);
            
            Storage::disk('public')->put($filename, (string) $encoded);
            $dadosValidados['thumbnail_url'] = $filename;
        }
        // -----------------------------------------------

        $evento->update($dadosValidados);

        return redirect()->route('organizador.eventos.show', $evento)->with('sucesso', 'Evento atualizado com sucesso!');
    }

    public function destroy(Evento $evento): RedirectResponse
    {
        $this->authorize('delete', $evento);
        $evento->delete();
        return redirect()->route('organizador.eventos.index')->with('sucesso', 'Evento excluído com sucesso!');
    }

    public function updateFinanceiro(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);
        $validated = $request->validate([
            'nome_beneficiario' => 'required|string|max:255',
            'pix_chave_tipo' => 'nullable|string|in:cpf_cnpj,email,telefone,aleatoria',
            'pix_chave' => 'nullable|string|max:255',
            'banco_nome' => 'nullable|string|max:255',
            'banco_agencia' => 'nullable|string|max:20',
            'banco_conta' => 'nullable|string|max:20',
            'banco_tipo_conta' => 'nullable|string|in:corrente,poupanca',
        ]);
        $evento->dadosBancarios()->updateOrCreate(['evento_id' => $evento->id], $validated);
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'repasse'])->with('sucesso', 'Dados para repasse atualizados com sucesso!');
    }

    public function updateFormasPagamento(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);

        $pagamentoManual = $request->boolean('pagamento_manual');
        $rules = [
            'pagamento_manual' => 'required|boolean',
            'aceite_responsabilidade' => $pagamentoManual ? 'accepted' : 'nullable',
        ];
        if ($pagamentoManual) {
            $rules['chave_pix_tipo'] = 'required|string|in:cpf_cnpj,email,telefone,aleatoria';
            $rules['chave_pix'] = 'required|string|max:255';
            $rules['qrcode_pix'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120';
        }

        $messages = [
            'aceite_responsabilidade.accepted' => 'Para ativar o pagamento manual, você precisa marcar que assume a responsabilidade pelo controle de recebimento.',
        ];
        $request->validate($rules, $messages);

        $data = [
            'pagamento_manual' => $pagamentoManual,
        ];

        if ($pagamentoManual) {
            $data['chave_pix_tipo'] = $request->input('chave_pix_tipo');
            $data['chave_pix'] = $request->input('chave_pix');

            if ($request->hasFile('qrcode_pix')) {
                if ($evento->qrcode_pix_url) {
                    Storage::disk('public')->delete($evento->qrcode_pix_url);
                }
                $path = $request->file('qrcode_pix')->store('eventos/qrcode-pix', 'public');
                $data['qrcode_pix_url'] = $path;
            }
        } else {
            $data['chave_pix'] = null;
            $data['chave_pix_tipo'] = null;
            if ($evento->qrcode_pix_url) {
                Storage::disk('public')->delete($evento->qrcode_pix_url);
                $data['qrcode_pix_url'] = null;
            } else {
                $data['qrcode_pix_url'] = null;
            }
        }

        $evento->update($data);

        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'formas_pgto'])
            ->with('sucesso', 'Formas de pagamento atualizadas com sucesso!');
    }

    public function storeContato(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);
        $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
        ]);
        $ordem = $evento->eventoContatos()->max('ordem') ?? -1;
        $evento->eventoContatos()->create([
            'nome' => $request->input('nome'),
            'telefone' => $request->input('telefone') ?: null,
            'cargo' => $request->input('cargo') ?: null,
            'ordem' => $ordem + 1,
        ]);
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'contatos'])->with('sucesso', 'Contato adicionado.');
    }

    public function updateContato(Request $request, Evento $evento, EventoContato $evento_contato): RedirectResponse
    {
        $this->authorize('update', $evento);
        if ($evento_contato->evento_id !== $evento->id) {
            abort(404);
        }
        $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
        ]);
        $evento_contato->update([
            'nome' => $request->input('nome'),
            'telefone' => $request->input('telefone') ?: null,
            'cargo' => $request->input('cargo') ?: null,
        ]);
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'contatos'])->with('sucesso', 'Contato atualizado.');
    }

    public function destroyContato(Evento $evento, EventoContato $evento_contato): RedirectResponse
    {
        $this->authorize('update', $evento);
        if ($evento_contato->evento_id !== $evento->id) {
            abort(404);
        }
        $evento_contato->delete();
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'contatos'])->with('sucesso', 'Contato removido.');
    }

    public function updateRegulamento(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);

        if ($request->boolean('remover_pdf')) {
            if ($evento->regulamento_arquivo) {
                Storage::disk('public')->delete($evento->regulamento_arquivo);
            }
            $evento->update([
                'regulamento_tipo' => null,
                'regulamento_arquivo' => null,
                'regulamento_texto' => null,
                'regulamento_atualizado_em' => null,
            ]);
            return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'regulamento'])->with('sucesso', 'Regulamento removido.');
        }

        $request->validate([
            'regulamento_tipo' => 'required|in:pdf,texto',
            'regulamento_arquivo' => 'nullable|file|mimes:pdf|max:10240',
            'regulamento_texto' => 'nullable|string',
        ]);

        if ($request->input('regulamento_tipo') === 'pdf') {
            if (!$request->hasFile('regulamento_arquivo') && !$evento->regulamento_arquivo) {
                return back()->withErrors(['regulamento_arquivo' => 'Envie um arquivo PDF ou altere para "Texto no site".']);
            }
            if ($request->hasFile('regulamento_arquivo')) {
                if ($evento->regulamento_arquivo) {
                    Storage::disk('public')->delete($evento->regulamento_arquivo);
                }
                $file = $request->file('regulamento_arquivo');
                $path = $file->store('eventos/regulamentos/' . $evento->id, 'public');
                $evento->update([
                    'regulamento_tipo' => 'pdf',
                    'regulamento_arquivo' => $path,
                    'regulamento_texto' => null,
                    'regulamento_atualizado_em' => now(),
                ]);
            } else {
                $evento->update(['regulamento_atualizado_em' => now()]);
            }
            return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'regulamento'])->with('sucesso', $request->hasFile('regulamento_arquivo') ? 'Regulamento em PDF atualizado.' : 'Regulamento mantido.');
        }

        $antigoArquivo = $evento->regulamento_arquivo;
        $evento->update([
            'regulamento_tipo' => 'texto',
            'regulamento_arquivo' => null,
            'regulamento_texto' => $request->input('regulamento_texto') ? Purifier::clean($request->input('regulamento_texto')) : null,
            'regulamento_atualizado_em' => now(),
        ]);
        if ($antigoArquivo) {
            Storage::disk('public')->delete($antigoArquivo);
        }
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'regulamento'])->with('sucesso', 'Regulamento em texto atualizado.');
    }

    public function confirmarCortesia(Inscricao $inscricao): RedirectResponse
    {
        $this->authorize('update', $inscricao->evento);
        $inscricao->update([
            'status' => 'confirmada',
            'valor_pago' => 0.00,
            'data_pagamento' => now(),
            'metodo_pagamento' => 'Cortesia',
        ]);
        return back()->with('sucesso', 'Inscrição de ' . $inscricao->atleta->user->name . ' confirmada como cortesia!');
    }

    /**
     * Exibe o comprovante de pagamento da inscrição (somente organizador do evento).
     */
    public function verComprovante(Inscricao $inscricao)
    {
        $this->authorize('update', $inscricao->evento);
        if (!$inscricao->comprovante_pagamento_url) {
            abort(404, 'Comprovante não encontrado.');
        }
        $path = Storage::disk('public')->path($inscricao->comprovante_pagamento_url);
        if (!file_exists($path)) {
            abort(404, 'Arquivo do comprovante não encontrado.');
        }
        return response()->file($path);
    }

    /**
     * Alterna confirmação de pagamento: confirmada ↔ aguardando_pagamento.
     * Só permite confirmar se houver comprovante anexado.
     */
    public function toggleConfirmarPagamento(Inscricao $inscricao): RedirectResponse
    {
        $this->authorize('update', $inscricao->evento);
        $nome = $inscricao->atleta->user->name ?? 'Inscrição';
        if ($inscricao->status === 'confirmada') {
            $inscricao->update([
                'status' => 'aguardando_pagamento',
                'data_pagamento' => null,
                'metodo_pagamento' => null,
            ]);
            return back()->with('sucesso', "Pagamento de {$nome} marcado como pendente.");
        }
        $inscricao->update([
            'status' => 'confirmada',
            'data_pagamento' => now(),
            'metodo_pagamento' => $inscricao->metodo_pagamento ?: 'Pagamento manual',
        ]);
        return back()->with('sucesso', "Pagamento de {$nome} confirmado!");
    }

    public function togglePublicList(Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);
        $evento->update(['lista_inscritos_publica' => !$evento->lista_inscritos_publica]);
        return back()->with('sucesso', "A lista de inscritos agora está " . ($evento->lista_inscritos_publica ? 'pública' : 'privada') . ".");
    }

    public function exportarInscritos(Request $request, Evento $evento): StreamedResponse
    {
        $this->authorize('view', $evento);

        $query = $evento->inscricoes()->with(['atleta.user', 'categoria', 'equipe']);

        if ($request->has('status') && $request->status !== 'todos') {
             $query->where('inscricoes.status', $request->status);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('inscricoes.created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('inscricoes.created_at', '<=', $request->data_fim);
        }

        $query->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')
              ->join('users', 'atletas.user_id', '=', 'users.id')
              ->orderBy('users.name')
              ->select('inscricoes.*');

        $inscricoes = $query->get();

        return new StreamedResponse(function() use ($inscricoes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Nome', 'Email', 'CPF', 'Data Nasc.', 'Telefone', 'Categoria', 'Equipe', 'Status', 'Valor Pago', 'Tipo', 'Código', 'Número Peito'], ';');
            foreach ($inscricoes as $inscricao) {
                fputcsv($file, [
                    $inscricao->atleta->user->name,
                    $inscricao->atleta->user->email,
                    $inscricao->atleta->cpf ?? 'N/A',
                    $inscricao->atleta->data_nascimento ? \Carbon\Carbon::parse($inscricao->atleta->data_nascimento)->format('d/m/Y') : 'N/A',
                    $inscricao->atleta->telefone ?? 'N/A',
                    $inscricao->categoria->nome,
                    $inscricao->equipe->nome ?? 'Individual',
                    $inscricao->status == 'confirmada' ? 'Confirmada' : 'Pendente',
                    number_format($inscricao->valor_pago, 2, ',', '.'),
                    $inscricao->metodo_pagamento ?? 'Normal',
                    $inscricao->codigo_inscricao,
                    $inscricao->numero_atleta ?? 'S/N',
                ], ';');
            }
            fclose($file);
        }, 200, [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=inscritos-" . $evento->slug . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function gerarRelatorioFinanceiroPDF(Request $request, Evento $evento)
    {
        $this->authorize('view', $evento);
        $evento->load(['organizacao.cidade.estado']);
        $organizacao = $evento->organizacao;

        $lancamentosQuery = $evento->lancamentosFinanceiros()->orderBy('data', 'desc');
        $inscricoesQuery = $evento->inscricoes()->where('status', 'confirmada');

        if ($request->filled('data_inicio')) {
            $lancamentosQuery->whereDate('data', '>=', $request->data_inicio);
            $inscricoesQuery->whereDate('data_pagamento', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $lancamentosQuery->whereDate('data', '<=', $request->data_fim);
            $inscricoesQuery->whereDate('data_pagamento', '<=', $request->data_fim);
        }

        $lancamentosFinanceiros = $lancamentosQuery->get();
        $totalReceitasManuais = $lancamentosFinanceiros->where('tipo', 'receita')->sum('valor');
        $totalDespesas = $lancamentosFinanceiros->where('tipo', 'despesa')->sum('valor');
        $valorTotalRecebidoInscricoes = $inscricoesQuery->sum('valor_pago');
        $totalReceitas = $totalReceitasManuais + $valorTotalRecebidoInscricoes;
        $saldoFinal = $totalReceitas - $totalDespesas;
        $periodo = $request->filled('data_inicio')
            ? 'Período: ' . date('d/m/Y', strtotime($request->data_inicio)) . ' até ' . ($request->filled('data_fim') ? date('d/m/Y', strtotime($request->data_fim)) : 'Hoje')
            : 'Período: Geral';

        $pdf = Pdf::loadView('organizador.eventos.relatorio-financeiro-pdf', compact(
            'evento',
            'organizacao',
            'lancamentosFinanceiros',
            'totalReceitas',
            'totalDespesas',
            'saldoFinal',
            'periodo'
        ));
        return $pdf->download('financeiro-' . $evento->slug . '.pdf');
    }
    
    public function gerarRelatorioInscritosPDF(Request $request, Evento $evento)
    {
        $this->authorize('view', $evento);
        
        $query = $evento->inscricoes()->with(['atleta.user', 'atleta.cidade.estado', 'categoria.percurso', 'equipe']);

        // Filtro de Status
        if ($request->has('status') && $request->status !== 'todos') {
             $query->where('status', $request->status);
        } else if (!$request->has('status')) {
             $query->where('status', 'confirmada');
        }
        
        // Filtro de Data
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $inscricoes = $query->get();
        
        // Agrupamento e Ordenação Aprimorados para o PDF
        $inscricoesAgrupadas = $inscricoes->sortBy(fn($i) => $i->categoria->percurso->id ?? 0)
            ->groupBy(fn($inscricao) => $inscricao->categoria->percurso->descricao ?? 'Percurso não definido')
            ->map(function ($inscricoesPercurso) {
                return $inscricoesPercurso->sortBy(fn($i) => $i->categoria->nome)
                    ->groupBy(fn($inscricao) => $inscricao->categoria->nome ?? 'Categoria não definida')
                    ->map(function ($inscricoesCategoria) {
                        return $inscricoesCategoria->sortBy(function ($inscricao) {
                            $num = $inscricao->numero_atleta ? str_pad($inscricao->numero_atleta, 6, '0', STR_PAD_LEFT) : '999999';
                            return $num . '-' . $inscricao->atleta->user->name;
                        });
                    });
            });
        
        $titulo = ($request->status === 'todos') ? 'Lista de Inscritos (Geral)' : 'Lista de Inscritos Confirmados';

        $pdf = Pdf::loadView('organizador.eventos.relatorio-inscritos-pdf', compact('evento', 'inscricoesAgrupadas', 'titulo'));
        return $pdf->download('inscritos-' . $evento->slug . '.pdf');
    }

    public function showResultados(Request $request, Evento $evento): View
    {
        $this->authorize('update', $evento);
        $inscricoes = Inscricao::query()
            ->where('inscricoes.evento_id', $evento->id)
            ->where('inscricoes.status', 'confirmada')
            ->with(['atleta.user', 'atleta.equipe', 'categoria.percurso', 'resultado']) 
            ->join('categorias', 'inscricoes.categoria_id', '=', 'categorias.id')
            ->join('percursos', 'categorias.percurso_id', '=', 'percursos.id')
            ->orderBy('percursos.id')
            ->orderBy('categorias.nome')
            ->select('inscricoes.*')
            ->get();

        $rankingEquipesEtapa = $inscricoes
            ->whereNotNull('resultado')
            ->whereNotNull('atleta.equipe_id')
            ->groupBy('atleta.equipe_id')
            ->map(function ($inscricoesDaEquipe) {
                $equipe = $inscricoesDaEquipe->first()->atleta->equipe;
                if (!$equipe) return null; 
                return ['equipe' => $equipe, 'pontos_totais' => $inscricoesDaEquipe->sum('resultado.pontos_etapa')];
            })
            ->filter() 
            ->sortByDesc('pontos_totais')
            ->values();
            
        return view('organizador.eventos.resultados', compact('evento', 'inscricoes', 'rankingEquipesEtapa'));
    }

    public function updateSingleResultado(Request $request, Inscricao $inscricao): JsonResponse
    {
        $this->authorize('update', $inscricao->evento);
        $validated = $request->validate([
            'tempo_conclusao' => ['nullable', 'regex:/^(\d{2}):(\d{2}):(\d{2})\.(\d{3})$/'],
            'status_corrida' => 'required|in:completou,nao_iniciada,nao_completou,desqualificado',
        ]);
        
        DB::transaction(function() use ($validated, $inscricao) {
            $tempoEmMs = null;
            if (!empty($validated['tempo_conclusao'])) {
                sscanf($validated['tempo_conclusao'], "%d:%d:%d.%d", $hours, $minutes, $seconds, $milliseconds);
                $tempoEmMs = ($hours * 3600 + $minutes * 60 + $seconds) * 1000 + $milliseconds;
            }
            Resultado::updateOrCreate(['inscricao_id' => $inscricao->id], ['tempo_em_ms' => $tempoEmMs, 'status_corrida' => $validated['status_corrida']]);
            $this->apurarCategoria($inscricao->categoria);
        });
        return response()->json(['success' => 'Resultado salvo.']);
    }

    public function storeResultados(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);
        DB::transaction(function() use ($request, $evento) {
            if ($request->has('resultados')) {
                foreach ($request->input('resultados') as $inscricaoId => $dados) {
                    $inscricao = Inscricao::find($inscricaoId);
                    if (!$inscricao) continue;
                    $tempoEmMs = null;
                    if (!empty($dados['tempo_conclusao'])) {
                        sscanf($dados['tempo_conclusao'], "%d:%d:%d.%d", $hours, $minutes, $seconds, $milliseconds);
                        $tempoEmMs = ($hours * 3600 + $minutes * 60 + $seconds) * 1000 + $milliseconds;
                    }
                    Resultado::updateOrCreate(['inscricao_id' => $inscricaoId], ['tempo_em_ms' => $tempoEmMs, 'status_corrida' => $dados['status_corrida']]);
                }
            }
            $evento->load('percursos.categorias'); 
            foreach ($evento->percursos as $percurso) {
                foreach ($percurso->categorias as $categoria) {
                    $this->apurarCategoria($categoria);
                }
            }
        });
        return back()->with('sucesso', 'Resultados salvos!');
    }
    
    private function apurarCategoria(Categoria $categoria): void
    {
        $categoria->loadMissing('inscricoes'); 
        Resultado::whereIn('inscricao_id', $categoria->inscricoes->pluck('id'))->update(['posicao_categoria' => null, 'pontos_etapa' => null]);
        $inscricoesDaCategoria = Inscricao::with('resultado', 'evento.campeonato')->where('categoria_id', $categoria->id)->whereHas('resultado', function($query) { $query->where('status_corrida', 'completou')->whereNotNull('tempo_em_ms'); })->join('resultados', 'inscricoes.id', '=', 'resultados.inscricao_id')->orderBy('resultados.tempo_em_ms', 'asc')->select('inscricoes.*')->get();

        foreach ($inscricoesDaCategoria as $index => $inscricao) {
            $posicao = $index + 1;
            $pontos = 0;
            $evento = $inscricao->evento;
            if ($evento && $evento->campeonato) {
                $evento->loadMissing('campeonato');
                $pontosBase = $evento->campeonato->getPontosParaPosicao($posicao, $categoria->percurso_id, $categoria->id);
                $pontos = $pontosBase * ($evento->pontos_multiplicador ?? 1);
            }
            if ($inscricao->resultado) {
                $inscricao->resultado->update(['posicao_categoria' => $posicao, 'pontos_etapa' => $pontos]);
            }
        }
    }

    // ==========================================================
    // MÉTODOS DE CHECK-IN
    // ==========================================================
    
    public function checkinIndex(Request $request, Evento $evento)
    {
        $this->authorize('update', $evento);
        $totalInscritos = $evento->inscricoes()->count();
        $totalCheckins = $evento->inscricoes()->where('checkin_realizado', true)->count();
        $inscricoes = $evento->inscricoes()->with(['atleta.user', 'categoria.percurso', 'produtosOpcionais'])->whereIn('status', ['confirmada', 'aguardando_pagamento'])->get()
            ->map(function ($inscricao) {
                $nomeAtleta = optional(optional($inscricao->atleta)->user)->name ?? 'Atleta Desconhecido';
                $percurso = optional(optional($inscricao->categoria)->percurso)->descricao ?? null;
                return [
                    'id' => $inscricao->id,
                    'nome' => $nomeAtleta,
                    'iniciais' => strtoupper(substr($nomeAtleta, 0, 2)),
                    'cpf' => $inscricao->atleta->cpf ?? '',
                    'codigo_inscricao' => $inscricao->codigo_inscricao ?? '',
                    'categoria' => optional($inscricao->categoria)->nome ?? 'Sem Categoria',
                    'percurso' => $percurso,
                    'checkin_realizado' => (bool) $inscricao->checkin_realizado,
                    'numero_atleta' => $inscricao->numero_atleta,
                    'status' => $inscricao->status,
                    'metodo_pagamento' => $inscricao->metodo_pagamento,
                    'produtos' => $inscricao->produtosOpcionais->map(function ($prod) {
                        return [
                            'pivot_id' => $prod->pivot->id ?? null,
                            'nome' => $prod->nome,
                            'tamanho' => $prod->pivot->tamanho ?? 'U',
                            'quantidade' => (int) ($prod->pivot->quantidade ?? 1),
                            'entregue' => (bool) (isset($prod->pivot->entregue) ? $prod->pivot->entregue : true),
                        ];
                    })->values()->all(),
                    'temp_numero' => $inscricao->numero_atleta !== null && $inscricao->numero_atleta !== '' ? (string) $inscricao->numero_atleta : ''
                ];
            })->sortBy('nome')->values();
        return view('organizador.eventos.checkin', compact('evento', 'totalInscritos', 'totalCheckins', 'inscricoes'));
    }

    public function checkinStore(Request $request, Evento $evento, Inscricao $inscricao)
    {
        if ($inscricao->evento_id !== $evento->id) {
            abort(403);
        }
        $this->authorize('update', $evento);
        $request->validate([
            'numero_atleta' => 'required|string|max:20',
            'itens_entregues' => 'nullable|array',
            'itens_entregues.*' => 'boolean',
        ]);
        $inscricao->update([
            'checkin_realizado' => true,
            'numero_atleta' => $request->numero_atleta,
            'checkin_at' => now(),
        ]);
        $itensEntregues = $request->input('itens_entregues', []);
        if (!empty($itensEntregues)) {
            foreach ($itensEntregues as $pivotId => $entregue) {
                if (!is_numeric($pivotId)) {
                    continue;
                }
                \DB::table('inscricao_produto')
                    ->where('id', $pivotId)
                    ->where('inscricao_id', $inscricao->id)
                    ->update(['entregue' => (bool) $entregue]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function checkinUndo(Request $request, Evento $evento, Inscricao $inscricao)
    {
        if($inscricao->evento_id !== $evento->id) abort(403);
        $this->authorize('update', $evento);
        $inscricao->update(['checkin_realizado' => false, 'numero_atleta' => null, 'checkin_at' => null]);
        return response()->json(['success' => true]);
    }

    public function numeracao(Evento $evento): View
    {
        $this->authorize('update', $evento);
        $evento->load('percursos.categorias');
        return view('organizador.eventos.numeracao', compact('evento'));
    }

    public function salvarNumeracao(Request $request, Evento $evento): RedirectResponse
    {
        $this->authorize('update', $evento);
        $tipoNumeracao = $request->input('tipo_numeracao');
        $statusFiltro = $request->input('status_filtro', 'confirmada');
        $statusConsiderados = ($statusFiltro === 'todos') ? ['confirmada', 'aguardando_pagamento'] : ['confirmada'];

        DB::transaction(function () use ($request, $evento, $tipoNumeracao, $statusConsiderados) {
            if ($tipoNumeracao === 'global_alfabetica' || $tipoNumeracao === 'global_inscricao') {
                $inicio = (int) $request->input('numero_inicial_global', 1);
                $query = $evento->inscricoes()->whereIn('inscricoes.status', $statusConsiderados);
                if ($tipoNumeracao === 'global_alfabetica') {
                    $query->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')->join('users', 'atletas.user_id', '=', 'users.id')->orderBy('users.name')->select('inscricoes.*'); 
                } else {
                    $query->orderBy('inscricoes.created_at');
                }
                $inscricoes = $query->get();
                foreach ($inscricoes as $index => $inscricao) { $inscricao->update(['numero_atleta' => $inicio + $index]); }
            } elseif ($tipoNumeracao === 'por_categoria') {
                $faixas = $request->input('faixas', []);
                foreach ($faixas as $categoriaId => $numeroInicial) {
                    $numeroAtual = (int) $numeroInicial;
                    $inscricoesCategoria = Inscricao::where('evento_id', $evento->id)->where('categoria_id', $categoriaId)->whereIn('inscricoes.status', $statusConsiderados)->join('atletas', 'inscricoes.atleta_id', '=', 'atletas.id')->join('users', 'atletas.user_id', '=', 'users.id')->orderBy('users.name')->select('inscricoes.*')->get();
                    foreach ($inscricoesCategoria as $inscricao) { $inscricao->update(['numero_atleta' => $numeroAtual]); $numeroAtual++; }
                }
            }
        });
        return redirect()->route('organizador.eventos.show', ['evento' => $evento, 'tab' => 'numeracao'])->with('sucesso', "Numeração gerada com sucesso!");
    }
}