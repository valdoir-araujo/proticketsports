<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens; // Mantido comentado caso não use API Tokens agora
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Importações dos Models relacionados (Boas práticas para evitar erros de classe não encontrada)
use App\Models\Atleta;
use App\Models\Equipe;
use App\Models\Organizacao;
use App\Models\Permission;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // Principal campo de controle de acesso ('admin', 'organizador', 'atleta')
        'documento',     // CPF ou CNPJ
        'status',        // 'ativo', 'inativo'
        'celular',       // Essencial para WhatsApp
        'tipo_usuario',  // Mantido para compatibilidade com registros antigos ou controllers legados
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- RELACIONAMENTOS ---

    /**
     * Um usuário PODE TER UM perfil de atleta.
     */
    public function atleta(): HasOne
    {
        return $this->hasOne(Atleta::class);
    }

    /**
     * Relacionamento com Permissões (Sistema de Checkbox no Admin).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Um usuário pode pertencer a muitas organizações.
     */
    public function organizacoes(): BelongsToMany
    {
        return $this->belongsToMany(Organizacao::class, 'organizacao_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Um usuário pode pertencer a muitas equipes.
     * Importante: Isso requer a tabela 'equipe_user' (user_id, equipe_id).
     */
    public function equipes(): BelongsToMany
    {
        return $this->belongsToMany(Equipe::class, 'equipe_user')
                    ->withTimestamps();
    }

    // --- MÉTODOS DE VERIFICAÇÃO (ACL - Access Control List) ---

    /**
     * Verifica se o usuário pode acessar a área de atleta.
     * IMPORTANTE: Organizadores e Admins também podem se inscrever em eventos,
     * por isso eles retornam true aqui.
     */
    public function isAtleta(): bool
    {
        return in_array($this->role, ['atleta', 'organizador', 'admin']);
    }

    /**
     * Verifica se o usuário é organizador.
     * Pode ser pela role principal OU por uma permissão específica concedida pelo Admin.
     */
    public function isOrganizador(): bool
    {
        return $this->role === 'organizador' || $this->hasPermission('access_organizer_panel');
    }

    /**
     * Verifica se o usuário é Administrador do Sistema.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica permissões granulares.
     * O Admin sempre tem todas as permissões.
     */
    public function hasPermission(string $permissionName): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        // Verifica se a relação permissions está carregada para evitar queries N+1 se possível,
        // mas aqui acessamos direto a property dinâmica.
        return $this->permissions->contains('name', $permissionName);
    }
}