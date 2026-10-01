<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    // Esta función muestra el formulario de login
    // En terminos tecnicos, cuando se visita la ruta /login, se ejecuta esta función
    // que sirve el archivo auth.login.blade.php
    public function showLogin()
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    // Esta función valida los datos del formulario de login
    // En terminos tecnicos, cuando se envía el formulario de login, se ejecuta esta función
    // que valida los datos del formulario y si son correctos, se inicia la sesión del usuario
    // y se redirige al dashboard si es admin, a la creación de solicitudes si es empresa, o a la lista de ofertas si es candidato
    public function login(Request $request)
    {
        // Validamos los datos del formulario de login
        // Se verifica que los datos llegan y que son correctos
        $datos = $request->validate([
            // required, email, max:100 son reglas de validación nativas de Laravel
            // required: indica que el campo es obligatorio
            // email: indica que el campo debe ser un correo electrónico
            // max:100: indica que el campo no puede tener más de 100 caracteres
            'correo' => ['required', 'email', 'max:100'],
            'password' => ['required'],
        ], 
        // Mensajes de error personalizados
        // Estos mensajes se muestran dependiendo cual de las reglas definidas arriba no se cumplen
        // Por ejemplo, si el correo no es un correo electrónico (no tiene el @), se muestra el mensaje 'El correo no es válido.'
        [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.max' => 'El correo no puede superar los 100 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Auth es un facade de Laravel, un facade es una clase que proporciona acceso a metodos estaticos de una clase
        // En este caso, Auth es una clase que proporciona acceso a metodos como attempt, logout, etc.
        // attempt es un método que recibe un array de datos y realiza una consulta a la base de datos con esos datos
        // Como sabe Auth en que tabla buscar?
        // Auth sabe en que tabla buscar porque en el archivo config/auth.php se define el provider 'users'
        // El provider 'users' es el encargado de buscar el usuario en la base de datos
        if (! Auth::attempt(['correo' => $datos['correo'], 'password' => $datos['password']])) {
            return back()
                ->withInput($request->only('correo'))
                ->withErrors(['correo' => 'Correo o contraseña incorrectos.']);
        }

        // regenerate es un método que genera un nuevo token de sesión
        // sirve para evitar ataques de sesión
        $request->session()->regenerate();

        // Redirige a la vista que corresponda segun el rol del usuario
        return redirect()->route(auth()->user()->rutaInicio());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showRegistro()
    {
        $ciudades = Ciudad::query()
            ->with('provincia')
            ->orderBy('nombre')
            ->get();

        return response()
            ->view('auth.registro', compact('ciudades'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function registro(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'max:45'],
            'apellido' => ['required', 'max:45'],
            'correo' => ['required', 'email', 'max:100', 'unique:usuarios,correo'],
            'password' => ['required', 'min:6', 'confirmed'],
            'fecha_nac' => ['nullable', 'date', 'before:today'],
            'ciudades_id' => ['nullable', 'exists:ciudades,id'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 45 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede superar los 45 caracteres.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.max' => 'El correo no puede superar los 100 caracteres.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'fecha_nac.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nac.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'ciudades_id.exists' => 'La ciudad seleccionada no es válida.',
        ]);

        // DB::transaction es un método que permite ejecutar una transacción de base de datos
        // una transacción es un conjunto de operaciones que se ejecutan como una unidad
        // si una de las operaciones falla, todas las operaciones se deshacen
        // si todas las operaciones se ejecutan correctamente, la transacción se completa
        // en este caso, se crea el usuario y se crea el candidato
        // si alguna de las operaciones falla, se deshace la transacción y se muestra un mensaje de error
        DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'roles_id' => 3,
                'correo' => $datos['correo'],
                'password' => Hash::make($datos['password']),
            ]);

            $usuario->candidato()->create([
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'fecha_nac' => $datos['fecha_nac'] ?? null,
                'ciudades_id' => $datos['ciudades_id'] ?? null,
            ]);
        });

        return redirect()
            ->route('login')
            ->with('ok', 'Cuenta creada. Iniciá sesión con tu correo y contraseña.');
    }
}
