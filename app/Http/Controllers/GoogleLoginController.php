<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirect(Request $request)
    {
        $request->session()->put('google_login', true);

        return Socialite::driver('google')->setScopes(['openid', 'email', 'profile'])
            ->with(['prompt' => 'select_account'])->redirect();
    }

    public function complete(Request $request)
    {
        $request->session()->forget('google_login');
        try {
            $identity = Socialite::driver('google')
                ->setHttpClient(new Client(['connect_timeout' => 3, 'timeout' => 8]))->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('status', 'No se pudo ingresar con Google. Inténtalo nuevamente.');
        }
        if (! $identity->getEmail() || ! ($identity->user['email_verified'] ?? $identity->user['verified_email'] ?? false)) {
            return redirect()->route('login')->with('status', 'Google debe confirmar tu dirección de correo.');
        }
        $user = User::where('google_id', $identity->getId())->first();
        if (! $user) {
            // Existing accounts must explicitly link Google while logged in.
            if (User::where('email', $identity->getEmail())->exists()) {
                return redirect()->route('login')->with('status', 'Este correo ya tiene una cuenta. Ingresa con tu contraseña y conecta Google para habilitar este acceso.');
            }
            $user = User::create(['name' => $identity->getName() ?: 'Usuario Google', 'email' => $identity->getEmail(), 'password' => Str::random(64)]);
            $user->forceFill(['google_id' => $identity->getId(), 'email_verified_at' => now()])->save();
        }
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
