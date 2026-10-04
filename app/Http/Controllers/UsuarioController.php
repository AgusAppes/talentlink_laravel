<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // Esta función muestra el listado de usuarios para el admin
    // En terminos tecnicos, cuando el admin abre /usuarios, se ejecuta esta función
    // y trae cada usuario con su rol y su perfil para armar el nombre
    public function index()
    {
        $usuarios = User::query()
            ->with(['rol', 'empresa', 'candidato', 'personalRrhh'])
            ->orderBy('roles_id')
            ->orderBy('correo')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    // Esta función muestra el formulario de alta
    // En terminos tecnicos, cuando el admin abre /usuarios/nuevo, se ejecuta esta función
    // y sirve el formulario de Personal RRHH o Empresa
    public function create()
    {
        return view('usuarios.create');
    }

    // Esta función crea el usuario y su perfil
    // En terminos tecnicos, cuando se envía el formulario de alta, se ejecuta esta función
    // y guarda el usuario junto con personal_rrhh o empresas en una sola transacción
    public function store(Request $request)
    {
        $datos = $request->validate([
            'correo' => ['unique:usuarios,correo'],
            'password' => ['min:6', 'confirmed'],
            'nombre' => ['required_if:roles_id,1'],
            'apellido' => ['required_if:roles_id,1'],
            'empresa_nombre' => ['required_if:roles_id,2'],
        ], [
            'correo.unique' => 'Ese correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'nombre.required_if' => 'El nombre es obligatorio para Personal RRHH.',
            'apellido.required_if' => 'El apellido es obligatorio para Personal RRHH.',
            'empresa_nombre.required_if' => 'El nombre de la empresa es obligatorio.',
        ]);

        DB::transaction(function () use ($datos, $request) {
            $usuario = User::create([
                'roles_id' => $request->input('roles_id'),
                'correo' => $request->input('correo'),
                'password' => Hash::make($datos['password']),
            ]);

            if ((int) $request->input('roles_id') === 1) {
                $usuario->personalRrhh()->create([
                    'nombre' => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                ]);
            }

            if ((int) $request->input('roles_id') === 2) {
                $usuario->empresa()->create([
                    'nombre' => $datos['empresa_nombre'],
                ]);
            }
        });

        return redirect()
            ->route('usuarios.index')
            ->with('ok', 'Usuario creado correctamente.');
    }

    // Esta función muestra los roles y cuántos permisos tiene cada uno
    // En terminos tecnicos, cuando el admin abre /roles, se ejecuta esta función
    // y cuenta usuarios y permisos de cada rol
    public function roles()
    {
        $roles = Rol::query()
            ->withCount(['usuarios', 'permisos'])
            ->orderBy('id')
            ->get();

        return view('usuarios.roles', compact('roles'));
    }

    // Esta función muestra las casillas de permisos de un rol
    // En terminos tecnicos, cuando el admin abre /roles/{id}/permisos, se ejecuta esta función
    // y trae los permisos del rol y el catálogo completo
    public function editarPermisos(Rol $rol)
    {
        $rol->load('permisos');

        $permisos = Permiso::query()->orderBy('nombre')->get();

        return view('usuarios.roles_edit', compact('rol', 'permisos'));
    }

    // Esta función guarda los permisos marcados de un rol
    // En terminos tecnicos, cuando se envía el formulario de permisos, se ejecuta esta función
    // y sincroniza la tabla permisos_por_roles, sin quitarle al admin usuarios.ver ni usuarios.administrar
    public function actualizarPermisos(Request $request, Rol $rol)
    {
        $ids = array_map('intval', $request->input('permisos', []));

        if ((int) $rol->id === 1) {
            $obligatorios = Permiso::query()
                ->whereIn('nombre', ['usuarios.ver', 'usuarios.administrar'])
                ->pluck('id')
                ->all();

            $ids = array_values(array_unique(array_merge($ids, $obligatorios)));
        }

        $rol->permisos()->sync($ids);

        return redirect()
            ->route('usuarios.roles')
            ->with('ok', 'Permisos actualizados.');
    }
}
