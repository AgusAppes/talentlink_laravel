<?php

namespace App\Http\Controllers;

use App\Models\Busqueda;
use App\Models\Ciudad;
use App\Models\EstadoBusqueda;
use App\Models\Modalidad;
use App\Models\Notificacion;
use App\Models\PersonalRrhh;
use App\Models\Provincia;
use App\Models\SolicitudDocumento;
use Illuminate\Http\Request;

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
            $consulta->where('empresas_id', $usuario->empresa?->id);
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
}
