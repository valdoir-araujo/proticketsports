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
            Schema::table('eventos', function (Blueprint $table) {
                // Remove a chave estrangeira e a coluna antiga
                $table->dropForeign(['organizador_id']);
                $table->dropColumn('organizador_id');

                // Adiciona a nova coluna e chave estrangeira
                $table->foreignId('organizacao_id')->after('id')->constrained('organizacoes');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('eventos', function (Blueprint $table) {
                // Reverte as alterações
                $table->dropForeign(['organizacao_id']);
                $table->dropColumn('organizacao_id');
                $table->foreignId('organizador_id')->constrained('users');
            });
        }
    };
    
