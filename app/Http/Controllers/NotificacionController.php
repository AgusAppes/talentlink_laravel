<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // Esta función devuelve las notificaciones no leídas
    // En terminos tecnicos, cuando la campana pide /notificaciones, se ejecuta esta función
    // y responde el texto de cada aviso para actualizar la campana sin recargar
    public function index(Request $request)
    {
        Notificacion::recordatorios($request->user());

        $textos = Notificacion::query()
            ->where('usuarios_id', $request->user()->id)
            ->where('leida', false)
            ->latest()
            ->pluck('texto');

        return response()->json([
            'textos' => $textos,
        ]);
    }

    // Esta función marca como leídas las notificaciones del usuario
    // En terminos tecnicos, cuando envía Marcar leídas en la campana, se ejecuta esta función
    // y pone leida en true en las filas de ese usuario
    public function leer(Request $request)
    {
        Notificacion::query()
            ->where('usuarios_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return back();
    }
}
