<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegraPontuacao extends Model
{
    use HasFactory;

    /**
     * A tabela associada a este model.
     *
     * @var string
     */
    protected $table = 'regras_pontuacao';

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'campeonato_id',
        'percurso_id',    // Adicionado
        'categoria_id',   // Adicionado
        'posicao',
        'pontos',
    ];

    /**
     * Define a relação: uma regra de pontuação pertence a um campeonato.
     */
    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class);
    }

    /**
     * Define a relação opcional: uma regra de pontuação pode pertencer a um percurso.
     */
    public function percurso(): BelongsTo
    {
        return $this->belongsTo(Percurso::class);
    }

    /**
     * Define a relação opcional: uma regra de pontuação pode pertencer a uma categoria.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}

