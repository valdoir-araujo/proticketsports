<?php

namespace Database\Seeders;

use App\Models\Contato;
use Illuminate\Database\Seeder;

class ContatoSeeder extends Seeder
{
    /**
     * Insere os contatos iniciais da página Fale Conosco.
     */
    public function run(): void
    {
        $contatos = [
            ['area' => 'Contato Comercial', 'nome' => 'Valdoir Araujo', 'telefone' => null, 'icone' => 'fa-solid fa-briefcase', 'cor' => 'orange', 'ordem' => 1],
            ['area' => 'Suporte Técnico', 'nome' => 'Alice Araujo', 'telefone' => null, 'icone' => 'fa-solid fa-headset', 'cor' => 'blue', 'ordem' => 2],
            ['area' => 'Financeiro', 'nome' => 'Suzi Maragni', 'telefone' => null, 'icone' => 'fa-solid fa-file-invoice-dollar', 'cor' => 'emerald', 'ordem' => 3],
            ['area' => 'Demonstração Comercial', 'nome' => 'Valdoir Araujo', 'telefone' => null, 'icone' => 'fa-solid fa-presentation-screen', 'cor' => 'violet', 'ordem' => 4],
        ];

        foreach ($contatos as $item) {
            Contato::firstOrCreate(
                ['area' => $item['area'], 'nome' => $item['nome']],
                array_merge($item, ['ativo' => true])
            );
        }
    }
}
