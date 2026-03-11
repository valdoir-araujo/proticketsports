<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inscricao extends Model
{
    use HasFactory;

    protected $table = 'inscricoes';

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'atleta_id',
        'evento_id',
        'categoria_id',
        'lote_inscricao_id',
        'equipe_id',
        'cupom_id',
        'repasse_id',
        'valor_original',
        'valor_desconto',
        'taxa_plataforma',
        'valor_pago',
        'status',
        'data_pagamento',
        'metodo_pagamento',
        'transacao_id_gateway',
        'comprovante_pagamento_url',
        'codigo_inscricao',
        'dados_pagamento',
        
        // 🟢 CAMPOS DE CHECK-IN
        'checkin_realizado',
        'numero_atleta', 
        'checkin_at',

        // 🟢 NOVOS CAMPOS PARA DUPLAS (Adicionados para corrigir o erro)
        'nome_dupla',
        'parceiro_nome',
        'parceiro_cpf',
        'parceiro_data_nascimento',
        'parceiro_sexo',

        // Inscrições em grupo
        'codigo_grupo',
        'tipo_pagamento_grupo',
        'codigo_grupo_parcial',
        'lote_inscricao_geral_id',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'data_pagamento' => 'datetime',
        'checkin_at' => 'datetime',
        'checkin_realizado' => 'boolean',
        'dados_pagamento' => 'array',
        'valor_original' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'taxa_plataforma' => 'decimal:2',
        // Cast para a data do parceiro
        'parceiro_data_nascimento' => 'date',
    ];

    // --- RELACIONAMENTOS ---

    /**
     * A inscrição pertence a um Atleta.
     */
    public function atleta(): BelongsTo
    {
        return $this->belongsTo(Atleta::class);
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function loteInscricao(): BelongsTo
    {
        return $this->belongsTo(LoteInscricao::class);
    }
    
    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class);
    }
    
    public function cupom(): BelongsTo
    {
        return $this->belongsTo(Cupom::class);
    }
    
    public function repasse(): BelongsTo
    {
        return $this->belongsTo(Repasse::class);
    }

    public function resultado(): HasOne
    {
        return $this->hasOne(Resultado::class);
    }

    public function produtosOpcionais(): BelongsToMany
    {
        // Certifique-se que o nome da tabela aqui ('inscricao_produto') 
        // é o mesmo que está no seu banco de dados.
        return $this->belongsToMany(ProdutoOpcional::class, 'inscricao_produto')
            ->withPivot('quantidade', 'valor_pago_por_item', 'tamanho')
            ->withTimestamps();
    }
}