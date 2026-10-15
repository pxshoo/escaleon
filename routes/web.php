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


use App\Http\Controllers\AssistantController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/assistant/ask', [AssistantController::class, 'ask'])->name('assistant.ask');

    Route::get('/oauth/{provider}/connect', [OAuthController::class, 'redirectToProvider'])
        ->whereIn('provider', ['microsoft', 'google'])
        ->name('oauth.connect');

    Route::get('/oauth/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])
        ->whereIn('provider', ['microsoft', 'google'])
        ->name('oauth.callback');
});

use App\Services\GoogleWorkspaceService;
Route::get('/dashboard', function () {

    $connectedAccounts = auth()->user()->connectedAccounts?->keyBy('provider') ?? collect();

    $googleAccount = $connectedAccounts->get('google');



    $googleService = new GoogleWorkspaceService($googleAccount);



    $emails = $googleService->getRecentEmails(4);

    $events = $googleService->getUpcomingEvents(4);

    $tasks = $googleService->getTasks(4);



    return view('dashboard', compact('connectedAccounts', 'emails', 'events', 'tasks'));

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/assistant/ask', [App\Http\Controllers\AssistantController::class, 'ask'])->name('assistant.ask');

    Route::get('/oauth/{provider}/connect', [OAuthController::class, 'redirectToProvider'])
        ->whereIn('provider', ['microsoft', 'google'])
        ->name('oauth.connect');

    Route::get('/oauth/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])
        ->whereIn('provider', ['microsoft', 'google'])
        ->name('oauth.callback');
});

require __DIR__.'/auth.php'; 