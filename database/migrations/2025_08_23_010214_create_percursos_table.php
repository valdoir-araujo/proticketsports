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
        Schema::create('percursos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

        $table->string('descricao'); // Ex: "Pro", "Sport"
        $table->decimal('distancia_km', 8, 2);
        $table->unsignedInteger('altimetria_metros');
        $table->time('horario_alinhamento');
        $table->time('horario_largada');
        $table->string('strava_route_url')->nullable();
        $table->text('observacoes')->nullable();

        $table->timestamps();              
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('percursos');
    }
};
