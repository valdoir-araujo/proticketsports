<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class StravaController extends Controller
{
    /**
     * Redireciona o utilizador para a página de autorização do Strava.
     */
    public function connect(): RedirectResponse
    {
        $stravaConfig = config('services.strava');

        $queryParams = http_build_query([
            'client_id' => $stravaConfig['client_id'],
            'redirect_uri' => $stravaConfig['redirect_uri'],
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'read_all,activity:read_all', // Solicita permissão para ler perfil e atividades
        ]);

        return redirect()->away('https://www.strava.com/oauth/authorize?' . $queryParams);
    }

    /**
     * Recebe o callback do Strava após a autorização.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Verifica se o Strava retornou um erro
        if ($request->has('error')) {
            return redirect()->route('profile.edit')->withErrors('A conexão com o Strava foi cancelada.');
        }

        $stravaConfig = config('services.strava');
        $user = Auth::user();
        $atleta = $user->atleta;

        // Troca o código de autorização temporário pelos tokens de acesso permanentes
        $response = Http::post('https://www.strava.com/oauth/token', [
            'client_id' => $stravaConfig['client_id'],
            'client_secret' => $stravaConfig['client_secret'],
            'code' => $request->code,
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            return redirect()->route('profile.edit')->withErrors('Não foi possível obter os tokens do Strava. Tente novamente.');
        }

        $stravaData = $response->json();

        // Salva os dados do Strava no perfil do atleta
        $atleta->update([
            'strava_id' => $stravaData['athlete']['id'],
            'strava_access_token' => $stravaData['access_token'],
            'strava_refresh_token' => $stravaData['refresh_token'],
            'strava_token_expires_at' => Carbon::createFromTimestamp($stravaData['expires_at']),
        ]);

        return redirect()->route('profile.edit')->with('sucesso', 'Sua conta foi conectada ao Strava com sucesso!');
    }

    /**
     * Desconecta a conta do Strava do perfil do atleta.
     */
    public function disconnect(): RedirectResponse
    {
        $atleta = Auth::user()->atleta;

        if ($atleta) {
            $atleta->update([
                'strava_id' => null,
                'strava_access_token' => null,
                'strava_refresh_token' => null,
                'strava_token_expires_at' => null,
            ]);
        }

        return redirect()->route('profile.edit')->with('sucesso', 'Sua conta foi desconectada do Strava.');
    }
}
