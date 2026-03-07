<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Percurso extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'evento_id', 
        'percurso_modelo_id',
        'descricao',
        'distancia_km',
        'altimetria_metros',
        'horario_alinhamento',
        'horario_largada',
        'strava_route_url',
        'observacoes',
    ];

    /**
     * Um Percurso pertence a um Evento.
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Um Percurso possui muitas Categorias.
     */
    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }
}