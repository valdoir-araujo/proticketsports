<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Estado;
use App\Models\Equipe;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Exibe a lista de todos os usuários.
     */
    public function index(Request $request)
    {
        $query = User::where('id', '!=', auth()->id());

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        $usuarios = $query->latest()->paginate(20);
        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Dashboard de ACL.
     */
    public function aclDashboard(Request $request)
    {
        $query = User::with('permissions')->where('id', '!=', auth()->id());

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        $usuarios = $query->orderBy('name')->paginate(20);
        return view('admin.acl.dashboard', compact('usuarios'));
    }

    /**
     * Formulário de edição.
     */
    public function edit(User $usuario)
    {
        $estados = Estado::orderBy('nome')->get();
        $equipes = Equipe::orderBy('nome')->get();
        $usuario->load('atleta');

        return view('admin.usuarios.edit', compact('usuario', 'estados', 'equipes'));
    }

    /**
     * Atualização dos dados.
     */
    public function update(Request $request, User $usuario)
    {
        // 1. Validação dos dados
        $validated = $request->validate([
            // Campos da tabela USERS
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'tipo_usuario' => ['required', 'in:atleta,organizador,admin,staff'],
            'status' => ['required', 'in:ativo,inativo'],
            
            // Campos da tabela ATLETAS (Opcionais) / Dados Pessoais
            'documento' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'], // No form chama-se celular
            'data_nascimento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:masculino,feminino'],
            'tipo_sanguineo' => ['nullable', 'string', 'max:3'],
            'equipe_id' => ['nullable', 'exists:equipes,id'],
            'estado_id' => ['nullable', 'exists:estados,id'],
            'cidade_id' => ['nullable', 'exists:cidades,id'],
            'contato_emergencia_nome' => ['nullable', 'string', 'max:255'],
            'contato_emergencia_telefone' => ['nullable', 'string', 'max:20'],
        ]);

        // 2. Separação Estrita dos Dados
        
        // Dados exclusivos para a tabela 'users'
        // IMPORTANTE: Mapeamos 'celular' (form) para 'telefone' (banco)
        $dadosUsuario = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['tipo_usuario'],
            'tipo_usuario' => $validated['tipo_usuario'],
            'status' => $validated['status'],
            'telefone' => $validated['celular'], // Correção solicitada: coluna no users é telefone
        ];

        // Atualiza a tabela users
        $usuario->update($dadosUsuario);

        // 3. Atualização da tabela 'atletas'
        if (in_array($validated['tipo_usuario'], ['atleta', 'organizador', 'admin'])) {
            
            // Dados exclusivos para a tabela 'atletas'
            $dadosAtleta = [
                'cpf' => $validated['documento'],
                'telefone' => $validated['celular'], // Mantemos aqui também para garantir sincronia se existir nas duas
                'data_nascimento' => $validated['data_nascimento'],
                'sexo' => $validated['sexo'],
                'tipo_sanguineo' => $validated['tipo_sanguineo'],
                'equipe_id' => $validated['equipe_id'],
                'estado_id' => $validated['estado_id'],
                'cidade_id' => $validated['cidade_id'],
                'contato_emergencia_nome' => $validated['contato_emergencia_nome'],
                'contato_emergencia_telefone' => $validated['contato_emergencia_telefone'],
            ];

            // Atualiza ou cria o registro na tabela atletas
            $usuario->atleta()->updateOrCreate(
                ['user_id' => $usuario->id],
                $dadosAtleta
            );
        }

        return redirect()->route('admin.usuarios.index')
                         ->with('sucesso', 'Usuário atualizado com sucesso!');
    }

    /**
     * Edição de Permissões (ACL).
     */
    public function editPermissions(User $usuario)
    {
        $permissionsGrouped = Permission::all()->groupBy('group');
        $usuario->load('permissions');
        return view('admin.usuarios.permissions', compact('usuario', 'permissionsGrouped'));
    }

    /**
     * Atualização de Permissões (ACL).
     */
    public function updatePermissions(Request $request, User $usuario)
    {
        $request->validate([
            'role' => 'required|in:admin,organizador,atleta,staff',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $usuario->update(['role' => $request->role]);
        $usuario->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.acl.dashboard')
                         ->with('sucesso', "Permissões de acesso de {$usuario->name} atualizadas!");
    }

    /**
     * Exclusão de usuário.
     */
    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors(['erro' => 'Você não pode excluir sua própria conta.']);
        }

        $usuario->delete();
        return back()->with('sucesso', 'Usuário removido com sucesso.');
    }
}