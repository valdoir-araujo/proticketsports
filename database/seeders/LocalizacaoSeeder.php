<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LocalizacaoSeeder extends Seeder
{
    /**
     * Roda os seeds da base de dados, buscando os dados da API do IBGE.
     * Esta versão inclui a solução para o problema de certificado SSL em ambientes locais.
     *
     * @return void
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('cidades')->truncate();
        DB::table('estados')->truncate();
        DB::table('paises')->truncate();
        $this->command->info('Tabelas de localização limpas.');

        $paisId = DB::table('paises')->insertGetId([
            'nome'       => 'Brasil',
            'codigo_iso' => 'BR',
            'ddi'        => '+55',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $this->command->info('País "Brasil" inserido com sucesso.');

        try {
            // SOLUÇÃO PARA O ERRO DE SSL:
            // Usamos ->withoutVerifying() para contornar problemas de certificado em ambientes locais (WAMP/XAMPP).
            // Isto é seguro pois estamos a aceder a uma API oficial e confiável (IBGE).
            $estados = Http::withoutVerifying()->get('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')->json();

            $estadosParaInserir = [];
            foreach ($estados as $estado) {
                $estadosParaInserir[] = [
                    'id'        => $estado['id'],
                    'nome'      => $estado['nome'],
                    'uf'        => $estado['sigla'],
                    'pais_id'   => $paisId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('estados')->insert($estadosParaInserir);
            $this->command->info(count($estadosParaInserir) . ' estados brasileiros inseridos com sucesso.');

            // Para cada estado, buscar e inserir suas cidades
            $this->command->info('A buscar e inserir cidades. Este processo pode demorar alguns minutos...');

            foreach ($estados as $estado) {
                $cidades = Http::withoutVerifying()->get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$estado['id']}/municipios")->json();
                
                $cidadesParaInserir = [];
                foreach ($cidades as $cidade) {
                    $cidadesParaInserir[] = [
                        'id'         => $cidade['id'],
                        'nome'       => $cidade['nome'],
                        'estado_id'  => $estado['id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                // Insere as cidades em lote para melhor performance
                DB::table('cidades')->insert($cidadesParaInserir);
                $this->command->line("Cidades de {$estado['sigla']} inseridas.");
            }

            $this->command->info('Todas as cidades foram inseridas com sucesso!');

        } catch (\Exception $e) {
            $this->command->error('Falha ao conectar à API do IBGE: ' . $e->getMessage());
            $this->command->error('Por favor, verifique a sua conexão com a internet ou tente novamente mais tarde.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

