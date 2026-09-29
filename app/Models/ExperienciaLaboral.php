<?php

namespace App\Models;

use Illuminate\Support\Carbon;

class ExperienciaLaboral
{
    public string $id = '';

    public string $empresa = '';

    public ?string $puesto = null;

    public ?string $fecha_desde = null;

    public ?string $fecha_hasta = null;

    public ?string $descripcion = null;

    public array $habilidades = [];

    // Esta función arma una experiencia a partir del ítem guardado en Mongo
    // En terminos tecnicos, cuando el perfil o la ficha listan experiencias, se ejecuta esta función
    // y copia los campos del array embebido
    public static function desde(array $datos): self
    {
        $experiencia = new self();
        $experiencia->id = (string) ($datos['experiencia_id'] ?? '');
        $experiencia->empresa = (string) ($datos['empresa'] ?? '');
        $experiencia->puesto = self::texto($datos['puesto'] ?? null);
        $experiencia->fecha_desde = self::fecha($datos['fecha_desde'] ?? null);
        $experiencia->fecha_hasta = self::fecha($datos['fecha_hasta'] ?? null);
        $experiencia->descripcion = self::texto($datos['descripcion'] ?? null);
        $experiencia->habilidades = array_values($datos['habilidades'] ?? []);

        return $experiencia;
    }

    // Esta función arma el texto del período
    // En terminos tecnicos, cuando el perfil o el detalle muestran una experiencia, se ejecuta esta función
    // y devuelve las fechas en formato mes/año, o vacío si no hay fechas
    public function periodo(): string
    {
        $desde = $this->fecha_desde ? Carbon::parse($this->fecha_desde)->format('m/Y') : null;
        $hasta = $this->fecha_hasta ? Carbon::parse($this->fecha_hasta)->format('m/Y') : null;

        if ($desde && $hasta) {
            return $desde.' – '.$hasta;
        }

        if ($desde) {
            return $desde.' – Actualidad';
        }

        if ($hasta) {
            return 'Hasta '.$hasta;
        }

        return '';
    }

    // Esta función deja un texto o null si viene vacío
    // En terminos tecnicos, cuando se arma una experiencia, se ejecuta esta función
    // y descarta puestos o descripciones en blanco
    private static function texto(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    // Esta función deja la fecha como AAAA-MM-DD
    // En terminos tecnicos, cuando se arma una experiencia, se ejecuta esta función
    // y convierte el valor guardado a texto de fecha, o null si no hay
    private static function fecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        return Carbon::parse((string) $valor)->format('Y-m-d');
    }
}
