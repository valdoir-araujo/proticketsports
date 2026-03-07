<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->boolean('checkin_realizado')->default(false)->after('status');
            // Alterado de numero_peito para numero_atleta
            $table->string('numero_atleta')->nullable()->after('checkin_realizado');
            $table->timestamp('checkin_at')->nullable()->after('numero_atleta');
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            // Alterado de numero_peito para numero_atleta
            $table->dropColumn(['checkin_realizado', 'numero_atleta', 'checkin_at']);
        });
    }
};