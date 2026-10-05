<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'usuarios_id',
        'texto',
        'leida',
        'clave',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    // Esta función deja un aviso para un usuario
    // En terminos tecnicos, cuando una entrevista, etapa o solicitud ya se guardó, se ejecuta esta función
    // y crea la fila, o no la repite si ya existe la misma clave
    public static function avisar(int $usuariosId, string $texto, ?string $clave = null): void
    {
        if ($clave) {
            self::query()->firstOrCreate(
                [
                    'usuarios_id' => $usuariosId,
                    'clave' => $clave,
                ],
                [
                    'texto' => $texto,
                ]
            );

            return;
        }

        self::query()->create([
            'usuarios_id' => $usuariosId,
            'texto' => $texto,
        ]);
    }

    // Esta función avisa si falta media hora para una entrevista
    // En terminos tecnicos, cuando se dibuja la barra del panel, se ejecuta esta función
    // y crea un aviso por cada entrevista confirmada que empieza dentro de los próximos 30 minutos
    public static function recordatorios(User $usuario): void
    {
        if ($usuario->esEmpresa()) {
            return;
        }

        $consulta = Entrevista::query()
            ->with('postulacion.oferta.busqueda')
            ->where('estado', Entrevista::CONFIRMADA)
            ->whereBetween('inicio', [now(), now()->addMinutes(30)]);

        if ($usuario->esCandidato()) {
            if (! $usuario->candidato) {
                return;
            }

            $consulta->where('candidatos_id', $usuario->candidato->id);
        } elseif ($usuario->esAdmin() && $usuario->personalRrhh) {
            $consulta->where('personal_rrhh_id', $usuario->personalRrhh->id);
        } else {
            return;
        }

        foreach ($consulta->get() as $entrevista) {
            $puesto = $entrevista->postulacion->oferta->busqueda->nombre_puesto;
            $cuando = $entrevista->inicioLocal()->format('d/m/Y H:i');

            self::avisar(
                $usuario->id,
                'Falta menos de media hora para tu entrevista de '.$puesto.' ('.$cuando.').',
                'recordatorio-'.$entrevista->id
            );
        }
    }
}
