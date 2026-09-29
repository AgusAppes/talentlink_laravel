<?php

namespace App\Models;

use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

class PerfilDocumento extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'perfiles';

    public $timestamps = false;

    protected $fillable = [
        'candidato_id',
        'habilidades',
        'experiencias',
    ];

    protected $casts = [
        'candidato_id' => 'int',
        'habilidades' => 'array',
        'experiencias' => 'array',
    ];

    // Esta función trae el documento del candidato, y lo crea vacío si todavía no existe
    // En terminos tecnicos, cuando se guarda el perfil o una experiencia, se ejecuta esta función
    // y hace firstOrCreate en la colección perfiles
    public static function deCandidato(int $candidatoId): self
    {
        return static::query()->firstOrCreate(
            ['candidato_id' => $candidatoId],
            ['habilidades' => [], 'experiencias' => []]
        );
    }

    // Esta función reemplaza las habilidades generales del candidato
    // En terminos tecnicos, cuando se envía el formulario de /mi-perfil, se ejecuta esta función
    // y actualiza el array habilidades del documento
    public function guardarHabilidades(array $nombres): void
    {
        $this->habilidades = SolicitudDocumento::textos($nombres);
        $this->save();
    }

    // Esta función agrega una experiencia laboral con sus habilidades
    // En terminos tecnicos, cuando se envía el formulario de experiencia, se ejecuta esta función
    // y suma un ítem al array experiencias del documento
    public function agregarExperiencia(array $datos): void
    {
        $experiencias = $this->listaExperiencias();
        $experiencias[] = [
            'experiencia_id' => (string) Str::uuid(),
            'empresa' => $datos['empresa'],
            'puesto' => $datos['puesto'] ?? null,
            'fecha_desde' => $datos['fecha_desde'] ?? null,
            'fecha_hasta' => $datos['fecha_hasta'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'habilidades' => SolicitudDocumento::textos($datos['habilidades'] ?? []),
        ];

        $this->experiencias = $experiencias;
        $this->save();
    }

    // Esta función saca una experiencia del perfil
    // En terminos tecnicos, cuando el candidato envía eliminar, se ejecuta esta función
    // y deja el array experiencias sin ese experiencia_id
    public function quitarExperiencia(string $id): bool
    {
        $experiencias = $this->listaExperiencias();
        $quedan = array_values(array_filter(
            $experiencias,
            fn (array $item) => (string) ($item['experiencia_id'] ?? '') !== $id
        ));

        if (count($quedan) === count($experiencias)) {
            return false;
        }

        $this->experiencias = $quedan;
        $this->save();

        return true;
    }

    // Esta función devuelve las experiencias como lista de arreglos
    // En terminos tecnicos, cuando se agrega o se borra una experiencia, se ejecuta esta función
    // y normaliza el array guardado en el documento
    private function listaExperiencias(): array
    {
        return collect($this->experiencias ?? [])
            ->map(fn ($item) => (array) $item)
            ->values()
            ->all();
    }
}
