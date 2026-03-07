<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repasse extends Model
{
    use HasFactory;

    protected $fillable = [
        'evento_id',
        'organizador_id',
        'valor_total_repassado',
        'data_repassado',
        'comprovante_url',
        'observacoes',
        'status',
        'user_id_admin',
    ];

    protected $casts = [
        'data_repassado' => 'date',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Define a relação com a Organização, especificando a chave estrangeira correta.
     */
    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class, 'organizador_id');
    }

    /**
     * Define a relação com as Inscrições incluídas neste repasse.
     */
    public function inscricoes()
    {
        return $this->hasMany(Inscricao::class);
    }
}

