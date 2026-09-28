<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\SolicitudController;
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

    Route::get('/ofertas', [OfertaController::class, 'index'])->name('ofertas.index');
    Route::get('/ofertas/nueva', [OfertaController::class, 'create'])->name('ofertas.create');
    Route::post('/ofertas', [OfertaController::class, 'store'])->name('ofertas.store');
    Route::get('/ofertas/{oferta}/editar', [OfertaController::class, 'edit'])->name('ofertas.edit');
    Route::put('/ofertas/{oferta}', [OfertaController::class, 'update'])->name('ofertas.update');

    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/nueva', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::patch('/solicitudes/{busqueda}/estado', [SolicitudController::class, 'cambiarEstado'])->name('solicitudes.estado');

    Route::view('/candidatos', 'placeholder', ['titulo' => 'Candidatos'])->name('candidatos.index');
    Route::view('/mi-perfil', 'placeholder', ['titulo' => 'Mi perfil'])->name('candidatos.perfil');
    Route::get('/postulaciones', function () {
        if (auth()->user()->esEmpresa()) {
            abort(403);
        }

        return view('placeholder', ['titulo' => 'Postulaciones']);
    })->name('postulaciones.index');
    Route::view('/mi-empresa', 'placeholder', ['titulo' => 'Mi empresa'])->name('empresas.perfil');
    Route::view('/usuarios', 'placeholder', ['titulo' => 'Usuarios'])->name('usuarios.index');
});
