<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DadoBancario extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'dados_bancarios';

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'evento_id',
        'nome_beneficiario',
        'pix_chave_tipo',
        'pix_chave',
        'banco_nome',
        'banco_agencia',
        'banco_conta',
        'banco_tipo_conta',
    ];

    /**
     * Define a relação inversa, indicando que estes dados bancários
     * pertencem a um Evento.
     */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
}

