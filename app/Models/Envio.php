<?php

namespace App\Models;

use App\Http\Controllers\CalendarioController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Envio extends Model
{
    protected $table = 'envios';

    protected $fillable = [
        'postulaciones_id',
        'personal_rrhh_id',
        'telefono',
        'descripcion',
        'comentario',
    ];

    // Esta función trae la postulación que se envió
    // En terminos tecnicos, cuando se muestra el envío, se ejecuta esta función
    // y busca la fila de postulaciones con el postulaciones_id
    public function postulacion(): BelongsTo
    {
        return $this->belongsTo(Postulacion::class, 'postulaciones_id');
    }

    // Esta función trae al reclutador que envió el candidato
    // En terminos tecnicos, cuando se guarda el envío, se ejecuta esta función
    // y busca la fila de personal_rrhh con el personal_rrhh_id
    public function personalRrhh(): BelongsTo
    {
        return $this->belongsTo(PersonalRrhh::class, 'personal_rrhh_id');
    }

    // Esta función escribe la fecha del envío en hora argentina
    // En terminos tecnicos, cuando la postulación muestra "Enviado el", se ejecuta esta función
    // y pasa created_at a America/Argentina/Buenos_Aires
    public function enviadoEl(): string
    {
        return $this->created_at->timezone(CalendarioController::ZONA)->format('d/m/Y H:i');
    }
}
