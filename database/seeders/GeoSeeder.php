<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GeoSeeder extends Seeder
{
    private const BASE_URL = 'https://apis.datos.gob.ar/georef/api/v2.0';

    private const PAIS_ID = 1;

    private const MAX_POR_PAGINA = 500;

    public function run(): void
    {
        DB::table('paises')->updateOrInsert(
            ['id' => self::PAIS_ID],
            ['nombre' => 'Argentina']
        );

        if (! $this->seedProvincias()) {
            throw new \RuntimeException('No se pudieron cargar provincias desde Georef.');
        }

        if (! $this->seedLocalidades()) {
            $this->command?->warn('API de localidades no respondió. Usando ciudades mínimas.');
            $this->insertarCiudadesMinimas();
        }
    }

    private function seedProvincias(): bool
    {
        $this->command?->info('Descargando provincias desde Georef...');

        $data = $this->georefGet('/provincias.json');
        if ($data === null || empty($data['provincias']) || ! is_array($data['provincias'])) {
            return false;
        }

        DB::transaction(function () use ($data) {
            foreach ($data['provincias'] as $provincia) {
                DB::table('provincias')->updateOrInsert(
                    [
                        'nombre' => $provincia['nombre'],
                        'paises_id' => self::PAIS_ID,
                    ],
                    [
                        'nombre' => $provincia['nombre'],
                        'paises_id' => self::PAIS_ID,
                    ]
                );
            }
        });

        $this->command?->info('Provincias cargadas: ' . count($data['provincias']));

        return true;
    }

    private function seedLocalidades(): bool
    {
        $this->command?->info('Descargando localidades (paginado)...');

        $inicio = 0;
        $total = null;
        $paginasOk = 0;

        $page = $this->georefGet('/localidades?inicio=0&max=' . self::MAX_POR_PAGINA);
        if ($page === null || empty($page['localidades'])) {
            return false;
        }

        while (true) {
            if ($total === null && isset($page['total'])) {
                $total = (int) $page['total'];
            }

            if (empty($page['localidades']) || ! is_array($page['localidades'])) {
                break;
            }

            DB::transaction(function () use ($page) {
                foreach ($page['localidades'] as $loc) {
                    if (empty($loc['provincia']['nombre'])) {
                        continue;
                    }

                    $provinciaId = DB::table('provincias')
                        ->where('nombre', $loc['provincia']['nombre'])
                        ->where('paises_id', self::PAIS_ID)
                        ->value('id');

                    if ($provinciaId === null) {
                        continue;
                    }

                    DB::table('ciudades')->updateOrInsert(
                        [
                            'nombre' => $loc['nombre'],
                            'provincias_id' => $provinciaId,
                        ],
                        [
                            'nombre' => $loc['nombre'],
                            'provincias_id' => $provinciaId,
                        ]
                    );
                }
            });

            $paginasOk++;
            $cant = count($page['localidades']);
            $inicio += $cant;

            $this->command?->line("  ... {$inicio}" . ($total !== null ? " / {$total}" : ''));

            if ($cant < self::MAX_POR_PAGINA) {
                break;
            }
            if ($total !== null && $inicio >= $total) {
                break;
            }

            $page = $this->georefGet('/localidades?inicio=' . $inicio . '&max=' . self::MAX_POR_PAGINA);
            if ($page === null) {
                $this->command?->warn("Falló una página intermedia; se conservó lo descargado hasta inicio={$inicio}.");
                break;
            }
        }

        return $paginasOk > 0;
    }

    private function georefGet(string $path): ?array
    {
        for ($intento = 1; $intento <= 2; $intento++) {
            try {
                $response = Http::timeout(25)
                    ->connectTimeout(10)
                    ->acceptJson()
                    ->get(self::BASE_URL . $path);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data)) {
                        return $data;
                    }
                }
            } catch (\Throwable) {
                // reintenta
            }

            if ($intento < 2) {
                sleep(1);
            }
        }

        return null;
    }

    private function insertarCiudadesMinimas(): void
    {
        $ciudades = [
            ['Posadas', 'Misiones'],
            ['Oberá', 'Misiones'],
            ['Córdoba', 'Córdoba'],
            ['Rosario', 'Santa Fe'],
            ['Mendoza', 'Mendoza'],
            ['La Plata', 'Buenos Aires'],
            ['Mar del Plata', 'Buenos Aires'],
            ['Buenos Aires', 'Ciudad Autónoma de Buenos Aires'],
        ];

        $insertadas = 0;

        foreach ($ciudades as [$nombreCiudad, $nombreProvincia]) {
            $provinciaId = DB::table('provincias')
                ->where('nombre', $nombreProvincia)
                ->where('paises_id', self::PAIS_ID)
                ->value('id');

            if ($provinciaId === null) {
                continue;
            }

            DB::table('ciudades')->updateOrInsert(
                ['nombre' => $nombreCiudad, 'provincias_id' => $provinciaId],
                ['nombre' => $nombreCiudad, 'provincias_id' => $provinciaId]
            );
            $insertadas++;
        }

        $this->command?->info("Ciudades mínimas cargadas: {$insertadas}");
    }
}