<?php

namespace App\Http\Controllers;

use App\Models\Etapa;
use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostulacionController extends Controller
{
    // Esta función muestra las postulaciones según el rol
    // En terminos tecnicos, cuando se visita /postulaciones, se ejecuta esta función
    // y sirve el listado del candidato o la tabla del admin
    public function index()
    {
        $usuario = auth()->user();

        if ($usuario->esCandidato()) {
            $postulaciones = Postulacion::query()
                ->whereHas('candidatos', function ($candidatos) use ($usuario) {
                    $candidatos->where('candidatos.id', $usuario->candidato?->id);
                })
                ->with([
                    'etapa',
                    'oferta.estado',
                    'oferta.busqueda.empresa',
                    'oferta.busqueda.detalle.modalidad',
                ])
                ->orderByDesc('id')
                ->get();

            return view('postulaciones.mis', compact('postulaciones'));
        }

        return view('postulaciones.index', [
            'postulaciones' => Postulacion::query()
                ->with([
                    'candidatos',
                    'etapa',
                    'oferta.estado',
                    'oferta.busqueda.empresa',
                ])
                ->orderByDesc('id')
                ->get(),
            'etapas' => Etapa::query()->orderBy('id')->get(),
        ]);
    }

    // Esta función registra la postulación del candidato
    // En terminos tecnicos, cuando se envía el formulario de una tarjeta, se ejecuta esta función
    // y crea la postulación en etapa Pendiente de revisión
    public function store(Oferta $oferta)
    {
        $candidato = auth()->user()->candidato;

        if (! $candidato) {
            abort(403);
        }

        if ((int) $oferta->estado_ofertas_id !== 1) {
            return back()->with('error', 'Esta oferta ya no está disponible.');
        }

        $yaPostulo = $candidato->postulaciones()
            ->where('postulaciones.ofertas_id', $oferta->id)
            ->exists();

        if ($yaPostulo) {
            return back()->with('error', 'Ya te postulaste a esta oferta.');
        }

        DB::transaction(function () use ($oferta, $candidato) {
            $postulacion = Postulacion::create([
                'ofertas_id' => $oferta->id,
                'etapas_id' => 1,
            ]);

            $postulacion->candidatos()->attach($candidato->id);
        });

        return redirect()
            ->route('postulaciones.index')
            ->with('ok', 'Te postulaste correctamente.');
    }

    // Esta función cambia la etapa de una postulación
    // En terminos tecnicos, cuando el admin envía el formulario de una fila, se ejecuta esta función
    // y actualiza etapas_id
    public function cambiarEtapa(Request $request, Postulacion $postulacion)
    {
        $datos = $request->validate([
            'etapas_id' => ['required', 'exists:etapas,id'],
        ], [
            'etapas_id.required' => 'La etapa es obligatoria.',
            'etapas_id.exists' => 'La etapa seleccionada no es válida.',
        ]);

        $postulacion->update([
            'etapas_id' => $datos['etapas_id'],
        ]);

        return back()->with('ok', 'Etapa actualizada.');
    }
}
