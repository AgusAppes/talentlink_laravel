<?php

namespace App\Experto;

class ResultadoCompatibilidad
{
    // Esta función guarda la conclusión del motor y su explicación
    // En terminos tecnicos, cuando el motor termina de aplicar las reglas, se ejecuta esta función
    // y arma el resultado que después se guarda en la postulación
    public function __construct(
        public bool $evaluable,
        public ?int $porcentaje,
        public array $reglas,
        public array $coincidencias,
        public array $faltantes,
    ) {}

    // Esta función pasa el resultado a un arreglo para guardarlo
    // En terminos tecnicos, cuando se actualiza la postulación, se ejecuta esta función
    // y devuelve el detalle que va a la columna compatibilidad_detalle
    public function aArray(): array
    {
        return [
            'evaluable' => $this->evaluable,
            'porcentaje' => $this->porcentaje,
            'reglas' => $this->reglas,
            'coincidencias' => $this->coincidencias,
            'faltantes' => $this->faltantes,
        ];
    }
}
