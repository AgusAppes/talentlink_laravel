<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    // Esta función trae el documento de habilidades y experiencias, una sola vez por pedido
    // En terminos tecnicos, cuando el perfil o la ficha leen habilidades o experiencias, se ejecuta esta función
    // y busca el documento perfiles con el id de este candidato
    public function documentoPerfil(): ?PerfilDocumento
    {
        if (! $this->relationLoaded('documentoPerfil')) {
            $this->setRelation(
                'documentoPerfil',
                PerfilDocumento::query()->where('candidato_id', (int) $this->id)->first()
            );
        }

        return $this->getRelation('documentoPerfil');
    }

    // Esta función trae las habilidades generales del candidato
    // En terminos tecnicos, cuando el perfil muestra los tags, se ejecuta esta función
    // y lee el array habilidades del documento de Mongo
    protected function habilidades(): Attribute
    {
        return Attribute::get(fn () => collect($this->documentoPerfil()?->habilidades ?? []));
    }

    // Esta función trae las experiencias del candidato
    // En terminos tecnicos, cuando el perfil lista la experiencia laboral, se ejecuta esta función
    // y las ordena por fecha de inicio, de la más reciente a la más vieja
    protected function experiencias(): Attribute
    {
        return Attribute::get(function () {
            return collect($this->documentoPerfil()?->experiencias ?? [])
                ->map(fn ($item) => ExperienciaLaboral::desde((array) $item))
                ->sortByDesc(fn (ExperienciaLaboral $experiencia) => $experiencia->fecha_desde ?? '')
                ->values();
        });
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
