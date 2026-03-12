<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Estado;
use App\Models\Cidade;
use App\Models\Equipe;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\StravaController;

class ProfileController extends Controller
{
    /**
     * Exibe o formulário de perfil do utilizador.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $estados = Estado::orderBy('nome')->get();
        $cidades = collect();
        if($user->atleta?->estado_id) {
            $cidades = Cidade::where('estado_id', $user->atleta->estado_id)->orderBy('nome')->get();
        }
        $equipes = Equipe::orderBy('nome')->get();

        $stravaRedirectUri = StravaController::redirectUriComputed();
        $stravaCallbackDomain = parse_url($stravaRedirectUri, PHP_URL_HOST) ?: '';

        return view('profile.edit', [
            'user' => $user,
            'estados' => $estados,
            'cidades' => $cidades,
            'equipes' => $equipes,
            'strava_redirect_uri' => $stravaRedirectUri,
            'strava_callback_domain' => $stravaCallbackDomain,
        ]);
    }

    /**
     * Atualiza as informações do perfil do utilizador.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Atualiza os dados específicos do atleta, se for um atleta
        if ($user->isAtleta() && $user->atleta) {
            $atletaData = $request->validate([
                'telefone' => 'nullable|string|max:20',
                'data_nascimento' => 'nullable|date',
                'sexo' => 'nullable|in:masculino,feminino',
                'estado_id' => 'nullable|exists:estados,id',
                'cidade_id' => 'nullable|exists:cidades,id',
                'equipe_id' => 'nullable|exists:equipes,id',
                'tipo_sanguineo' => 'nullable|string|max:3',
                'contato_emergencia_nome' => 'nullable|string|max:255',
                'contato_emergencia_telefone' => 'nullable|string|max:20',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB; webp para fotos de celular
            ]);

            // Lógica de upload da foto
            if ($request->hasFile('foto')) {
                // Apaga a foto antiga, se existir
                if ($user->atleta->foto_url) {
                    Storage::disk('public')->delete($user->atleta->foto_url);
                }
                // Salva a nova foto e obtém o caminho
                $path = $request->file('foto')->store('atletas/fotos', 'public');
                $atletaData['foto_url'] = $path;
            }

            $user->atleta->update($atletaData);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Apaga a conta do utilizador.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

