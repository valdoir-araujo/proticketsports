<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Equipe;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Importado para usar transações

class EquipeController extends Controller
{
    /**
     * Exibe uma lista de TODAS as equipes cadastradas no sistema.
     * * CORREÇÃO PRINCIPAL: Agora lista todas as equipes usando Equipe::...,
     * em vez de tentar buscar as equipes do usuário logado.
     */
    public function index(): View
    {
        $equipes = Equipe::with(['coordenador.user', 'cidade', 'estado'])
                        ->latest()
                        ->paginate(15);

        return view('equipes.index', compact('equipes'));
    }

    /**
     * Mostra o formulário para criar uma nova equipe.
     */
    public function create(): View
    {
        // Ordenar atletas pelo nome do usuário para facilitar a busca no select
        $atletas = Atleta::with('user')
            ->get()
            ->sortBy(function ($atleta) {
                return $atleta->user->name;
            });

        $estados = Estado::orderBy('nome')->get();
        return view('equipes.create', compact('atletas', 'estados'));
    }

    /**
     * Salva a nova equipe no banco de dados (submissão de formulário padrão).
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Inicia uma transação para garantir integridade
            DB::beginTransaction();

            $validatedData = $this->validateTeamRequest($request);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('equipes/logos', 'public');
                $validatedData['logo_url'] = $logoPath;
            }

            // Mantém o registro de quem criou, se a coluna user_id existir na tabela equipes.
            // Se não existir e você não quiser esse registro, pode remover esta linha.
            $validatedData['user_id'] = Auth::id();

            Equipe::create($validatedData);

            // Confirma a transação
            DB::commit();

            return redirect()->route('equipes.index')->with('sucesso', 'Equipe cadastrada com sucesso!');

        } catch (\Exception $e) {
            // Em caso de erro, desfaz a transação
            DB::rollBack();

            // Se uma imagem foi salva, apaga para não deixar lixo
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            
            // Redireciona com erro. Em produção, evite $e->getMessage() e use uma mensagem genérica.
            return redirect()->back()->withInput()->withErrors(['erro' => 'Erro ao criar equipe: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Armazena uma nova equipe via AJAX a partir do modal.
     */
    public function storeAjax(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $this->validateTeamRequest($request);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('equipes/logos', 'public');
                $validated['logo_url'] = $logoPath;
            }
            
            // Mantém o registro de quem criou.
            $validated['user_id'] = Auth::id();

            $equipe = Equipe::create($validated);
            
            DB::commit();

            // Carrega os relacionamentos para o retorno do JSON
            $equipe->load(['coordenador.user', 'cidade', 'estado']);

            return response()->json($equipe, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validação falhou, não precisa de rollback pois nada foi salvo
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            // Retorna uma mensagem de erro genérica em produção para segurança
            return response()->json([
                'message' => 'Ocorreu um erro interno ao criar a equipe.',
                // 'error' => $e->getMessage() // Use apenas para debug local
            ], 500);
        }
    }


    /**
     * Mostra o formulário para editar uma equipe.
     */
    public function edit(Equipe $equipe): View
    {
        $this->authorize('update', $equipe);

        // Ordenar atletas para o select de edição também
        $atletas = Atleta::with('user')
            ->get()
            ->sortBy(function ($atleta) {
                return $atleta->user->name;
            });

        $estados = Estado::orderBy('nome')->get();
        return view('equipes.edit', compact('equipe', 'atletas', 'estados'));
    }

    /**
     * Atualiza uma equipe no banco de dados.
     */
    public function update(Request $request, Equipe $equipe): RedirectResponse
    {
        $this->authorize('update', $equipe);

        try {
            DB::beginTransaction();

            $validatedData = $this->validateTeamRequest($request, $equipe->id);
            $oldLogoPath = $equipe->logo_url;
            $newLogoPath = null;

            if ($request->hasFile('logo')) {
                $newLogoPath = $request->file('logo')->store('equipes/logos', 'public');
                $validatedData['logo_url'] = $newLogoPath;
            }
            
            $equipe->update($validatedData);

            // Se tudo deu certo e houve troca de logo, deleta o antigo
            if ($newLogoPath && $oldLogoPath) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            DB::commit();
            return redirect()->route('equipes.index')->with('sucesso', 'Equipe atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Se houve erro e um novo logo foi salvo, deleta o novo
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }
            return redirect()->back()->withInput()->withErrors(['erro' => 'Erro ao atualizar a equipe.']);
        }
    }

    /**
     * Remove uma equipe do banco de dados.
     */
    public function destroy(Equipe $equipe): RedirectResponse
    {
        $this->authorize('delete', $equipe);

        try {
            DB::beginTransaction();
            
            $logoPath = $equipe->logo_url;
            
            $equipe->delete();

            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            DB::commit();
            return redirect()->route('equipes.index')->with('sucesso', 'Equipe excluída com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['erro' => 'Erro ao excluir a equipe.']);
        }
    }

    /**
     * Valida os dados para criar ou atualizar uma equipe.
     */
    private function validateTeamRequest(Request $request, $equipeId = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('equipes', 'nome')->ignore($equipeId)],
            'coordenador_id' => ['required', 'integer', 'exists:atletas,id'],
            'data_fundacao' => ['nullable', 'date', 'before_or_equal:today'],
            // Aumentei o tamanho máximo para 2MB e adicionei webp
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'], 
            'estado_id' => ['required', 'integer', 'exists:estados,id'],
            // Valida se a cidade existe e se pertence ao estado selecionado
            'cidade_id' => [
                'required', 
                'integer', 
                'exists:cidades,id',
                Rule::exists('cidades', 'id')->where(function ($query) use ($request) {
                    return $query->where('estado_id', $request->estado_id);
                }),
            ],
        ], [
            // Mensagens personalizadas
            'cidade_id.exists' => 'A cidade selecionada não é válida ou não pertence ao estado informado.',
            'data_fundacao.before_or_equal' => 'A data de fundação não pode ser no futuro.',
        ]);
    }
}