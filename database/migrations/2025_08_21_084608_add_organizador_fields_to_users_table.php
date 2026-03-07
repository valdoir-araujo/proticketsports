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
         Schema::table('users', function (Blueprint $table) {
        $table->string('nome_fantasia')->nullable()->after('name');
        $table->string('documento', 20)->unique()->nullable()->after('email');
        $table->string('telefone', 20)->nullable()->after('documento');
        $table->enum('status', ['ativo', 'inativo', 'pendente'])->default('ativo')->after('telefone');
    });                
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
