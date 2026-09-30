<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

if (app()->isLocal()) {
    Route::post('/dev/login', function () {
        $user = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'google_id' => 'local-dev-admin', 'credits' => 10]
        );

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    })->name('dev.login');
}

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth'])
    ->name('dashboard');

// --- ADMIN PANEL ---
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
    Route::get('/users', \App\Livewire\Admin\UsersManager::class)->name('admin.users');
    Route::get('/logs', \App\Livewire\SystemLogs::class)->name('admin.logs');
});

Route::get('/summary/{summary}/download', [\App\Http\Controllers\SummaryController::class, 'download'])
    ->middleware(['auth'])
    ->name('summary.download');

Route::post('/credits/checkout', [\App\Http\Controllers\CreditsController::class, 'checkout'])
    ->middleware(['auth'])
    ->name('credits.checkout');
