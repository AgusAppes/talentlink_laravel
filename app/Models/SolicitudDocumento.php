<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SolicitudDocumento extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'solicitudes';

    public $timestamps = false;

    protected $fillable = [
        'busqueda_id',
        'descripcion',
        'cantidad_vacantes',
        'anios_experiencia',
        'modalidades_id',
        'ciudades_id',
        'provincias_id',
        'paises_id',
        'habilidades',
    ];

    protected $casts = [
        'busqueda_id' => 'int',
        'cantidad_vacantes' => 'int',
        'anios_experiencia' => 'int',
        'modalidades_id' => 'int',
        'ciudades_id' => 'int',
        'provincias_id' => 'int',
        'paises_id' => 'int',
        'habilidades' => 'array',
    ];

    // Esta función deja una sola lista de habilidades, sin vacíos ni repetidos
    // En terminos tecnicos, cuando se guarda una solicitud o un perfil, se ejecuta esta función
    // y devuelve los textos recortados, únicos sin importar mayúsculas
    public static function textos(array $nombres): array
    {
        return collect($nombres)
            ->map(fn ($nombre) => trim((string) $nombre))
            ->filter(fn ($nombre) => $nombre !== '')
            ->unique(fn ($nombre) => mb_strtolower($nombre))
            ->values()
            ->all();
    }

    // Esta función guarda o reemplaza la ficha de una búsqueda
    // En terminos tecnicos, cuando se registra una solicitud, se ejecuta esta función
    // y hace upsert del documento solicitudes con ese busqueda_id
    public static function guardar(int $busquedaId, array $datos): self
    {
        return static::query()->updateOrCreate(
            ['busqueda_id' => $busquedaId],
            [
                'descripcion' => $datos['descripcion'] ?? null,
                'cantidad_vacantes' => $datos['cantidad_vacantes'] ?? null,
                'anios_experiencia' => $datos['anios_experiencia'] ?? null,
                'modalidades_id' => $datos['modalidades_id'] ?? null,
                'ciudades_id' => $datos['ciudades_id'] ?? null,
                'provincias_id' => $datos['provincias_id'] ?? null,
                'paises_id' => $datos['paises_id'] ?? null,
                'habilidades' => self::textos($datos['habilidades'] ?? []),
            ]
        );
    }
}
