<?php

namespace App\Http\Controllers;

use App\Models\ConnectedAccount;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function disconnectGoogle(Request $request): RedirectResponse
    {
        $request->user()->connectedAccounts()->where('provider', 'google')->delete();
        $request->session()->forget(['assistant_reply', '_old_input']);

        return redirect()->route('dashboard')->with('status', 'Cuenta de Google desconectada.');
    }

    // Permisos requeridos para Microsoft Graph
    protected array $azureScopes = [
        'offline_access',
        'User.Read',
        'Mail.Read',
        'Calendars.Read',
        'Tasks.Read',
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
        if ($provider === 'google') {
            request()->session()->forget('google_login');
        }
        if ($provider === 'microsoft' && (! config('services.azure.client_id') || ! config('services.azure.client_secret'))) {
            return redirect()->route('dashboard')->with('status', 'Falta configurar AZURE_CLIENT_ID y AZURE_CLIENT_SECRET para conectar Outlook empresarial.');
        }
        $driverName = $provider === 'microsoft' ? 'azure' : 'google';
        $scopes = $provider === 'microsoft' ? $this->azureScopes : $this->googleScopes;

        $driver = Socialite::driver($driverName)->scopes($scopes);

        if ($provider === 'google') {
            $driver->with(['access_type' => 'offline', 'prompt' => 'select_account consent']);
        }

        return $driver->redirect();
    }

    public function handleProviderCallback(string $provider): RedirectResponse
    {
        if ($provider === 'google' && request()->session()->get('google_login') && ! Auth::check()) {
            return app(GoogleLoginController::class)->complete(request());
        }
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        $driverName = $provider === 'microsoft' ? 'azure' : 'google';

        try {
            $socialUser = Socialite::driver($driverName)
                ->setHttpClient(new Client(['connect_timeout' => 3, 'timeout' => 8]))
                ->user();
        } catch (\Throwable) {
            return redirect()->route('dashboard')->with('status', 'No se pudo conectar la cuenta. Vuelve a intentarlo.');
        }

        $user = Auth::user();
        if ($provider === 'google' && strcasecmp($user->email, $socialUser->getEmail() ?? '') === 0
            && ($socialUser->user['email_verified'] ?? $socialUser->user['verified_email'] ?? false)) {
            $owner = User::where('google_id', $socialUser->getId())->first();
            if (! $owner || $owner->id === $user->id) {
                $user->forceFill(['google_id' => $socialUser->getId()])->save();
            }
        }

        $account = ConnectedAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken ?: $user->connectedAccounts()->where('provider', $provider)->value('refresh_token'),
                'expires_at' => isset($socialUser->expiresIn) ? Carbon::now()->addSeconds($socialUser->expiresIn) : null,
                'scopes' => $socialUser->approvedScopes ?? [],
            ]
        );
        Cache::forget('workspace:'.$user->id.':'.$account->id);

        return redirect()->route('dashboard')->with('status', ucfirst($provider).' conectado exitosamente.');
    }
}
