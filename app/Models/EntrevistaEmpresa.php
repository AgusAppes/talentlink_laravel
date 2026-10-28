<?php

namespace App\Models;

use App\Http\Controllers\CalendarioController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class EntrevistaEmpresa extends Model
{
    protected $table = 'entrevistas_empresa';

    protected $fillable = [
        'envios_id',
        'inicio',
        'nota',
    ];

    protected $casts = [
        'inicio' => 'datetime',
    ];

    // Esta función trae el envío de la entrevista
    // En terminos tecnicos, cuando la empresa propone un horario, se ejecuta esta función
    // y busca la fila de envios con el envios_id
    public function envio(): BelongsTo
    {
        return $this->belongsTo(Envio::class, 'envios_id');
    }

    // Esta función pasa el inicio a la hora de Argentina
    // En terminos tecnicos, cuando la ficha muestra el horario propuesto, se ejecuta esta función
    // y convierte el datetime guardado a America/Argentina/Buenos_Aires
    public function inicioLocal(): ?Carbon
    {
        return $this->inicio?->copy()->timezone(CalendarioController::ZONA);
    }
}
