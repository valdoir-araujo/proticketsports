<?php

namespace Database\Seeders;

use App\Models\Modalidade;
use Illuminate\Database\Seeder;

class ModalidadeSeeder extends Seeder
{
    public function run(): void
    {
        $modalidades = [
            'Corrida de Rua',
            'Ciclismo',
            'Triathlon',
            'Natação',
            'Caminhada',
            'Outros',
        ];

        foreach ($modalidades as $nome) {
            Modalidade::firstOrCreate(['nome' => $nome]);
        }
    }
}
