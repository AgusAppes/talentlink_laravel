<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntrevistaController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->rutaInicio());
    }

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

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/leer', [NotificacionController::class, 'leer'])->name('notificaciones.leer');

    Route::get('/cambiar-password', [PerfilController::class, 'editPassword'])->name('password.edit');
    Route::put('/cambiar-password', [PerfilController::class, 'updatePassword'])->name('password.update');

    Route::middleware('permiso:dashboard.ver')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/metricas', [DashboardController::class, 'metricas'])->name('dashboard.metricas');
    });

    Route::middleware('permiso:ofertas.ver')->group(function () {
        Route::get('/ofertas', [OfertaController::class, 'index'])->name('ofertas.index');
    });

    Route::middleware('permiso:ofertas.crear')->group(function () {
        Route::get('/ofertas/nueva', [OfertaController::class, 'create'])->name('ofertas.create');
        Route::post('/ofertas', [OfertaController::class, 'store'])->name('ofertas.store');
        Route::get('/ofertas/{oferta}/editar', [OfertaController::class, 'edit'])->name('ofertas.edit');
        Route::put('/ofertas/{oferta}', [OfertaController::class, 'update'])->name('ofertas.update');
    });

    Route::middleware('permiso:solicitudes.ver|solicitudes.crear')->group(function () {
        Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    });

    Route::middleware('permiso:solicitudes.crear')->group(function () {
        Route::get('/solicitudes/nueva', [SolicitudController::class, 'create'])->name('solicitudes.create');
        Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    });

    Route::patch('/solicitudes/{busqueda}/estado', [SolicitudController::class, 'cambiarEstado'])
        ->middleware('permiso:solicitudes.editar')
        ->name('solicitudes.estado');

    Route::middleware('permiso:candidatos.ver')->group(function () {
        Route::get('/candidatos', [CandidatoController::class, 'index'])->name('candidatos.index');
        Route::get('/candidatos/{candidato}', [CandidatoController::class, 'show'])->name('candidatos.show');
    });

    Route::middleware('permiso:candidatos.editar')->group(function () {
        Route::get('/mi-perfil', [PerfilController::class, 'candidato'])->name('candidatos.perfil');
        Route::put('/mi-perfil', [PerfilController::class, 'actualizarCandidato'])->name('candidatos.perfil.update');
        Route::post('/mi-perfil/experiencias', [PerfilController::class, 'storeExperiencia'])->name('perfil.experiencias.store');
        Route::delete('/mi-perfil/experiencias/{id}', [PerfilController::class, 'destroyExperiencia'])->name('perfil.experiencias.destroy');
    });

    Route::get('/postulaciones', [PostulacionController::class, 'index'])
        ->middleware('permiso:postulaciones.ver')
        ->name('postulaciones.index');

    Route::get('/postulaciones/{id}/cv', [PostulacionController::class, 'cv'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('postulaciones.cv');

    Route::post('/ofertas/{oferta}/postular', [PostulacionController::class, 'store'])
        ->middleware('permiso:postulaciones.crear')
        ->name('postulaciones.store');

    Route::delete('/ofertas/{oferta}/postular', [PostulacionController::class, 'destroy'])
        ->middleware('permiso:postulaciones.crear')
        ->name('postulaciones.destroy');

    Route::patch('/postulaciones/{postulacion}/etapa', [PostulacionController::class, 'cambiarEtapa'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('postulaciones.etapa');

    Route::get('/mi-perfil/horario', [CalendarioController::class, 'horario'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('perfil.horario');

    Route::put('/mi-perfil/horario', [CalendarioController::class, 'guardarHorario'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('perfil.horario.update');

    Route::get('/calendario', [CalendarioController::class, 'index'])
        ->middleware('permiso:postulaciones.ver|postulaciones.gestionar')
        ->name('calendario');

    Route::post('/calendario/bloqueos', [CalendarioController::class, 'guardarBloqueo'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('calendario.bloqueos.store');

    Route::delete('/calendario/bloqueos/{bloqueo}', [CalendarioController::class, 'eliminarBloqueo'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('calendario.bloqueos.destroy');

    Route::post('/postulaciones/{postulacion}/entrevista', [EntrevistaController::class, 'store'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('entrevistas.store');

    Route::get('/postulaciones/{postulacion}/entrevista', [EntrevistaController::class, 'elegir'])
        ->middleware('permiso:postulaciones.crear')
        ->name('entrevistas.elegir');

    Route::post('/postulaciones/{postulacion}/entrevista/confirmar', [EntrevistaController::class, 'confirmar'])
        ->middleware('permiso:postulaciones.crear')
        ->name('entrevistas.confirmar');

    Route::post('/entrevistas/{entrevista}/cancelar', [EntrevistaController::class, 'cancelar'])
        ->middleware('permiso:postulaciones.ver|postulaciones.gestionar')
        ->name('entrevistas.cancelar');

    Route::post('/postulaciones/{postulacion}/compatibilidad', [PostulacionController::class, 'compatibilidad'])
        ->middleware('permiso:postulaciones.gestionar')
        ->name('postulaciones.compatibilidad');

    Route::middleware('permiso:empresas.editar')->group(function () {
        Route::get('/mi-empresa', [PerfilController::class, 'empresa'])->name('empresas.perfil');
        Route::put('/mi-empresa', [PerfilController::class, 'actualizarEmpresa'])->name('empresas.perfil.update');
    });

    Route::middleware('permiso:usuarios.ver')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/roles', [UsuarioController::class, 'roles'])->name('usuarios.roles');
    });

    Route::middleware('permiso:usuarios.administrar')->group(function () {
        Route::get('/usuarios/nuevo', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/roles/{rol}/permisos', [UsuarioController::class, 'editarPermisos'])->name('usuarios.roles.edit');
        Route::put('/roles/{rol}/permisos', [UsuarioController::class, 'actualizarPermisos'])->name('usuarios.roles.update');
    });
});
