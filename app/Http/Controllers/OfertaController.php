<?php

namespace App\Http\Controllers;

use App\Models\Busqueda;
use App\Models\EstadoOferta;
use App\Models\Oferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OfertaController extends Controller
{
    // Esta función muestra las ofertas según el rol
    // En terminos tecnicos, cuando se visita /ofertas, se ejecuta esta función
    // y sirve el feed del candidato o la tabla de admin y empresa
    public function index()
    {
        Gate::authorize('viewAny', Oferta::class);

        $usuario = auth()->user();

        if ($usuario->esCandidato()) {
            $ofertas = Oferta::query()
                ->where('estado_ofertas_id', 1)
                ->with([
                    'busqueda.empresa',
                    'busqueda.detalle.modalidad',
                    'busqueda.detalle.ciudad.provincia',
                    'busqueda.habilidades',
                ])
                ->orderByDesc('id')
                ->get();

            return view('ofertas.feed', compact('ofertas'));
        }

        if ($usuario->esEmpresa() && ! $usuario->empresa) {
            abort(403);
        }

        $consulta = Oferta::query()
            ->with([
                'busqueda.empresa',
                'busqueda.detalle.modalidad',
                'busqueda.detalle.ciudad.provincia',
                'personalRrhh',
                'estado',
            ])
            ->orderByDesc('id');

        if ($usuario->esEmpresa()) {
            $consulta->whereHas('busqueda', function ($busqueda) use ($usuario) {
                $busqueda->where('empresas_id', $usuario->empresa->id);
            });
        }

        return view('ofertas.index', [
            'ofertas' => $consulta->get(),
        ]);
    }

    // Esta función muestra el formulario para publicar una oferta
    // En terminos tecnicos, cuando el admin visita /ofertas/nueva, se ejecuta esta función
    // y sirve ofertas/create.blade.php con las solicitudes que todavía no tienen oferta
    public function create(Request $request)
    {
        Gate::authorize('create', Oferta::class);

        if (! $request->user()->personalRrhh) {
            abort(403);
        }

        $solicitudes = Busqueda::query()
            ->with('empresa')
            ->doesntHave('oferta')
            ->where('estado_busqueda_id', '!=', 3)
            ->orderByDesc('id')
            ->get();

        return view('ofertas.create', [
            'solicitudes' => $solicitudes,
            'estados' => EstadoOferta::query()->orderBy('id')->get(),
            'busquedaPreseleccionada' => $request->query('busquedas_id'),
        ]);
    }

    // Esta función publica una oferta
    // En terminos tecnicos, cuando se envía el formulario de alta, se ejecuta esta función
    // y crea la oferta y pasa la solicitud a En oferta dentro de una transacción
    public function store(Request $request)
    {
        Gate::authorize('create', Oferta::class);

        $rrhh = $request->user()->personalRrhh;

        if (! $rrhh) {
            abort(403);
        }

        $datos = $request->validate([
            'busquedas_id' => ['required', 'exists:busquedas,id', 'unique:ofertas,busquedas_id'],
            'estado_ofertas_id' => ['required', 'exists:estado_ofertas,id'],
        ], [
            'busquedas_id.required' => 'La solicitud es obligatoria.',
            'busquedas_id.exists' => 'La solicitud seleccionada no es válida.',
            'busquedas_id.unique' => 'Esa solicitud ya tiene una oferta publicada.',
            'estado_ofertas_id.required' => 'El estado es obligatorio.',
            'estado_ofertas_id.exists' => 'El estado seleccionado no es válido.',
        ]);

        $busqueda = Busqueda::query()->findOrFail($datos['busquedas_id']);

        if ((int) $busqueda->estado_busqueda_id === 3) {
            return back()
                ->withInput()
                ->withErrors(['busquedas_id' => 'No se puede publicar una solicitud cerrada.']);
        }

        DB::transaction(function () use ($datos, $rrhh, $busqueda) {
            Oferta::create([
                'busquedas_id' => $datos['busquedas_id'],
                'personal_rrhh_id' => $rrhh->id,
                'estado_ofertas_id' => $datos['estado_ofertas_id'],
            ]);

            $busqueda->update([
                'estado_busqueda_id' => 2,
            ]);
        });

        return redirect()
            ->route('ofertas.index')
            ->with('ok', 'Oferta publicada correctamente.');
    }

    // Esta función muestra el formulario para cambiar el estado de la oferta
    // En terminos tecnicos, cuando el admin visita /ofertas/{id}/editar, se ejecuta esta función
    // y sirve ofertas/edit.blade.php con el puesto y los estados
    public function edit(Oferta $oferta)
    {
        Gate::authorize('update', $oferta);

        $oferta->load('busqueda.empresa');

        return view('ofertas.edit', [
            'oferta' => $oferta,
            'estados' => EstadoOferta::query()->orderBy('id')->get(),
        ]);
    }

    // Esta función guarda el estado nuevo de la oferta
    // En terminos tecnicos, cuando el admin envía el formulario de edición, se ejecuta esta función
    // y actualiza estado_ofertas_id
    public function update(Request $request, Oferta $oferta)
    {
        Gate::authorize('update', $oferta);

        $datos = $request->validate([
            'estado_ofertas_id' => ['required', 'exists:estado_ofertas,id'],
        ], [
            'estado_ofertas_id.required' => 'El estado es obligatorio.',
            'estado_ofertas_id.exists' => 'El estado seleccionado no es válido.',
        ]);

        $oferta->update([
            'estado_ofertas_id' => $datos['estado_ofertas_id'],
        ]);

        return redirect()
            ->route('ofertas.index')
            ->with('ok', 'Estado de la oferta actualizado.');
    }
}
