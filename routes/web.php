<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::get('/auth/google', [GoogleLoginController::class, 'redirect'])->middleware('guest')->name('google.login');
Route::get('/oauth/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])->whereIn('provider', ['google', 'microsoft'])->name('oauth.callback');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');
    Route::post('/assistant/ask', [AssistantController::class, 'ask'])->middleware('throttle:10,1')->name('assistant.ask');
    Route::get('/oauth/{provider}/connect', [OAuthController::class, 'redirectToProvider'])->whereIn('provider', ['google', 'microsoft'])->name('oauth.connect');
    Route::delete('/oauth/google/disconnect', [OAuthController::class, 'disconnectGoogle'])->name('oauth.google.disconnect');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
