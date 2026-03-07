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
        Schema::create('repasses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizador_id')->constrained('organizacoes')->onDelete('cascade');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->decimal('valor_total_repassado', 10, 2);
            $table->timestamp('data_repassado');
            $table->string('status')->default('pago'); // Começa como 'pago' ao ser criado
            $table->string('comprovante_url')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repasses');
    }
};
