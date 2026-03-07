<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('atletas', function (Blueprint $table) {
            $table->id();

            // Relação 1-para-1 com a tabela 'users'. Um atleta É um usuário.
            // O ->unique() garante que um usuário só pode ter um perfil de atleta.
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('cpf', 14)->unique()->nullable();
        //    $table->date('data_nascimento')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->char('sexo')->nullable()->comment('M ou F'); // char(1)

            // Endereço
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();

            // Informações específicas do esporte
            $table->string('equipe')->nullable();
            $table->string('tipo_sanguineo', 3)->nullable();
            $table->text('contato_emergencia')->nullable()->comment('Nome e telefone do contato');

            $table->timestamps();
        });                      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atletas');
    }
};
