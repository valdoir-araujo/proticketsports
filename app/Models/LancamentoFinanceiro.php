<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LancamentoFinanceiro extends Model
{
    use HasFactory;

    protected $table = 'lancamentos_financeiros';

    protected $fillable = [
        'evento_id',
        'tipo',
        'descricao',
        'valor',
        'data',
        'categoria',
        'comprovante_url',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
    ];

    /**
     * Um lançamento pertence a um evento.
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
}
