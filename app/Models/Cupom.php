<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cupom extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'cupons';

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'evento_id',
        'codigo',
        'tipo_desconto',
        'valor',
        'limite_uso', // <-- CORRIGIDO AQUI
        'usos',
        'data_validade',
        'ativo',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'data_validade' => 'datetime',
        'ativo' => 'boolean',
    ];

    /**
     * Um cupom pertence a um evento.
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
}

