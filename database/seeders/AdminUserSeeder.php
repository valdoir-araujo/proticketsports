<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            // Condição para encontrar o usuário (chave única)
            ['email' => 'admin@proticketsports.com.br'],
            // Dados para criar ou atualizar
            [
                'name' => 'Valdoir Siqueira de Araujo',
                'password' => Hash::make('adm12345'), // Hash::make é mais seguro que bcrypt()
                'tipo_usuario' => 'admin',
                'documento' => '000.000.000-00',
                'status' => 'ativo', // Adicionado um status padrão
                'email_verified_at' => now()
            ]
        );
    }
}