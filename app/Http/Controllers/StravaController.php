<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class StravaController extends Controller
{
    /**
     * URL de callback usada na autorização e na troca de token.
     * Deve ser EXATAMENTE a mesma que o domínio configurado no Strava (Authorization Callback Domain).
     * STRAVA_REDIRECT_URI no .env define a URL completa; senão usa APP_URL + /strava/callback.
     */
    private function getRedirectUri(): string
    {
        return self::redirectUriComputed();
    }

    /**
     * Retorna a redirect_uri que será enviada ao Strava (para exibir no perfil ou debug).
     */
    public static function redirectUriComputed(): string
    {
        $config = config('services.strava.redirect_uri');
        if (!empty(trim((string) $config))) {
            return rtrim(trim($config), '/');
        }
        $base = rtrim(config('app.url'), '/');
        if (empty($base)) {
            $base = 'http://localhost';
        }
        if (app()->environment('production') && str_starts_with($base, 'http://')) {
            $base = 'https://' . substr($base, 7);
        }
        return $base . '/strava/callback';
    }

    /**
     * Redireciona o utilizador para a página de autorização do Strava.
     * Apenas utilizadores com perfil de atleta podem conectar.
     */
    public function connect(): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->atleta) {
            return redirect()->route('profile.edit')
                ->withErrors('Complete seu perfil de atleta antes de conectar o Strava.');
        }

        $stravaConfig = config('services.strava');
        if (empty($stravaConfig['client_id'])) {
            return redirect()->route('profile.edit')
                ->withErrors('Strava não está configurado. Contacte o administrador.');
        }

        $redirectUri = $this->getRedirectUri();

        $queryParams = http_build_query([
            'client_id' => $stravaConfig['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'read,activity:read',
        ]);

        return redirect()->away('https://www.strava.com/oauth/authorize?' . $queryParams);
    }

    /**
     * Recebe o callback do Strava após a autorização.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('profile.edit')->withErrors('A conexão com o Strava foi cancelada.');
        }

        if (!$request->filled('code')) {
            return redirect()->route('profile.edit')->withErrors('Resposta inválida do Strava. Tente conectar novamente.');
        }

        $user = Auth::user();
        $atleta = $user->atleta;

        if (!$atleta) {
            return redirect()->route('profile.edit')
                ->withErrors('Complete seu perfil de atleta antes de conectar o Strava.');
        }

        $stravaConfig = config('services.strava');
        $redirectUri = $this->getRedirectUri();

        // Pequeno atraso para evitar falha intermitente na validação do code no Strava
        usleep(500000); // 0.5s

        $response = Http::asForm()->post('https://www.strava.com/oauth/token', [
            'client_id' => $stravaConfig['client_id'],
            'client_secret' => $stravaConfig['client_secret'],
            'code' => $request->code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->failed()) {
            Log::warning('Strava token exchange failed', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'body' => $response->body(),
                'redirect_uri' => $redirectUri,
            ]);
            $domain = parse_url($redirectUri, PHP_URL_HOST);
            return redirect()->route('profile.edit')->withErrors(
                'Não foi possível obter os tokens do Strava. No painel do Strava (Settings > My API Application), em "Authorization Callback Domain" use exatamente: ' . ($domain ?: $redirectUri) . ' — e a URL de callback deve ser: ' . $redirectUri
            );
        }

        $stravaData = $response->json();
        if (empty($stravaData['athlete']['id']) || empty($stravaData['access_token'])) {
            return redirect()->route('profile.edit')->withErrors('Resposta inválida do Strava. Tente novamente.');
        }

        $athlete = $stravaData['athlete'];
        $profilePhotoUrl = $athlete['profile'] ?? $athlete['profile_medium'] ?? null;

        $atleta->update([
            'strava_id' => $athlete['id'],
            'strava_access_token' => $stravaData['access_token'],
            'strava_refresh_token' => $stravaData['refresh_token'] ?? null,
            'strava_token_expires_at' => isset($stravaData['expires_at'])
                ? Carbon::createFromTimestamp($stravaData['expires_at'])
                : null,
            'strava_profile_photo_url' => $profilePhotoUrl,
        ]);

        return redirect()->route('profile.edit')->with('sucesso', 'Sua conta foi conectada ao Strava com sucesso!');
    }

    /**
     * Desconecta a conta do Strava do perfil do atleta (por utilizador).
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
                'strava_profile_photo_url' => null,
            ]);
        }

        return redirect()->route('profile.edit')->with('sucesso', 'Sua conta foi desconectada do Strava.');
    }
}
