<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parceiro extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'tipo',
        'descricao',
        'logo_url',
        'site_url',
        'instagram',
        'email',
        'telefone',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /** Tipos de parceiro disponíveis para cadastro. */
    public const TIPOS = [
        'medalhas_trofeus' => 'Medalhas e Troféus',
        'locucao_narracao' => 'Locução / Narração',
        'midia' => 'Mídia / Comunicação',
        'fotografia_video' => 'Fotografia e Vídeo',
        'outro' => 'Outro',
    ];

    /**
     * Escopo para listar apenas parceiros ativos.
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Retorna o label do tipo.
     */
    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * URL do Instagram (armazenado como link completo).
     */
    public function getInstagramUrlAttribute(): ?string
    {
        return $this->instagram ?: null;
    }

    /**
     * Extrai o usuário do link do Instagram para exibição (ex: instagram.com/empresa -> empresa).
     */
    public function getInstagramUsuarioAttribute(): ?string
    {
        if (empty($this->instagram)) {
            return null;
        }
        $path = parse_url($this->instagram, PHP_URL_PATH);
        if (!$path) {
            return null;
        }
        return trim(ltrim($path, '/'), '/');
    }
}
