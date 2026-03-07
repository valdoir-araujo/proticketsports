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
            Schema::create('organizacao_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organizacao_id')->constrained('organizacoes')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('role')->default('membro')->comment('Ex: admin, membro');
                $table->timestamps();

                // Garante que um usuário não possa ser adicionado duas vezes à mesma organização
                $table->unique(['organizacao_id', 'user_id']);
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('organizacao_user');
        }
    };
    
