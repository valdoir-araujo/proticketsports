<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoriaModelo extends Model
{
    use HasFactory;

    protected $table = 'categoria_modelos';

    protected $fillable = [
        'organizacao_id',
        'percurso_modelo_id', // Ligação ao percurso
        'nome',
        'codigo',
        'genero',
        'idade_min',
        'idade_max',
    ];

    /**
     * Um modelo de categoria pertence a uma organização.
     */
    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organizacao::class);
    }

    /**
     * Um modelo de categoria pertence a um modelo de percurso.
     */
    public function percursoModelo(): BelongsTo
    {
        return $this->belongsTo(PercursoModelo::class);
    }
}

