<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('chave_pix_tipo', 20)->nullable()->after('chave_pix');
            $table->string('qrcode_pix_url', 500)->nullable()->after('chave_pix_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['chave_pix_tipo', 'qrcode_pix_url']);
        });
    }
};
