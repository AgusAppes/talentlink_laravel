<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
