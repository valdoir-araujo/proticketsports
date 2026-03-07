<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campeonato extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organizacao_id',
        'nome',
        'ano',
        'logo_url',
        'regulamento_url',
        'status',
    ];

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organizacao::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    public function regrasPontuacao(): HasMany
    {
        return $this->hasMany(RegraPontuacao::class);
    }

    /**
     * Obtém os pontos para uma determinada posição, seguindo a hierarquia:
     * Categoria > Percurso > Geral.
     */
    public function getPontosParaPosicao(int $posicao, ?int $percursoId = null, ?int $categoriaId = null): int
    {
        // Carrega todas as regras do campeonato uma única vez para otimizar a performance
        $regras = $this->regrasPontuacao;

        // 1. Procura pela regra mais específica: por Categoria
        if ($categoriaId) {
            $regra = $regras->where('categoria_id', $categoriaId)->where('posicao', $posicao)->first();
            if ($regra) {
                return $regra->pontos;
            }
        }

        // 2. Se não encontrar, procura pela regra de Percurso
        if ($percursoId) {
            $regra = $regras->where('percurso_id', $percursoId)->whereNull('categoria_id')->where('posicao', $posicao)->first();
            if ($regra) {
                return $regra->pontos;
            }
        }

        // 3. Se ainda não encontrar, procura pela regra Geral do campeonato
        $regra = $regras->whereNull('percurso_id')->whereNull('categoria_id')->where('posicao', $posicao)->first();

        // Retorna os pontos da regra encontrada, ou 0 se nenhuma regra corresponder
        return $regra->pontos ?? 0;
    }
}

