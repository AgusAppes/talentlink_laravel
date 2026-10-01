<?php

namespace App\Http\Controllers;

use App\Models\Busqueda;
use App\Models\Candidato;
use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MovilController extends Controller
{
    // Esta función inicia la sesión del candidato en la aplicación
    // En terminos tecnicos, cuando la app envía el login a /api/movil/login, se ejecuta esta función
    // y devuelve un token si el correo y la contraseña son de un candidato
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email', 'max:100'],
            'password' => ['required'],
        ], [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.max' => 'El correo no puede superar los 100 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = User::query()->where('correo', $datos['correo'])->first();

        if (! $usuario || ! Hash::check($datos['password'], $usuario->password)) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Correo o contraseña incorrectos.',
            ], 422);
        }

        if (! $usuario->esCandidato() || ! $usuario->candidato) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Esta aplicación es solo para candidatos.',
            ], 422);
        }

        return $this->iniciarSesion($usuario);
    }

    // Esta función crea la cuenta de un candidato desde la aplicación
    // En terminos tecnicos, cuando la app envía el registro a /api/movil/registro, se ejecuta esta función
    // y crea el usuario con rol candidato, junto con su perfil, y devuelve el token
    public function registro(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'max:45'],
            'apellido' => ['required', 'max:45'],
            'correo' => ['required', 'email', 'max:100', 'unique:usuarios,correo'],
            'password' => ['required', 'min:6', 'confirmed'],
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
        ]);

        $usuario = DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'roles_id' => 3,
                'correo' => $datos['correo'],
                'password' => Hash::make($datos['password']),
            ]);

            $usuario->candidato()->create([
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
            ]);

            return $usuario;
        });

        return $this->iniciarSesion($usuario);
    }

    // Esta función devuelve las ofertas publicadas
    // En terminos tecnicos, cuando la app abre el feed, se ejecuta esta función
    // y responde JSON con las ofertas activas y si el candidato ya se postuló
    public function ofertas(Request $request): JsonResponse
    {
        $candidato = $this->candidatoDesdeToken($request);
        $postuladas = $candidato->postulaciones()->pluck('ofertas_id');

        $ofertas = Oferta::query()
            ->where('estado_ofertas_id', 1)
            ->with(['busqueda.empresa'])
            ->orderByDesc('id')
            ->get();

        Busqueda::hidratarFichas($ofertas->pluck('busqueda'));

        $ofertas = $ofertas->map(function (Oferta $oferta) use ($postuladas) {
            $ficha = $oferta->busqueda?->ficha;
            $ciudad = $ficha?->ciudad
                ? $ficha->ciudad->nombre.($ficha->ciudad->provincia ? ', '.$ficha->ciudad->provincia->nombre : '')
                : null;

            return [
                'id' => $oferta->id,
                'puesto' => $oferta->busqueda?->nombre_puesto,
                'empresa' => $oferta->busqueda?->empresa?->nombre,
                'modalidad' => $ficha?->modalidad?->nombre,
                'ciudad' => $ciudad,
                'vacantes' => $ficha?->cantidad_vacantes,
                'descripcion' => $ficha?->descripcion,
                'requiere_cv' => (bool) $oferta->requiere_cv,
                'ya_postulada' => $postuladas->contains($oferta->id),
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'ofertas' => $ofertas,
        ]);
    }

    // Esta función registra la postulación del candidato
    // En terminos tecnicos, cuando la app envía una oferta guardada en el teléfono, se ejecuta esta función
    // y crea la postulación en etapa Pendiente de revisión
    public function postular(Request $request, Oferta $oferta): JsonResponse
    {
        $candidato = $this->candidatoDesdeToken($request);

        if ((int) $oferta->estado_ofertas_id !== 1) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Esta oferta ya no está disponible.',
            ], 422);
        }

        if ($oferta->requiere_cv) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Esta oferta pide CV. Postulate desde la web.',
            ], 422);
        }

        $yaPostulo = $candidato->postulaciones()
            ->where('postulaciones.ofertas_id', $oferta->id)
            ->exists();

        if ($yaPostulo) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ya te postulaste a esta oferta.',
            ], 422);
        }

        DB::transaction(function () use ($oferta, $candidato) {
            $postulacion = Postulacion::create([
                'ofertas_id' => $oferta->id,
                'etapas_id' => 1,
            ]);

            $postulacion->candidatos()->attach($candidato->id);
        });

        return response()->json([
            'ok' => true,
            'mensaje' => 'Te postulaste correctamente.',
        ]);
    }

    // Esta función guarda el token y lo devuelve a la aplicación
    // En terminos tecnicos, cuando el login o el registro salen bien, se ejecuta esta función
    // y responde el token junto con el nombre del candidato
    private function iniciarSesion(User $usuario): JsonResponse
    {
        $token = Str::random(60);
        $usuario->api_token = hash('sha256', $token);
        $usuario->save();

        return response()->json([
            'ok' => true,
            'token' => $token,
            'nombre' => $usuario->candidato->nombre,
            'apellido' => $usuario->candidato->apellido,
        ]);
    }

    // Esta función identifica al candidato por el token de la app
    // En terminos tecnicos, cuando una ruta de la app pide datos, se ejecuta esta función
    // y busca el usuario cuyo api_token coincide con el encabezado Authorization
    private function candidatoDesdeToken(Request $request): Candidato
    {
        $plano = (string) $request->bearerToken();
        $usuario = $plano === ''
            ? null
            : User::query()->where('api_token', hash('sha256', $plano))->first();

        if (! $usuario || ! $usuario->esCandidato() || ! $usuario->candidato) {
            abort(response()->json([
                'ok' => false,
                'mensaje' => 'Tenés que iniciar sesión.',
            ], 401));
        }

        return $usuario->candidato;
    }
}
