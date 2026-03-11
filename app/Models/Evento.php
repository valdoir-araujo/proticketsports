<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizacao_id',
        'nome',
        'modalidade_id',
        'slug',
        'campeonato_id',
        'local',
        'cidade_id',
        'estado_id',
        'data_evento',
        'data_inicio_inscricoes',
        'data_fim_inscricoes',
        'descricao_curta',
        'descricao_completa',
        'banner_url',
        'thumbnail_url',
        'status',
        'pontos_multiplicador',
        'taxaservico', // CORRIGIDO: Deve ser igual ao banco (minúsculo)
        'pagamento_manual',
        'chave_pix',
        'chave_pix_tipo',
        'qrcode_pix_url',
        'lista_inscritos_publica',
        'limite_vagas',
        'regulamento_tipo',
        'regulamento_arquivo',
        'regulamento_texto',
        'regulamento_atualizado_em',
    ];

    protected $casts = [
        'data_evento' => 'datetime',
        'data_inicio_inscricoes' => 'datetime',
        'data_fim_inscricoes' => 'datetime',
        'lista_inscritos_publica' => 'boolean',
        'pagamento_manual' => 'boolean',
        'taxaservico' => 'decimal:2', // CORRIGIDO: Igual ao banco
        'regulamento_atualizado_em' => 'datetime',
    ];

    // --- RELAÇÕES ---

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organizacao::class);
    }

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }
    
    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class);
    }

    public function percursos(): HasMany
    {
        return $this->hasMany(Percurso::class);
    }

    public function inscricoes(): HasMany
    {
        return $this->hasMany(Inscricao::class);
    }

    public function lancamentosFinanceiros(): HasMany
    {
        return $this->hasMany(LancamentoFinanceiro::class);
    }

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function produtosOpcionais(): HasMany
    {
        return $this->hasMany(ProdutoOpcional::class);
    }

    public function cupons(): HasMany
    {
        return $this->hasMany(Cupom::class);
    }
    
    public function repasses(): HasMany
    {
        return $this->hasMany(Repasse::class);
    }
    
    public function categorias(): HasManyThrough
    {
        return $this->hasManyThrough(Categoria::class, Percurso::class);
    }
    
    public function dadosBancarios(): HasOne
    {
        return $this->hasOne(DadoBancario::class);
    }

    public function lotesInscricaoGeral(): HasMany
    {
        return $this->hasMany(LoteInscricaoGeral::class);
    }

    public function eventoContatos(): HasMany
    {
        return $this->hasMany(EventoContato::class)->orderBy('ordem');
    }

    // --- MÉTODOS ---

    /**
     * Retorna a taxa cadastrada no evento ou 0.00 se não houver.
     * Evita retornar valores fixos via código.
     */
    public function getTaxaAplicadaAttribute()
    {
        // Usa a coluna correta: taxaservico
        if (!is_null($this->taxaservico)) {
            return $this->taxaservico;
        }

        // Retorna 0.00 para evitar erros, mas nunca inventa "10%"
        return 0.00; 
    }

    public function calcularValorTaxa($valorBase)
    {
        if ($valorBase <= 0) return 0;
        
        // Usa o accessor criado acima
        return $valorBase * ($this->taxa_aplicada / 100);
    }

    public function getLoteAtivoParaCategoria(Categoria $categoria)
    {
        $agora = now();

        $loteEspecifico = $categoria->lotesInscricao()
            ->where('data_inicio', '<=', $agora)
            ->where('data_fim', '>=', $agora)
            ->first();

        if ($loteEspecifico) {
            return $loteEspecifico;
        }

        $loteGlobal = $this->lotesInscricaoGeral()
            ->where('data_inicio', '<=', $agora)
            ->where('data_fim', '>=', $agora)
            ->first();
            
        return $loteGlobal;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}