<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LimparDadosTeste extends Command
{
    /**
     * O nome e a assinatura do comando no terminal.
     *
     * @var string
     */
    protected $signature = 'db:limpar-testes {--tudo : Apaga também Eventos e Organizações, mantendo apenas Users/Atletas}';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Limpa tabelas de movimentação (inscrições, pagamentos, resultados) mantendo Usuários e Atletas.';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        if (!$this->confirm('Isso apagará todas as INSCRIÇÕES, PAGAMENTOS e RESULTADOS. Deseja continuar?')) {
            return;
        }

        $this->info('Iniciando limpeza...');

        // Desativa verificação de chave estrangeira para permitir o truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Tabelas de Movimentação (Sempre apaga)
        $tabelasMovimentacao = [
            'inscricao_produto',
            'resultados',
            'pagamentos',
            'inscricoes',
            'checkins', // Se tiver tabela de checkin
        ];

        foreach ($tabelasMovimentacao as $tabela) {
            if (Schema::hasTable($tabela)) {
                DB::table($tabela)->truncate();
                $this->line("- Tabela <comment>$tabela</comment> limpa.");
            }
        }

        // 2. Se passar a flag --tudo, apaga também a estrutura dos eventos
        if ($this->option('tudo')) {
            if ($this->confirm('Você escolheu apagar também EVENTOS, CATEGORIAS e ORGANIZAÇÕES. Tem certeza?')) {
                $tabelasEstrutura = [
                    'cupons',
                    'lotes_inscricao',
                    'lotes_inscricao_gerais',
                    'categorias',
                    'categoria_modelos',
                    'percursos',
                    'percurso_modelos',
                    'produtos_opcionais',
                    'regras_pontuacao',
                    'eventos',
                    'campeonatos',
                    'lancamentos_financeiros',
                    'organizacao_user',
                    'organizacoes',
                    'equipes', // Opcional, dependendo se quer manter equipes
                ];

                foreach ($tabelasEstrutura as $tabela) {
                    if (Schema::hasTable($tabela)) {
                        DB::table($tabela)->truncate();
                        $this->line("- Tabela <comment>$tabela</comment> limpa.");
                    }
                }
            }
        }

        // Reativa chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Limpeza concluída! Usuários e Atletas foram preservados.');
    }
}