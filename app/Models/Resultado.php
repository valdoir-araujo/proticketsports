<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultado extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inscricao_id',
        'tempo_em_ms',
        'posicao_categoria',
        'pontos_etapa', // Nome do campo corrigido
        'status_corrida',
    ];

    /**
     * Define a relação: um resultado pertence a uma inscrição.
     */
    public function inscricao(): BelongsTo
    {
        return $this->belongsTo(Inscricao::class);
    }

    /**
     * Accessor para formatar os milissegundos num formato de tempo legível (HH:MM:SS.mmm).
     * Este método é chamado automaticamente quando você acede a $resultado->tempo_formatado
     */
    public function getTempoFormatadoAttribute(): ?string
    {
        if (is_null($this->tempo_em_ms)) {
            return null;
        }

        $totalSeconds = floor($this->tempo_em_ms / 1000);
        $milliseconds = str_pad($this->tempo_em_ms % 1000, 3, '0', STR_PAD_LEFT);
        $hours = str_pad(floor($totalSeconds / 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad(floor(($totalSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($totalSeconds % 60, 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$seconds}.{$milliseconds}";
    }
}

