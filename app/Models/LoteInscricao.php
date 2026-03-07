<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteInscricao extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'lotes_inscricao'; // <-- ADICIONE ESTA LINHA

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'categoria_id',
        'descricao',
        'valor',
        'data_inicio',
        'data_fim',
    ];

    /**
     * Um Lote de Inscrição pertence a uma Categoria.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
