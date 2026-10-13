<?php

namespace App\Http\Controllers;

use App\Models\ConnectedAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

class OAuthController extends Controller
{
    // Permisos requeridos para Microsoft Graph
    protected array $azureScopes = [
        'offline_access',
        'User.Read',
        'Mail.Read',
        'Calendars.Read',
        'Tasks.ReadWrite',
    ];

    // Permisos requeridos para Google Workspace
    protected array $googleScopes = [
        'openid',
        'profile',
        'email',
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/calendar.readonly',
        'https://www.googleapis.com/auth/tasks',
    ];

    public function redirectToProvider(string $provider): RedirectResponse
    {
        $driverName = $provider === 'microsoft' ? 'azure' : 'google';
        $scopes = $provider === 'microsoft' ? $this->azureScopes : $this->googleScopes;

        $driver = Socialite::driver($driverName)->scopes($scopes);

        if ($provider === 'google') {
            $driver->with(['access_type' => 'offline', 'prompt' => 'consent']);
        }

        return $driver->redirect();
    }

    public function handleProviderCallback(string $provider): RedirectResponse
    {
        $driverName = $provider === 'microsoft' ? 'azure' : 'google';

        // Desactiva la verificación de certificados de Guzzle en entorno local
        $socialUser = Socialite::driver($driverName)
            ->stateless()
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
            ->user();

        $user = Auth::user();

        ConnectedAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'expires_at' => isset($socialUser->expiresIn) ? Carbon::now()->addSeconds($socialUser->expiresIn) : null,
                'scopes' => $socialUser->approvedScopes ?? [],
            ]
        );

        return redirect()->route('dashboard')->with('status', ucfirst($provider) . ' conectado exitosamente.');
    }
}