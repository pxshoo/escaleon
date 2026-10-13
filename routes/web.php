<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthController;

Route::get('/', function () {
    return view('welcome');
});

use App\Models\ConnectedAccount;

Route::get('/dashboard', function () {
    $connectedAccounts = auth()->user()->connectedAccounts?->keyBy('provider') ?? collect();

    return view('dashboard', compact('connectedAccounts'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas para iniciar la vinculación
    Route::get('/oauth/{provider}/connect', [OAuthController::class, 'redirectToProvider'])
        ->whereIn('provider', ['microsoft', 'google'])
        ->name('oauth.connect');

    // Callback donde redirigen Microsoft y Google
    Route::get('/oauth/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])
        ->whereIn('provider', ['microsoft', 'google'])
        ->name('oauth.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
