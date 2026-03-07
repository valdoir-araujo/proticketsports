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
        Schema::create('organizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nome_fantasia');
            $table->string('telefone')->nullable();
            $table->string('documento')->nullable()->unique()->comment('CPF ou CNPJ da organização');
            $table->string('logo_url')->nullable();
            
            // Campos financeiros
            $table->string('pix_chave_tipo')->nullable()->comment('Tipo da chave PIX: cpf_cnpj, email, telefone, aleatoria');
            $table->string('pix_chave')->nullable();
            $table->string('banco_nome')->nullable();
            $table->string('banco_agencia')->nullable();
            $table->string('banco_conta')->nullable();
            $table->string('banco_tipo_conta')->nullable()->comment('corrente ou poupanca');

            // Campos de endereço
            $table->string('cep', 9)->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->foreignId('cidade_id')->nullable()->constrained('cidades')->onDelete('set null');
            $table->foreignId('estado_id')->nullable()->constrained('estados')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizacoes');
    }
};
