<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalRrhh extends Model
{
    protected $table = 'personal_rrhh';

    public $timestamps = false;

    protected $fillable = [
        'usuarios_id',
        'nombre',
        'apellido',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuarios_id');
    }

    // Esta función trae las franjas semanales del reclutador
    // En terminos tecnicos, cuando el calendario arma los huecos, se ejecuta esta función
    // y busca las filas de disponibilidades de este personal de RRHH
    public function disponibilidades(): HasMany
    {
        return $this->hasMany(Disponibilidad::class, 'personal_rrhh_id');
    }

    // Esta función trae los días u horarios marcados como no disponibles
    // En terminos tecnicos, cuando el calendario resta excepciones, se ejecuta esta función
    // y busca las filas de bloqueos de este personal de RRHH
    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class, 'personal_rrhh_id');
    }
}
