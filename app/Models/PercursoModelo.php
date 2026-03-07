<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PercursoModelo extends Model
{
    use HasFactory;

    protected $table = 'percurso_modelos';

    protected $fillable = [
        'organizacao_id',
        'descricao',
        'codigo',
    ];

    /**
     * Um modelo de percurso pertence a uma organização.
     */
    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organizacao::class);
    }

    /**
     * Um modelo de percurso tem muitos modelos de categoria.
     */
    public function categoriaModelos(): HasMany
    {
        return $this->hasMany(CategoriaModelo::class);
    }

    /**
     * 🟢 RELAÇÃO ADICIONADA:
     * Um modelo de percurso pode estar vinculado a vários percursos reais de eventos.
     * Isso corrige o erro "Call to undefined method ...::percursos()"
     */
    public function percursos(): HasMany
    {
        return $this->hasMany(Percurso::class);
    }
}