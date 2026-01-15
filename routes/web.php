<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ROUTE FALLBACK
Route::fallback(function () {
    // Sudah login → arahkan ke dashboard
    if (Auth::check()) {
        return redirect()->route('beranda');
    }

    // Belum login → arahkan ke login
    return redirect()->route('login');
});

Route::get('/', fn () => redirect('/login'));

// ROUTES LOGIN
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// MIDDLEWARE LOGIN
Route::middleware([AuthMiddleware::class])->group(function () {
    // DASHBOARD
    Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');
});