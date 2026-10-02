<?php

namespace App\Experto;

use App\Models\Candidato;
use App\Models\ExperienciaLaboral;
use App\Models\Postulacion;
use App\Models\SolicitudDocumento;

class EvaluadorCompatibilidad
{
    // Esta función deja listos el motor de reglas y el lector de CV
    // En terminos tecnicos, cuando un controlador pide evaluar una postulación, se ejecuta esta función
    // y Laravel inyecta las dos piezas
    public function __construct(
        private MotorCompatibilidad $motor,
        private LectorCv $lector,
    ) {}

    // Esta función calcula la compatibilidad y la guarda en la postulación
    // En terminos tecnicos, cuando el candidato se postula o el reclutador pide calcular, se ejecuta esta función
    // y actualiza el porcentaje y el detalle de reglas
    public function guardar(Postulacion $postulacion, Candidato $candidato): bool
    {
        try {
            $resultado = $this->evaluar($postulacion, $candidato);

            $postulacion->update([
                'compatibilidad' => $resultado->porcentaje,
                'compatibilidad_detalle' => $resultado->aArray(),
            ]);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    // Esta función junta los datos del candidato, de la solicitud y del CV
    // En terminos tecnicos, cuando se va a inferir el porcentaje, se ejecuta esta función
    // y lee Mongo, el perfil y, si hay PDF, su texto
    private function evaluar(Postulacion $postulacion, Candidato $candidato): ResultadoCompatibilidad
    {
        $busquedaId = $postulacion->oferta()->first()?->busquedas_id;
        $ficha = $busquedaId
            ? SolicitudDocumento::query()->where('busqueda_id', (int) $busquedaId)->first()
            : null;

        $textoCv = '';
        $estadoCv = null;

        if ($postulacion->cv) {
            $textoCv = $this->lector->texto($postulacion->cv);
            $estadoCv = $textoCv !== '' ? 'leido' : 'no_leido';
        }

        $experiencias = $candidato->experiencias
            ->map(fn (ExperienciaLaboral $experiencia) => [
                'desde' => $experiencia->fecha_desde,
                'hasta' => $experiencia->fecha_hasta,
                'habilidades' => $experiencia->habilidades,
            ])
            ->all();

        return $this->motor->inferir(
            habilidadesRequeridas: $ficha?->habilidades ?? [],
            habilidadesPerfil: $candidato->habilidades->all(),
            experiencias: $experiencias,
            aniosRequeridos: $ficha?->anios_experiencia,
            modalidadId: $ficha?->modalidades_id,
            ciudadOferta: $ficha?->ciudades_id,
            ciudadCandidato: $candidato->ciudades_id ? (int) $candidato->ciudades_id : null,
            textoCv: $textoCv,
            estadoCv: $estadoCv,
        );
    }
}
