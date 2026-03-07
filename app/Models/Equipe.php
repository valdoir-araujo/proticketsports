<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'nome',
        'coordenador_id',
        'logo_url',
        'data_fundacao',
        'estado_id', // Adicionado
        'cidade_id', // Adicionado
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'data_fundacao' => 'date',
    ];

    /**
     * Relacionamento: Uma equipa pertence a um coordenador (Atleta).
     */
    public function coordenador(): BelongsTo
    {
        return $this->belongsTo(Atleta::class, 'coordenador_id');
    }

    /**
     * Relacionamento: Uma equipa pode ter vários atletas (membros).
     */
    public function atletas(): HasMany
    {
        return $this->hasMany(Atleta::class);
    }
    
    /**
     * Relacionamento: Uma equipa pertence a uma Cidade.
     */
    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }

    /**
     * Relacionamento: Uma equipa pertence a um Estado.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }
}
