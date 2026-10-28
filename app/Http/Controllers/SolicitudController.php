<?php

namespace App\Http\Controllers;

use App\Models\Busqueda;
use App\Models\Candidato;
use App\Models\Ciudad;
use App\Models\Envio;
use App\Models\EstadoBusqueda;
use App\Models\Modalidad;
use App\Models\Notificacion;
use App\Models\PersonalRrhh;
use App\Models\Provincia;
use App\Models\SolicitudDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SolicitudController extends Controller
{
    // Esta función muestra el listado de solicitudes
    // En terminos tecnicos, cuando se visita /solicitudes, se ejecuta esta función
    // y sirve solicitudes/index.blade.php con las búsquedas del admin o de la empresa
    public function index()
    {
        $usuario = auth()->user();

        $consulta = Busqueda::query()
            ->with(['empresa', 'estado', 'oferta.estado'])
            ->orderByDesc('id');

        if ($usuario->esEmpresa()) {
            $consulta->where('empresas_id', $usuario->empresa?->id)
                ->withCount(['postulaciones as candidatos_recibidos' => function ($postulaciones) {
                    $postulaciones->whereHas('envio');
                }]);
        }

        $busquedas = $consulta->paginate(10)->withQueryString();
        Busqueda::hidratarFichas($busquedas);

        return view('solicitudes.index', [
            'busquedas' => $busquedas,
            'estados' => $usuario->esAdmin()
                ? EstadoBusqueda::query()->orderBy('id')->get()
                : collect(),
        ]);
    }

    // Esta función muestra el formulario de alta
    // En terminos tecnicos, cuando la empresa visita /solicitudes/nueva, se ejecuta esta función
    // y sirve solicitudes/create.blade.php con modalidades, provincias y ciudades
    public function create()
    {
        $provinciaElegida = old('provincias_id');

        if (! $provinciaElegida && old('ciudades_id')) {
            $provinciaElegida = Ciudad::query()->whereKey(old('ciudades_id'))->value('provincias_id');
        }

        return view('solicitudes.create', [
            'modalidades' => Modalidad::query()->orderBy('nombre')->get(),
            'provincias' => Provincia::query()->orderBy('nombre')->get(),
            'ciudades' => Ciudad::query()->orderBy('nombre')->get(['id', 'nombre', 'provincias_id']),
            'provinciaElegida' => $provinciaElegida,
        ]);
    }

    // Esta función guarda una solicitud nueva
    // En terminos tecnicos, cuando se envía el formulario de alta, se ejecuta esta función
    // y crea la búsqueda en MySQL y la ficha con las habilidades en MongoDB
    public function store(Request $request)
    {
        $empresa = $request->user()->empresa;

        if (! $empresa) {
            abort(403);
        }

        $ciudadId = $request->input('ciudades_id') ?: null;
        $provinciaId = null;
        $paisId = null;

        if ($ciudadId) {
            $ciudad = Ciudad::query()->with('provincia')->findOrFail($ciudadId);
            $provinciaId = $ciudad->provincias_id;
            $paisId = $ciudad->provincia->paises_id;
        }

        $busqueda = Busqueda::create([
            'nombre_puesto' => $request->input('nombre_puesto'),
            'empresas_id' => $empresa->id,
            'estado_busqueda_id' => 1,
        ]);

        try {
            SolicitudDocumento::guardar($busqueda->id, [
                'descripcion' => $request->input('descripcion'),
                'cantidad_vacantes' => $request->input('cantidad_vacantes'),
                'anios_experiencia' => $request->input('anios_experiencia'),
                'modalidades_id' => $request->input('modalidades_id'),
                'ciudades_id' => $ciudadId,
                'provincias_id' => $provinciaId,
                'paises_id' => $paisId,
                'habilidades' => $request->input('habilidades', []),
            ]);
        } catch (\Throwable $e) {
            $busqueda->delete();

            throw $e;
        }

        foreach (PersonalRrhh::query()->pluck('usuarios_id') as $usuariosId) {
            Notificacion::avisar(
                $usuariosId,
                $empresa->nombre.' cargó una solicitud para '.$busqueda->nombre_puesto.'.'
            );
        }

        return redirect()
            ->route('solicitudes.index')
            ->with('ok', 'Solicitud registrada correctamente.');
    }

    // Esta función cambia el estado de una solicitud
    // En terminos tecnicos, cuando el admin envía el formulario de una fila, se ejecuta esta función
    // y actualiza estado_busqueda_id
    public function cambiarEstado(Request $request, Busqueda $busqueda)
    {
        $busqueda->update([
            'estado_busqueda_id' => $request->input('estado_busqueda_id'),
        ]);

        return back()->with('ok', 'Estado actualizado.');
    }

    // Esta función muestra los candidatos que RRHH envió en una solicitud
    // En terminos tecnicos, cuando la empresa abre Candidatos recibidos, se ejecuta esta función
    // y sirve solicitudes/candidatos.blade.php con los envíos de esa búsqueda
    public function candidatos(Busqueda $busqueda)
    {
        $this->busquedaDeLaEmpresa($busqueda);

        $envios = Envio::query()
            ->whereHas('postulacion.oferta', function ($oferta) use ($busqueda) {
                $oferta->where('busquedas_id', $busqueda->id);
            })
            ->with([
                'postulacion.candidatos.usuario',
                'entrevistaEmpresa',
            ])
            ->orderByDesc('id')
            ->get();

        return view('solicitudes.candidatos', compact('busqueda', 'envios'));
    }

    // Esta función muestra el perfil de un candidato recibido
    // En terminos tecnicos, cuando la empresa abre Ver perfil, se ejecuta esta función
    // y sirve solicitudes/perfil.blade.php si ese candidato fue enviado en la búsqueda
    public function perfil(Busqueda $busqueda, Candidato $candidato)
    {
        $this->busquedaDeLaEmpresa($busqueda);
        abort_unless($this->candidatoEnviado($busqueda, $candidato), 404);

        $candidato->load(['usuario', 'ciudad.provincia']);

        return view('solicitudes.perfil', compact('busqueda', 'candidato'));
    }

    // Esta función guarda el día y horario que la empresa propone al candidato
    // En terminos tecnicos, cuando envía el modal Solicitar entrevista, se ejecuta esta función
    // y crea o actualiza entrevistas_empresa y avisa al candidato
    public function solicitarEntrevista(Request $request, Envio $envio)
    {
        $envio->load('postulacion.oferta.busqueda.empresa', 'postulacion.candidatos');
        $busqueda = $envio->postulacion->oferta->busqueda;
        $this->busquedaDeLaEmpresa($busqueda);

        $datos = $request->validate([
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'nota' => ['nullable', 'string', 'max:500'],
        ], [
            'fecha.required' => 'El día es obligatorio.',
            'fecha.date' => 'El día no es válido.',
            'hora.required' => 'El horario es obligatorio.',
            'hora.date_format' => 'El horario no es válido.',
            'nota.max' => 'La nota no puede superar los 500 caracteres.',
        ]);

        $inicio = Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'].' '.$datos['hora'], CalendarioController::ZONA)->startOfMinute();

        if ($inicio->lte(Carbon::now(CalendarioController::ZONA))) {
            throw ValidationException::withMessages([
                'fecha' => 'Elegí un día y un horario posteriores a ahora.',
            ]);
        }

        $nota = trim((string) ($datos['nota'] ?? ''));
        $nota = $nota !== '' ? $nota : null;

        $envio->entrevistaEmpresa()->updateOrCreate(
            ['envios_id' => $envio->id],
            [
                'inicio' => $inicio->copy()->utc(),
                'nota' => $nota,
            ]
        );

        $candidato = $envio->postulacion->candidatos->first();

        if ($candidato) {
            $texto = $busqueda->empresa->nombre.' te propone una entrevista para '.$busqueda->nombre_puesto.' el '.$inicio->format('d/m/Y').' a las '.$inicio->format('H:i').'.';

            if ($nota) {
                $texto .= ' Nota: '.$nota;
            }

            Notificacion::avisar($candidato->usuarios_id, $texto);
        }

        return back()->with('ok', 'Enviaste la solicitud de entrevista al candidato.');
    }

    // Esta función comprueba que la solicitud sea de la empresa logueada
    // En terminos tecnicos, cuando la empresa abre candidatos, el perfil o pide una entrevista, se ejecuta esta función
    // y corta con 403 si la búsqueda es de otra empresa
    private function busquedaDeLaEmpresa(Busqueda $busqueda): void
    {
        $empresaId = auth()->user()->empresa?->id;

        abort_unless($empresaId && (int) $busqueda->empresas_id === (int) $empresaId, 403);
    }

    // Esta función dice si el candidato fue enviado en esa solicitud
    // En terminos tecnicos, cuando se abre el perfil, se ejecuta esta función
    // y busca un envío de la oferta de esa búsqueda para ese candidato
    private function candidatoEnviado(Busqueda $busqueda, Candidato $candidato): bool
    {
        return Envio::query()
            ->whereHas('postulacion', function ($postulacion) use ($busqueda, $candidato) {
                $postulacion->whereHas('oferta', function ($oferta) use ($busqueda) {
                    $oferta->where('busquedas_id', $busqueda->id);
                })->whereHas('candidatos', function ($candidatos) use ($candidato) {
                    $candidatos->where('candidatos.id', $candidato->id);
                });
            })
            ->exists();
    }
}
