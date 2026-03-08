<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atleta extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'user_id',
        'foto_url',
        'cpf',
        'telefone',
        'data_nascimento',
        'sexo',
        'cidade_id',
        'estado_id',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'equipe_id',
        'tipo_sanguineo',
        'contato_emergencia_nome',
        'contato_emergencia_telefone',
        'strava_id',
        'strava_access_token',
        'strava_refresh_token',
        'strava_token_expires_at',
        'strava_profile_photo_url',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'data_nascimento' => 'date',
    ];

    /**
     * Um atleta pertence a um utilizador.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Um atleta pertence a uma equipa (opcional).
     */
    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class);
    }

    /**
     * Um atleta tem muitas inscrições.
     */
    public function inscricoes(): HasMany
    {
        return $this->hasMany(Inscricao::class);
    }

    /**
     * Um atleta pertence a uma cidade.
     */
    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }
    
    /**
     * Um atleta pertence a um estado.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    /**
     * URL da foto a exibir no perfil: foto do Strava se estiver conectado, senão foto do cadastro.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!empty($this->strava_profile_photo_url)) {
            return $this->strava_profile_photo_url;
        }
        if (!empty($this->foto_url)) {
            return asset('storage/' . $this->foto_url);
        }
        return null;
    }
}