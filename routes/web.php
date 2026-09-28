<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registro'])->name('registro.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('placeholder', ['titulo' => 'Dashboard']);
    })->name('dashboard');

    Route::get('/solicitudes/create', function () {
        return view('placeholder', ['titulo' => 'Nueva solicitud']);
    })->name('solicitudes.create');

    Route::get('/ofertas', function () {
        return view('placeholder', ['titulo' => 'Ofertas']);
    })->name('ofertas.index');
});
