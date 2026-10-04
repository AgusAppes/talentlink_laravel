<?php

namespace App\Models;

use App\Http\Controllers\CalendarioController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Entrevista extends Model
{
    public const SOLICITADA = 'solicitada';

    public const CONFIRMADA = 'confirmada';

    public const CANCELADA = 'cancelada';

    protected $table = 'entrevistas';

    protected $fillable = [
        'postulaciones_id',
        'personal_rrhh_id',
        'candidatos_id',
        'inicio',
        'duracion_minutos',
        'estado',
        'cancelada_por',
        'motivo',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'duracion_minutos' => 'integer',
    ];

    // Esta función trae la postulación de la entrevista
    // En terminos tecnicos, cuando se cancela un turno, se ejecuta esta función
    // y busca la fila de postulaciones con el postulaciones_id
    public function postulacion(): BelongsTo
    {
        return $this->belongsTo(Postulacion::class, 'postulaciones_id');
    }

    // Esta función trae al reclutador de la entrevista
    // En terminos tecnicos, cuando el candidato elige un hueco, se ejecuta esta función
    // y busca la fila de personal_rrhh con el personal_rrhh_id
    public function personalRrhh(): BelongsTo
    {
        return $this->belongsTo(PersonalRrhh::class, 'personal_rrhh_id');
    }

    // Esta función trae al candidato de la entrevista
    // En terminos tecnicos, cuando el calendario arma el título, se ejecuta esta función
    // y busca la fila de candidatos con el candidatos_id
    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class, 'candidatos_id');
    }

    // Esta función dice si la entrevista sigue abierta
    // En terminos tecnicos, cuando la postulación decide si se puede pedir otra, se ejecuta esta función
    // y acepta solo solicitada o confirmada
    public function estaActiva(): bool
    {
        return in_array($this->estado, [self::SOLICITADA, self::CONFIRMADA], true);
    }

    // Esta función pasa el inicio a la hora de Argentina
    // En terminos tecnicos, cuando el calendario muestra el turno, se ejecuta esta función
    // y convierte el datetime guardado a America/Argentina/Buenos_Aires
    public function inicioLocal(): ?Carbon
    {
        return $this->inicio?->copy()->timezone(CalendarioController::ZONA);
    }

    // Esta función calcula la hora de fin en Argentina
    // En terminos tecnicos, cuando el calendario mide hasta dónde llega el turno, se ejecuta esta función
    // y suma duracion_minutos al inicio local
    public function finLocal(): ?Carbon
    {
        $inicio = $this->inicioLocal();

        if (! $inicio || ! $this->duracion_minutos) {
            return null;
        }

        return $inicio->copy()->addMinutes($this->duracion_minutos);
    }

    // Esta función arma el título que ve el reclutador
    // En terminos tecnicos, cuando se pinta un turno en su calendario, se ejecuta esta función
    // y junta el nombre del candidato con el puesto de la oferta
    public function titulo(): string
    {
        $nombre = trim(($this->candidato?->nombre ?? '').' '.($this->candidato?->apellido ?? ''));
        $puesto = $this->postulacion?->oferta?->busqueda?->nombre_puesto ?? '';

        return 'entrevista con '.$nombre.'-'.$puesto;
    }

    // Esta función arma el título que ve el candidato
    // En terminos tecnicos, cuando se pinta un turno en su calendario, se ejecuta esta función
    // y junta el nombre del reclutador con el puesto de la oferta
    public function tituloCandidato(): string
    {
        $nombre = trim(($this->personalRrhh?->nombre ?? '').' '.($this->personalRrhh?->apellido ?? ''));
        $puesto = $this->postulacion?->oferta?->busqueda?->nombre_puesto ?? '';

        return 'entrevista con '.$nombre.'-'.$puesto;
    }

    // Esta función arma el texto de una entrevista cancelada
    // En terminos tecnicos, cuando el calendario muestra un turno en gris, se ejecuta esta función
    // y dice quién canceló y el motivo
    public function textoCancelacion(): string
    {
        $quien = $this->cancelada_por === 'candidato' ? 'el candidato' : 'el reclutador';
        $texto = 'Cancelada por '.$quien;

        if ($this->motivo) {
            $texto .= ': '.$this->motivo;
        }

        return $texto;
    }
}
