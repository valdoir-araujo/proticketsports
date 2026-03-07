<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Categoria;
use App\Models\LoteInscricao;

// Rota para buscar o preço de uma categoria
Route::get('/categoria/{id}/preco', function ($id) {
    // Passo 1: Tenta encontrar a categoria
    $categoria = Categoria::find($id);
    if (!$categoria) {
        return response()->json(['error' => 'Categoria com ID ' . $id . ' não foi encontrada.'], 404);
    }

    $now = now();
    
    // Passo 2: Tenta encontrar o lote ATIVO, considerando as datas
    $loteAtivo = LoteInscricao::where('categoria_id', $categoria->id)
        ->whereDate('data_inicio', '<=', $now)
        ->whereDate('data_fim', '>=', $now)
        ->first();

    // Se encontrar um lote ativo, retorna o valor (sucesso!)
    if ($loteAtivo) {
        return response()->json(['valor' => $loteAtivo->valor]);
    }

    // --- INÍCIO DA DEPURAÇÃO AVANÇADA ---
    // Se não encontrou um lote ativo, vamos descobrir porquê.
    // Primeiro, procuramos por QUALQUER lote para esta categoria, ignorando as datas.
    $loteExistente = LoteInscricao::where('categoria_id', $categoria->id)->first();

    if ($loteExistente) {
        // Se encontrámos um lote mas ele não está ativo, mostramos o motivo.
        return response()->json([
            'error' => 'Encontrámos um lote, mas as suas datas de vigência não incluem a data atual.',
            'dados_depuracao' => [
                'data_hora_atual_servidor' => $now->toDateTimeString(),
                'lote_encontrado' => $loteExistente->toArray()
            ]
        ], 404);
    } else {
        // Se não encontrámos NENHUM lote para esta categoria.
        return response()->json(['error' => 'Nenhum lote de preço (mesmo inativo) foi encontrado para esta categoria.'], 404);
    }
    // --- FIM DA DEPURAÇÃO AVANÇADA ---
});
