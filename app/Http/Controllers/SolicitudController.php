<?php

namespace App\Http\Controllers;

use App\Models\Busqueda;
use App\Models\Ciudad;
use App\Models\EstadoBusqueda;
use App\Models\Modalidad;
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
        if (! auth()->user()->empresa) {
            abort(403);
        }

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

        $datos = $request->validate([
            'nombre_puesto' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'cantidad_vacantes' => ['required', 'integer', 'min:1'],
            'anios_experiencia' => ['nullable', 'integer', 'min:0'],
            'modalidades_id' => ['required', 'exists:modalidades,id'],
            'ciudades_id' => ['nullable', 'exists:ciudades,id'],
            'habilidades' => ['nullable', 'array', 'max:15'],
            'habilidades.*' => ['string', 'max:50'],
        ], [
            'nombre_puesto.required' => 'El nombre del puesto es obligatorio.',
            'nombre_puesto.max' => 'El nombre del puesto no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'cantidad_vacantes.required' => 'La cantidad de vacantes es obligatoria.',
            'cantidad_vacantes.integer' => 'La cantidad de vacantes debe ser un número.',
            'cantidad_vacantes.min' => 'La cantidad de vacantes debe ser al menos 1.',
            'anios_experiencia.integer' => 'Los años de experiencia deben ser un número.',
            'anios_experiencia.min' => 'Los años de experiencia no pueden ser negativos.',
            'modalidades_id.required' => 'La modalidad es obligatoria.',
            'modalidades_id.exists' => 'La modalidad seleccionada no es válida.',
            'ciudades_id.exists' => 'La ciudad seleccionada no es válida.',
            'habilidades.array' => 'Las habilidades no son válidas.',
            'habilidades.max' => 'Podés cargar hasta 15 habilidades.',
            'habilidades.*.max' => 'Cada habilidad puede tener hasta 50 caracteres.',
        ]);

        $ciudadId = $datos['ciudades_id'] ?? null;
        $provinciaId = null;
        $paisId = null;

        if ($ciudadId) {
            $ciudad = Ciudad::query()->with('provincia')->findOrFail($ciudadId);
            $provinciaId = $ciudad->provincias_id;
            $paisId = $ciudad->provincia->paises_id;
        }

        $busqueda = Busqueda::create([
            'nombre_puesto' => $datos['nombre_puesto'],
            'empresas_id' => $empresa->id,
            'estado_busqueda_id' => 1,
        ]);

        try {
            SolicitudDocumento::guardar($busqueda->id, [
                'descripcion' => $datos['descripcion'] ?? null,
                'cantidad_vacantes' => $datos['cantidad_vacantes'],
                'anios_experiencia' => $datos['anios_experiencia'] ?? null,
                'modalidades_id' => $datos['modalidades_id'],
                'ciudades_id' => $ciudadId,
                'provincias_id' => $provinciaId,
                'paises_id' => $paisId,
                'habilidades' => $datos['habilidades'] ?? [],
            ]);
        } catch (\Throwable $e) {
            $busqueda->delete();

            throw $e;
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
        $datos = $request->validate([
            'estado_busqueda_id' => ['required', 'exists:estado_busqueda,id'],
        ], [
            'estado_busqueda_id.required' => 'El estado es obligatorio.',
            'estado_busqueda_id.exists' => 'El estado seleccionado no es válido.',
        ]);

        $busqueda->update([
            'estado_busqueda_id' => $datos['estado_busqueda_id'],
        ]);

        return back()->with('ok', 'Estado actualizado.');
    }
}
