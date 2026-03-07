<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LancamentoFinanceiroController extends Controller
{
    /**
     * Salva um novo lançamento financeiro para um evento.
     */
    public function store(Request $request, Evento $evento): RedirectResponse
    {
        // ==========================================================
        // LÓGICA DE SEGURANÇA CORRIGIDA AQUI
        // Verifica se o usuário logado é membro da organização dona do evento.
        // ==========================================================
        if (!Auth::user()->organizacoes->contains($evento->organizacao)) {
            abort(403, 'Acesso Não Autorizado');
        }

        // 2. Validação: Garante que todos os dados estão corretos.
        $dadosValidados = $request->validate([
            'tipo' => 'required|in:receita,despesa',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria' => 'required|string|max:100',
            'comprovante' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048', // 2MB Max
            'observacoes' => 'nullable|string',
        ]);

        // 3. Upload do Comprovativo (se existir)
        if ($request->hasFile('comprovante')) {
            $path = $request->file('comprovante')->store("eventos/{$evento->id}/financeiro", 'public');
            $dadosValidados['comprovante_url'] = $path;
        }

        // 4. Salvar no Banco de Dados usando o relacionamento
        $evento->lancamentosFinanceiros()->create($dadosValidados);

        // 5. Redirecionar de Volta: Adiciona o #financeiro ao final da URL.
        return redirect(route('organizador.eventos.show', $evento) . '#financeiro')
                         ->with('sucesso', 'Lançamento financeiro adicionado com sucesso!');
    }
}
