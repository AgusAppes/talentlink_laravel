<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    // Esta función muestra el perfil del candidato logueado
    public function candidato()
    {
        $candidato = auth()->user()->candidato;

        if (! $candidato) {
            abort(403);
        }

        $candidato->load('ciudad.provincia');

        $ciudades = Ciudad::query()
            ->with('provincia')
            ->join('provincias', 'ciudades.provincias_id', '=', 'provincias.id')
            ->orderBy('provincias.nombre')
            ->orderBy('ciudades.nombre')
            ->select('ciudades.*')
            ->get();

        return view('perfiles.candidato', compact('candidato', 'ciudades'));
    }

    // Esta función guarda el perfil del candidato
    // En terminos tecnicos, cuando se envía el formulario de /mi-perfil, se ejecuta esta función
    // y actualiza nombre, apellido, fecha de nacimiento y ciudad
    public function actualizarCandidato(Request $request)
    {
        $candidato = $request->user()->candidato;

        if (! $candidato) {
            abort(403);
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:45'],
            'apellido' => ['required', 'string', 'max:45'],
            'fecha_nac' => ['nullable', 'date', 'before:today'],
            'ciudades_id' => ['nullable', 'exists:ciudades,id'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 45 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede superar los 45 caracteres.',
            'fecha_nac.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nac.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'ciudades_id.exists' => 'La ciudad seleccionada no es válida.',
        ]);

        $candidato->update([
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'fecha_nac' => $datos['fecha_nac'],
            'ciudades_id' => $datos['ciudades_id'],
        ]);

        return redirect()
            ->route('candidatos.perfil')
            ->with('ok', 'Perfil actualizado correctamente.');
    }

    // Esta función muestra el perfil de la empresa logueada
    public function empresa()
    {
        $empresa = auth()->user()->empresa;

        if (! $empresa) {
            abort(403);
        }

        return view('perfiles.empresa', compact('empresa'));
    }

    // Esta función guarda el nombre de la empresa
    // En terminos tecnicos, cuando se envía el formulario de /mi-empresa, se ejecuta esta función
    // y actualiza el nombre
    public function actualizarEmpresa(Request $request)
    {
        $empresa = $request->user()->empresa;

        if (! $empresa) {
            abort(403);
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ], [
            'nombre.required' => 'El nombre de la empresa es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
        ]);

        $empresa->update([
            'nombre' => $datos['nombre'],
        ]);

        return redirect()
            ->route('empresas.perfil')
            ->with('ok', 'Datos de la empresa actualizados.');
    }
}
