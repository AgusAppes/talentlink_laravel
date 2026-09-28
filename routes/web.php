<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PostulacionController;
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

    Route::get('/candidatos', [CandidatoController::class, 'index'])->name('candidatos.index');
    Route::get('/candidatos/{candidato}', [CandidatoController::class, 'show'])->name('candidatos.show');
    Route::get('/mi-perfil', [PerfilController::class, 'candidato'])->name('candidatos.perfil');
    Route::put('/mi-perfil', [PerfilController::class, 'actualizarCandidato'])->name('candidatos.perfil.update');
    Route::get('/postulaciones', [PostulacionController::class, 'index'])->name('postulaciones.index');
    Route::post('/ofertas/{oferta}/postular', [PostulacionController::class, 'store'])->name('postulaciones.store');
    Route::patch('/postulaciones/{postulacion}/etapa', [PostulacionController::class, 'cambiarEtapa'])->name('postulaciones.etapa');
    Route::get('/mi-empresa', [PerfilController::class, 'empresa'])->name('empresas.perfil');
    Route::put('/mi-empresa', [PerfilController::class, 'actualizarEmpresa'])->name('empresas.perfil.update');
    Route::view('/usuarios', 'placeholder', ['titulo' => 'Usuarios'])->name('usuarios.index');
});
