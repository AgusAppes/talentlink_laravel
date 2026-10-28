<?php

namespace App\Http\Controllers;

use App\Experto\EvaluadorCompatibilidad;
use App\Models\Busqueda;
use App\Models\Entrevista;
use App\Models\Etapa;
use App\Models\Notificacion;
use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
                    'entrevistas' => function ($consulta) {
                        $consulta->whereIn('estado', [Entrevista::SOLICITADA, Entrevista::CONFIRMADA]);
                    },
                ])
                ->orderByDesc('id')
                ->get();

            Busqueda::hidratarFichas($postulaciones->pluck('oferta.busqueda'));

            return view('postulaciones.mis', compact('postulaciones'));
        }

        return view('postulaciones.index', [
            'postulaciones' => Postulacion::query()
                ->with([
                    'candidatos',
                    'etapa',
                    'oferta.estado',
                    'oferta.busqueda.empresa',
                    'entrevistas' => function ($consulta) {
                        $consulta->whereIn('estado', [Entrevista::SOLICITADA, Entrevista::CONFIRMADA]);
                    },
                ])
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    // Esta función muestra el detalle de una postulación
    // En terminos tecnicos, cuando se visita /postulaciones/{postulacion}, se ejecuta esta función
    // y sirve el archivo postulaciones.show.blade.php
    public function show(Postulacion $postulacion)
    {
        $postulacion->load([
            'candidatos',
            'etapa',
            'oferta.estado',
            'oferta.busqueda.empresa',
            'entrevistas' => function ($consulta) {
                $consulta->whereIn('estado', [Entrevista::SOLICITADA, Entrevista::CONFIRMADA]);
            },
        ]);

        return view('postulaciones.show', compact('postulacion'));
    }

    // Esta función muestra el formulario para gestionar una postulación
    // En terminos tecnicos, cuando se visita /postulaciones/{postulacion}/editar, se ejecuta esta función
    // y sirve postulaciones.edit.blade.php con las etapas y si el reclutador tiene horarios
    public function edit(Postulacion $postulacion)
    {
        $postulacion->load([
            'candidatos.usuario',
            'envio',
            'etapa',
            'oferta.busqueda.empresa',
            'entrevistas' => function ($consulta) {
                $consulta->whereIn('estado', [Entrevista::SOLICITADA, Entrevista::CONFIRMADA]);
            },
        ]);

        $reclutador = auth()->user()->personalRrhh;

        return view('postulaciones.edit', [
            'postulacion' => $postulacion,
            'etapas' => Etapa::query()->orderBy('id')->get(),
            'tieneHorario' => $reclutador !== null && $reclutador->disponibilidades()->exists(),
        ]);
    }

    // Esta función registra la postulación del candidato
    // En terminos tecnicos, cuando se envía el formulario de una tarjeta, se ejecuta esta función
    // y crea la postulación en etapa Pendiente de revisión
    public function store(Request $request, Oferta $oferta)
    {
        $candidato = $request->user()->candidato;

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

        try {
            $request->validate([
                'cv' => $oferta->requiere_cv ? 'required|file|mimes:pdf|max:5120' : 'nullable',
            ], [
                'cv.required' => 'Esta oferta requiere que adjuntes tu CV.',
                'cv.mimes' => 'El CV debe ser un archivo PDF.',
                'cv.max' => 'El CV no puede superar los 5 MB.',
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->with('cv_oferta', $oferta->id);
        }

        $rutaCv = null;

        if ($oferta->requiere_cv) {
            $rutaCv = $request->file('cv')->store('cvs', config('filesystems.cv_disk'));

            if (! $rutaCv) {
                return back()->with('error', 'No se pudo guardar el CV.')->with('cv_oferta', $oferta->id);
            }
        }

        $postulacion = DB::transaction(function () use ($oferta, $candidato, $rutaCv) {
            $postulacion = Postulacion::create([
                'ofertas_id' => $oferta->id,
                'etapas_id' => 1,
                'cv' => $rutaCv,
            ]);

            $postulacion->candidatos()->attach($candidato->id);

            return $postulacion;
        });

        app(EvaluadorCompatibilidad::class)->guardar($postulacion, $candidato);

        return redirect()
            ->route('postulaciones.index')
            ->with('ok', 'Te postulaste correctamente.');
    }

    // Esta función cancela la postulación del candidato
    // En terminos tecnicos, cuando envía el formulario de cancelar, se ejecuta esta función
    // y borra su postulación a esa oferta
    public function destroy(Request $request, Oferta $oferta)
    {
        $candidato = $request->user()->candidato;

        if (! $candidato) {
            abort(403);
        }

        $postulacion = $candidato->postulaciones()
            ->where('postulaciones.ofertas_id', $oferta->id)
            ->first();

        if (! $postulacion) {
            return back()->with('error', 'No tenés una postulación a esta oferta.');
        }

        $postulacion->eliminar();

        return back()->with('ok', 'Cancelaste tu postulación.');
    }

    // Esta función cambia la etapa de una postulación
    // En terminos tecnicos, cuando el admin envía el formulario de una fila, se ejecuta esta función
    // y actualiza etapas_id
    public function cambiarEtapa(Request $request, Postulacion $postulacion)
    {
        $etapaAnterior = (int) $postulacion->etapas_id;

        $postulacion->update([
            'etapas_id' => $request->input('etapas_id'),
        ]);

        if ((int) $postulacion->etapas_id !== $etapaAnterior) {
            $postulacion->load(['etapa', 'oferta.busqueda']);
            $candidato = $postulacion->candidatos()->first();

            if ($candidato && $postulacion->etapa) {
                Notificacion::avisar(
                    $candidato->usuarios_id,
                    'Tu postulación a '.$postulacion->oferta->busqueda->nombre_puesto.' pasó a '.$postulacion->etapa->nombre.'.'
                );
            }
        }

        return back()->with('ok', 'Etapa actualizada.');
    }

    // Esta función guarda la ficha que la empresa va a recibir
    // En terminos tecnicos, cuando RRHH envía el modal de la postulación, se ejecuta esta función
    // y crea o actualiza la fila de envios si la etapa es Aprobada o En entrevista
    public function enviar(Request $request, Postulacion $postulacion)
    {
        $reclutador = $request->user()->personalRrhh;

        if (! $reclutador) {
            abort(403);
        }

        if (! $postulacion->puedeEnviarse()) {
            return back()->with('error', 'Este candidato se puede enviar cuando la etapa es Aprobada o En entrevista.');
        }

        $datos = $request->validate([
            'telefono' => ['nullable', 'string', 'max:40'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ], [
            'telefono.max' => 'El teléfono no puede superar los 40 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 2000 caracteres.',
            'comentario.max' => 'El comentario no puede superar los 2000 caracteres.',
        ]);

        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $descripcion = trim((string) ($datos['descripcion'] ?? ''));
        $comentario = trim((string) ($datos['comentario'] ?? ''));
        $yaEnviado = $postulacion->envio()->exists();

        $postulacion->envio()->updateOrCreate(
            ['postulaciones_id' => $postulacion->id],
            [
                'personal_rrhh_id' => $reclutador->id,
                'telefono' => $telefono !== '' ? $telefono : null,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'comentario' => $comentario !== '' ? $comentario : null,
            ]
        );

        return back()->with('ok', $yaEnviado
            ? 'Actualizaste el envío a la empresa.'
            : 'Enviaste el candidato a la empresa.');
    }

    // Esta función vuelve a calcular la compatibilidad de una postulación
    // En terminos tecnicos, cuando el reclutador envía Calcular en el listado, se ejecuta esta función
    // y guarda el porcentaje y las reglas que se cumplieron
    public function compatibilidad(Postulacion $postulacion, EvaluadorCompatibilidad $evaluador)
    {
        $candidato = $postulacion->candidatos()->first();

        if (! $candidato) {
            return back()->with('error', 'Esta postulación no tiene candidato.');
        }

        if (! $evaluador->guardar($postulacion, $candidato)) {
            return back()->with('error', 'No se pudo calcular la compatibilidad.');
        }

        return back()->with('ok', 'Se calculó la compatibilidad.');
    }

    // Esta función muestra el CV de una postulación
    // En terminos tecnicos, cuando el admin abre /postulaciones/{id}/cv, se ejecuta esta función
    // y devuelve el PDF guardado en el disco de CV
    public function cv($id)
    {
        $postulacion = Postulacion::findOrFail($id);
        $disco = config('filesystems.cv_disk');

        abort_if(! $postulacion->cv || ! Storage::disk($disco)->exists($postulacion->cv), 404);

        return Storage::disk($disco)->response($postulacion->cv);
    }
}
