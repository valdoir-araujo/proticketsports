<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizacao extends Model
{
    use HasFactory;

    protected $table = 'organizacoes';

    // Lista exata das colunas que existem na sua tabela
    protected $fillable = [
        'user_id', 
        'nome_fantasia',
        'telefone',
        'documento',       // CPF ou CNPJ
        'logo_url',        // Corrigido de 'logo' para 'logo_url'
        'cidade_id',
        'estado_id',
        'pix_chave_tipo',
        'pix_chave',
        'banco_nome',
        'banco_agencia',
        'banco_conta',
        'banco_tipo_conta',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro'
    ];

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organizacao_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    public function campeonatos(): HasMany
    {
        return $this->hasMany(Campeonato::class);
    }

    public function percursoModelos(): HasMany
    {
        return $this->hasMany(PercursoModelo::class);
    }

    public function categoriaModelos(): HasMany
    {
        return $this->hasMany(CategoriaModelo::class);
    }
    
    public function repasses(): HasMany
    {
        return $this->hasMany(Repasse::class);
    }
}