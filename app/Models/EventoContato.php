<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoContato extends Model
{
    protected $fillable = [
        'evento_id',
        'nome',
        'telefone',
        'email',
        'cargo',
        'ordem',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
}
