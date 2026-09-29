<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\PerfilDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    // Esta función muestra el perfil del candidato logueado
    public function candidato()
    {
        $candidato = auth()->user()->candidato;

        if (! $candidato) {
            abort(403);
        }

        $candidato->load(['ciudad.provincia']);

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
    // y actualiza los datos, la foto y las habilidades
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
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'habilidades' => ['nullable', 'array', 'max:10'],
            'habilidades.*' => ['string', 'max:50'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 45 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede superar los 45 caracteres.',
            'fecha_nac.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nac.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'ciudades_id.exists' => 'La ciudad seleccionada no es válida.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.mimes' => 'La foto debe ser JPG o PNG.',
            'foto.max' => 'La foto no puede superar los 2 MB.',
            'habilidades.max' => 'Podés cargar hasta 10 habilidades.',
            'habilidades.*.max' => 'Cada habilidad puede tener hasta 50 caracteres.',
        ]);

        $foto = $candidato->foto;

        if ($request->hasFile('foto')) {
            if ($foto) {
                Storage::disk('public')->delete($foto);
            }

            $foto = $request->file('foto')->store('fotos', 'public');
        } elseif ($request->boolean('quitar_foto') && $foto) {
            Storage::disk('public')->delete($foto);
            $foto = null;
        }

        DB::transaction(function () use ($request, $candidato, $datos, $foto) {
            $candidato->update([
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'fecha_nac' => $datos['fecha_nac'],
                'ciudades_id' => $datos['ciudades_id'],
                'descripcion' => $datos['descripcion'],
                'foto' => $foto,
            ]);

            PerfilDocumento::deCandidato((int) $candidato->id)
                ->guardarHabilidades($request->input('habilidades', []));
        });

        return redirect()
            ->route('candidatos.perfil')
            ->with('ok', 'Perfil actualizado correctamente.');
    }

    // Esta función agrega una experiencia laboral
    // En terminos tecnicos, cuando se envía el formulario de experiencia, se ejecuta esta función
    // y agrega la experiencia, con sus habilidades, al documento del candidato
    public function storeExperiencia(Request $request)
    {
        $candidato = $request->user()->candidato;

        if (! $candidato) {
            abort(403);
        }

        $datos = $request->validate([
            'empresa' => ['required', 'string', 'max:100'],
            'puesto' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date_format:Y-m'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m', 'after_or_equal:fecha_desde'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'habilidades_experiencia' => ['nullable', 'array', 'max:10'],
            'habilidades_experiencia.*' => ['string', 'max:50'],
        ], [
            'empresa.required' => 'El nombre de la empresa es obligatorio.',
            'empresa.max' => 'El nombre de la empresa no puede superar los 100 caracteres.',
            'puesto.max' => 'El puesto no puede superar los 100 caracteres.',
            'fecha_desde.date_format' => 'La fecha de inicio no es válida.',
            'fecha_hasta.date_format' => 'La fecha de fin no es válida.',
            'fecha_hasta.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'habilidades_experiencia.max' => 'Podés cargar hasta 10 habilidades en la experiencia.',
            'habilidades_experiencia.*.max' => 'Cada habilidad puede tener hasta 50 caracteres.',
        ]);

        PerfilDocumento::deCandidato((int) $candidato->id)->agregarExperiencia([
            'empresa' => $datos['empresa'],
            'puesto' => $datos['puesto'] ?? null,
            'fecha_desde' => ($datos['fecha_desde'] ?? null) ? $datos['fecha_desde'].'-01' : null,
            'fecha_hasta' => ($datos['fecha_hasta'] ?? null) ? $datos['fecha_hasta'].'-01' : null,
            'descripcion' => $datos['descripcion'] ?? null,
            'habilidades' => $datos['habilidades_experiencia'] ?? [],
        ]);

        return redirect()
            ->route('candidatos.perfil')
            ->with('ok', 'Experiencia agregada.');
    }

    // Esta función elimina una experiencia laboral
    // En terminos tecnicos, cuando el candidato envía el formulario de eliminar, se ejecuta esta función
    // y borra solo una experiencia de su propio perfil
    public function destroyExperiencia(Request $request, $id)
    {
        $candidato = $request->user()->candidato;

        if (! $candidato) {
            abort(403);
        }

        $perfil = PerfilDocumento::deCandidato((int) $candidato->id);

        if (! $perfil->quitarExperiencia((string) $id)) {
            abort(404);
        }

        return redirect()
            ->route('candidatos.perfil')
            ->with('ok', 'Experiencia eliminada.');
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

    // Esta función muestra el formulario para cambiar la contraseña
    // En terminos tecnicos, cuando se visita /cambiar-password, se ejecuta esta función
    // y sirve perfil/password.blade.php
    public function editPassword()
    {
        return view('perfil.password');
    }

    // Esta función guarda la contraseña nueva
    // En terminos tecnicos, cuando se envía el formulario de /cambiar-password, se ejecuta esta función
    // y actualiza la contraseña del usuario logueado
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:password_actual'],
        ], [
            'password_actual.required' => 'Ingresá tu contraseña actual.',
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Ingresá la nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación no coincide con la nueva contraseña.',
            'password.different' => 'La nueva contraseña tiene que ser distinta de la actual.',
        ]);

        $usuario = $request->user();
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        return back()->with('ok', 'Contraseña actualizada correctamente.');
    }
}
