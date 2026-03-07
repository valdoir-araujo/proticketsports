<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscricao;
use App\Models\Repasse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RelatorioFinanceiroController extends Controller
{
    /**
     * Mostra a página principal de gestão de repasses.
     */
    public function index(): View
    {
        // Pega os lotes de repasse para exibir nas tabelas
        $repassesPendentes = Repasse::where('status', 'Pendente')->with('organizacao')->latest()->get();
        $repassesRealizados = Repasse::whereIn('status', ['Realizado', 'Cancelado'])->with('organizacao')->latest()->paginate(10);

        // Calcula os totais da plataforma inteira para os cards de resumo
        $inscricoesConfirmadas = Inscricao::where('status', 'confirmada')->get();
        $faturamentoBrutoTotal = $inscricoesConfirmadas->sum('valor_pago');
        $receitaPlataformaTotal = $inscricoesConfirmadas->sum('taxa_plataforma');
        $totalRepassado = Repasse::where('status', 'Realizado')->sum('valor_total_repassado');

        // Dados para os cards de desempenho recente (últimos 30 dias)
        $inscricoes30dias = $inscricoesConfirmadas->where('created_at', '>=', now()->subDays(30));
        $faturamentoUltimos30Dias = $inscricoes30dias->sum('valor_pago');
        $receitaUltimos30Dias = $inscricoes30dias->sum('taxa_plataforma');
        $totalTransacoes = $inscricoes30dias->count();
        $ticketMedio = $totalTransacoes > 0 ? $faturamentoUltimos30Dias / $totalTransacoes : 0;

        return view('admin.relatorios.financeiros.index', compact(
            'repassesPendentes',
            'repassesRealizados',
            'faturamentoBrutoTotal',
            'receitaPlataformaTotal',
            'totalRepassado',
            'faturamentoUltimos30Dias',
            'receitaUltimos30Dias',
            'totalTransacoes',
            'ticketMedio'
        ));
    }

    /**
     * Mostra a página para criar um novo lote de repasse.
     */
    public function createRepasse(): View
    {
        $inscricoes = Inscricao::where('status', 'confirmada')
            ->whereNull('repasse_id')
            ->with(['evento.organizacao', 'atleta.user'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.repasses.create', compact('inscricoes'));
    }

    /**
     * Armazena um novo lote de repasse e associa as inscrições.
     */
    public function storeRepasseLote(Request $request): RedirectResponse
    {
        $dadosValidados = $request->validate([
            'inscricao_ids' => 'required|array|min:1',
            'inscricao_ids.*' => 'exists:inscricoes,id',
        ]);
        
        $inscricoes = Inscricao::with('evento')->find($dadosValidados['inscricao_ids']);
        
        if ($inscricoes->pluck('evento.organizacao_id')->unique()->count() > 1) {
            return back()->with('error', 'Só é possível criar um lote de repasse para um único organizador de cada vez.');
        }

        $evento = $inscricoes->first()->evento;
        $valorTotal = $inscricoes->sum(fn($i) => $i->valor_pago - $i->taxa_plataforma);

        DB::transaction(function () use ($inscricoes, $valorTotal, $evento) {
            $repasse = Repasse::create([
                'evento_id' => $evento->id,
                'organizador_id' => $evento->organizacao_id,
                'valor_total_repassado' => $valorTotal,
                'data_repassado' => now(), // Data temporária
                'status' => 'Pendente',
                'user_id_admin' => auth()->id(),
            ]);
            Inscricao::whereIn('id', $inscricoes->pluck('id'))->update(['repasse_id' => $repasse->id]);
        });
        
        return redirect()->route('admin.relatorios.financeiros.index')->with('sucesso', 'Lote de repasse criado com sucesso!');
    }

    /**
     * Mostra a página de detalhes para confirmar um lote de repasse.
     */
    public function showRepasseLote(Repasse $repasse): View
    {
        $repasse->load('organizacao', 'inscricoes.atleta.user', 'inscricoes.evento');
        return view('admin.repasses.show', compact('repasse'));
    }

    /**
     * Atualiza um lote de repasse para "Realizado" e anexa o comprovativo.
     */
    public function updateRepasseLote(Request $request, Repasse $repasse): RedirectResponse
    {
        $dadosValidados = $request->validate([
            'data_repassado' => 'required|date',
            'comprovante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'observacoes' => 'nullable|string',
        ]);

        $caminhoComprovante = $request->file('comprovante')->store('comprovantes_repasses', 'public');

        $repasse->update([
            'data_repassado' => $dadosValidados['data_repassado'],
            'comprovante_url' => $caminhoComprovante,
            'observacoes' => $dadosValidados['observacoes'],
            'status' => 'Realizado',
        ]);

        return redirect()->route('admin.relatorios.financeiros.index')->with('sucesso', 'Repasse #' . $repasse->id . ' confirmado com sucesso!');
    }

    /**
     * Cancela um lote de repasse, libertando as inscrições associadas.
     */
    public function destroyRepasseLote(Repasse $repasse): RedirectResponse
    {
        if ($repasse->status !== 'Pendente') {
            return back()->with('error', 'Apenas lotes com o status "Pendente" podem ser cancelados.');
        }

        DB::transaction(function () use ($repasse) {
            Inscricao::where('repasse_id', $repasse->id)->update(['repasse_id' => null]);
            $repasse->update(['status' => 'Cancelado']);
        });

        return redirect()->route('admin.relatorios.financeiros.index')->with('sucesso', 'Lote de repasse #' . $repasse->id . ' foi cancelado com sucesso.');
    }
}

