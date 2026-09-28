<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidato extends Model
{
    protected $table = 'candidatos';

    public $timestamps = false;

    protected $fillable = [
        'usuarios_id',
        'nombre',
        'apellido',
        'fecha_nac',
        'ciudades_id',
        'descripcion',
        'foto',
    ];

    // Esta función convierte el campo fecha_nac a un objeto DateTime
    // Esto es para que se pueda usar en el formulario de edición de perfil
    // En palabras simples, recibimos el campo fecha_nac de la base de datos como texto y lo convertimos a un objeto Date de Laravel
    protected $casts = [
        'fecha_nac' => 'date',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuarios_id');
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudades_id');
    }

    // Esta función trae las postulaciones del candidato
    // En terminos tecnicos, cuando el listado o el feed buscan sus postulaciones, se ejecuta esta función
    // y usa la tabla postulaciones_por_candidatos
    // Esta función trae las habilidades del candidato
    // En terminos tecnicos, cuando el perfil muestra los tags, se ejecuta esta función
    // y usa la tabla habilidades_candidatos
    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Habilidad::class,
            'habilidades_candidatos',
            'candidatos_id',
            'habilidades_id'
        );
    }

    // Esta función trae las experiencias del candidato
    // En terminos tecnicos, cuando el perfil lista la experiencia laboral, se ejecuta esta función
    // y las ordena por fecha de inicio, de la más reciente a la más vieja
    public function experiencias(): HasMany
    {
        return $this->hasMany(Experiencia::class, 'candidatos_id')->orderByDesc('fecha_desde');
    }

    public function postulaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Postulacion::class,
            'postulaciones_por_candidatos',
            'candidatos_id',
            'postulaciones_id'
        );
    }
}
