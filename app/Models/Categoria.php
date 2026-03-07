<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    /**
     * Os atributos que podem ser preenchidos em massa.
     * Inclui os campos novos e os antigos para garantir a compatibilidade.
     */
    protected $fillable = [
        'percurso_id',
        'categoria_modelo_id', // Ligação ao modelo da biblioteca
        'nome',
        'genero',
        'idade_min',
        'idade_max',
        'idade_minima',
        'idade_maxima',
        'ano_nascimento_min',
        'ano_nascimento_max',
        'vagas_disponiveis',
    ];

    /**
     * A categoria pertence a um percurso.
     */
    public function percurso(): BelongsTo
    {
        return $this->belongsTo(Percurso::class);
    }

    /**
     * Uma categoria (instância de evento) pertence a um modelo de categoria da biblioteca.
     */
    public function categoriaModelo(): BelongsTo
    {
        return $this->belongsTo(CategoriaModelo::class);
    }

    /**
     * Uma categoria tem muitas inscrições.
     */
    public function inscricoes(): HasMany
    {
        return $this->hasMany(Inscricao::class);
    }

    /**
     * Uma categoria pode ter muitos lotes de inscrição.
     */
    public function lotesInscricao(): HasMany
    {
        return $this->hasMany(LoteInscricao::class);
    }

    /**
     * Atalho para obter o lote de inscrição que está ativo na data atual.
     *
     * @return \App\Models\LoteInscricao|null
     */
    public function loteAtivo()
    {
        return $this->lotesInscricao()
            ->where('data_inicio', '<=', now())
            ->where('data_fim', '>=', now())
            ->first();
    }
}

