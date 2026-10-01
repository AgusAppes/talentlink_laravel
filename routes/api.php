<?php

use App\Http\Controllers\MovilController;
use Illuminate\Support\Facades\Route;

Route::post('/movil/login', [MovilController::class, 'login']);
Route::get('/movil/ofertas', [MovilController::class, 'ofertas']);
Route::post('/movil/ofertas/{oferta}/postular', [MovilController::class, 'postular']);
