<?php

namespace App\Console\Commands;

use App\Models\PerfilDocumento;
use App\Models\SolicitudDocumento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrarDocumentos extends Command
{
    protected $signature = 'talentlink:migrar-documentos';

    protected $description = 'Copia fichas de solicitudes y perfiles de candidatos a MongoDB';

    // Esta función copia el detalle relacional a las colecciones de Mongo
    // En terminos tecnicos, cuando se ejecuta php artisan talentlink:migrar-documentos,
    // lee MySQL y hace upsert de solicitudes y perfiles
    public function handle(): int
    {
        if (! Schema::hasTable('detalle_busquedas') || ! Schema::hasTable('experiencias')) {
            $this->warn('Las tablas relacionales ya no están. No hay nada para copiar.');

            return self::SUCCESS;
        }

        $solicitudes = $this->copiarSolicitudes();
        $perfiles = $this->copiarPerfiles();

        $this->info("Solicitudes copiadas: {$solicitudes}. Perfiles copiados: {$perfiles}.");

        return self::SUCCESS;
    }

    // Esta función copia cada búsqueda con su detalle y sus habilidades
    // En terminos tecnicos, cuando corre el comando, se ejecuta esta función
    // y hace upsert en la colección solicitudes
    private function copiarSolicitudes(): int
    {
        $detalles = DB::table('detalle_busquedas')->get()->keyBy(fn ($detalle) => (int) $detalle->busquedas_id);
        $habilidades = DB::table('habilidades_por_busqueda as hb')
            ->join('habilidades as h', 'h.id', '=', 'hb.habilidades_id')
            ->select('hb.busquedas_id', 'h.nombre')
            ->get()
            ->groupBy(fn ($fila) => (int) $fila->busquedas_id);

        $copiadas = 0;

        foreach (DB::table('busquedas')->pluck('id') as $busquedaId) {
            $busquedaId = (int) $busquedaId;
            $detalle = $detalles->get($busquedaId);
            $nombres = ($habilidades->get($busquedaId) ?? collect())->pluck('nombre')->all();

            SolicitudDocumento::guardar($busquedaId, [
                'descripcion' => $detalle->descripcion ?? null,
                'cantidad_vacantes' => $detalle->cantidad_vacantes ?? null,
                'anios_experiencia' => $detalle->anios_experiencia ?? null,
                'modalidades_id' => $detalle->modalidades_id ?? null,
                'ciudades_id' => $detalle->ciudades_id ?? null,
                'provincias_id' => $detalle->provincias_id ?? null,
                'paises_id' => $detalle->paises_id ?? null,
                'habilidades' => $nombres,
            ]);

            $copiadas++;
        }

        return $copiadas;
    }

    // Esta función copia habilidades generales y experiencias de cada candidato
    // En terminos tecnicos, cuando corre el comando, se ejecuta esta función
    // y hace upsert en la colección perfiles, con habilidades vacías en cada experiencia
    private function copiarPerfiles(): int
    {
        $habilidades = DB::table('habilidades_candidatos as hc')
            ->join('habilidades as h', 'h.id', '=', 'hc.habilidades_id')
            ->select('hc.candidatos_id', 'h.nombre')
            ->get()
            ->groupBy(fn ($fila) => (int) $fila->candidatos_id);

        $experiencias = DB::table('experiencias')->orderBy('id')->get()->groupBy(fn ($fila) => (int) $fila->candidatos_id);
        $copiados = 0;

        foreach (DB::table('candidatos')->pluck('id') as $candidatoId) {
            $candidatoId = (int) $candidatoId;
            $items = ($experiencias->get($candidatoId) ?? collect())->map(function ($experiencia) {
                return [
                    'experiencia_id' => (string) $experiencia->id,
                    'empresa' => $experiencia->empresa,
                    'puesto' => $experiencia->puesto,
                    'fecha_desde' => $experiencia->fecha_desde,
                    'fecha_hasta' => $experiencia->fecha_hasta,
                    'descripcion' => $experiencia->descripcion,
                    'habilidades' => [],
                ];
            })->values()->all();

            PerfilDocumento::query()->updateOrCreate(
                ['candidato_id' => $candidatoId],
                [
                    'habilidades' => ($habilidades->get($candidatoId) ?? collect())->pluck('nombre')->all(),
                    'experiencias' => $items,
                ]
            );

            $copiados++;
        }

        return $copiados;
    }
}
