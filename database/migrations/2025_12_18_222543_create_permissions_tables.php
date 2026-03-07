<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Permissões (As rotinas do site)
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ex: gerenciar_financeiro
            $table->string('label'); // Ex: Acesso ao Financeiro
            $table->string('group'); // Ex: Financeiro, Cadastros, Sistema
            $table->timestamps();
        });

        // 2. Tabela Pivot (Vincula User -> Permission)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Popular com as permissões iniciais
        $permissions = [
            // Grupo Administrativo
            ['name' => 'access_admin_panel', 'label' => 'Acessar Painel Admin', 'group' => 'Sistema'],
            ['name' => 'manage_users', 'label' => 'Gerenciar Utilizadores e Permissões', 'group' => 'Sistema'],
            
            // Grupo Cadastros
            ['name' => 'manage_banners', 'label' => 'Gerenciar Banners', 'group' => 'Cadastros'],
            ['name' => 'manage_modalidades', 'label' => 'Gerenciar Modalidades', 'group' => 'Cadastros'],
            ['name' => 'manage_eventos', 'label' => 'Supervisionar Eventos', 'group' => 'Cadastros'],
            
            // Grupo Financeiro
            ['name' => 'view_reports', 'label' => 'Ver Relatórios Financeiros', 'group' => 'Financeiro'],
            ['name' => 'manage_repasses', 'label' => 'Gerenciar Repasses', 'group' => 'Financeiro'],
            ['name' => 'manage_settings', 'label' => 'Configurações do Sistema', 'group' => 'Sistema'],
        ];

        DB::table('permissions')->insert($permissions);
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permissions');
    }
};