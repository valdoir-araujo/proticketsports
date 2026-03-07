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
        Schema::table('equipes', function (Blueprint $table) {
            // Coluna para armazenar o CAMINHO do logo da equipe.
            // Nullable, pois uma equipe pode não ter um logo.
            $table->string('logo_url')->nullable()->after('estado');

            // Coluna para a data de fundação.
            // Nullable, pois pode ser uma informação opcional.
            $table->date('data_fundacao')->nullable()->after('logo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipes', function (Blueprint $table) {
            //
        });
    }
};
