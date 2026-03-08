<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contato extends Model
{
    use HasFactory;

    protected $fillable = [
        'area',
        'nome',
        'foto_url',
        'telefone',
        'email',
        'icone',
        'cor',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /** Cores disponíveis para o card na página pública. */
    public const CORES = [
        'orange' => 'Laranja',
        'blue' => 'Azul',
        'emerald' => 'Verde',
        'violet' => 'Violeta',
    ];

    /**
     * Escopo para listar apenas contatos ativos (exibidos na página pública).
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
