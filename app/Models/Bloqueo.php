<?php

namespace App\Models;

use App\Http\Controllers\CalendarioController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Bloqueo extends Model
{
    protected $table = 'bloqueos';

    public $timestamps = false;

    protected $fillable = [
        'personal_rrhh_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'nota',
        'tipo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Esta función dice si el bloqueo tapa el día completo
    // En terminos tecnicos, cuando el calendario pinta un día, se ejecuta esta función
    // y mira si hora_inicio quedó vacía
    public function esDiaEntero(): bool
    {
        return $this->hora_inicio === null || $this->hora_fin === null;
    }

    // Esta función dice si el bloqueo ya terminó
    // En terminos tecnicos, cuando el calendario decide si muestra Quitar, se ejecuta esta función
    // y compara el fin del bloqueo con la hora actual de Argentina
    public function yaPaso(): bool
    {
        $fin = $this->esDiaEntero()
            ? Carbon::createFromFormat('Y-m-d', $this->fecha->format('Y-m-d'), CalendarioController::ZONA)->startOfDay()->addDay()
            : Carbon::createFromFormat('Y-m-d H:i', $this->fecha->format('Y-m-d').' '.substr($this->hora_fin, 0, 5), CalendarioController::ZONA);

        return $fin->lte(Carbon::now(CalendarioController::ZONA));
    }
}
