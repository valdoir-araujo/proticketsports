<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Pais extends Model
{
    use HasFactory;

    /**
     * Indica se o modelo deve ter timestamps (created_at, updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'nome_pt',
        'sigla',
        'bacen',
    ];

    /**
     * Um país tem muitos estados.
     */
    public function estados(): HasMany
    {
        return $this->hasMany(Estado::class);
    }

    /**
     * Um país tem muitas cidades através dos seus estados.
     */
    public function cidades(): HasManyThrough
    {
        return $this->hasManyThrough(Cidade::class, Estado::class);
    }
}

