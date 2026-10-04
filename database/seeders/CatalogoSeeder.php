<?php

namespace Database\Seeders;

use App\Models\SolicitudDocumento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedCatalogos();
        });

        $this->seedBusquedasDemo();
    }

    private function seedCatalogos(): void
    {
        $this->insertarCatalogo('estado_busqueda', [
            [1, 'Pendiente'],
            [2, 'En oferta'],
            [3, 'Cerrada'],
        ]);

        $this->insertarCatalogo('estado_ofertas', [
            [1, 'Publicada'],
            [2, 'En pausa'],
            [3, 'Finalizada'],
        ]);

        $this->insertarCatalogo('modalidades', [
            [1, 'Presencial'],
            [2, 'Remoto'],
            [3, 'Híbrida'],
        ]);

        $this->insertarCatalogo('etapas', [
            [1, 'Pendiente de revisión'],
            [2, 'En revisión'],
            [3, 'Rechazada'],
            [4, 'Aprobada'],
            [5, 'En entrevista'],
            [6, 'Entrevista cancelada'],
        ]);
    }

    private function insertarCatalogo(string $tabla, array $filas): void
    {
        foreach ($filas as [$id, $nombre]) {
            DB::table($tabla)->updateOrInsert(
                ['id' => $id],
                ['nombre' => $nombre]
            );
        }
    }

    private function seedBusquedasDemo(): void
    {
        $empresasDemo = [
            'Aurora Tech' => $this->empresaId('Aurora Tech'),
            'Mate y Code' => $this->empresaId('Mate y Code'),
            'Pampa Foods' => $this->empresaId('Pampa Foods'),
            'Andes Cargo' => $this->empresaId('Andes Cargo'),
            'Río Plata SA' => $this->empresaId('Río Plata SA'),
        ];

        foreach ($empresasDemo as $nombre => $id) {
            if ($id === null) {
                throw new \RuntimeException("Falta empresa demo: {$nombre}. Ejecutá DemoSeeder antes.");
            }
        }

        $geo = DB::table('ciudades as c')
            ->join('provincias as p', 'p.id', '=', 'c.provincias_id')
            ->where('c.nombre', 'like', '%Posadas%')
            ->select('c.id as ciudad_id', 'c.provincias_id', 'p.paises_id')
            ->first();

        if ($geo === null) {
            $geo = DB::table('ciudades as c')
                ->join('provincias as p', 'p.id', '=', 'c.provincias_id')
                ->orderBy('c.id')
                ->select('c.id as ciudad_id', 'c.provincias_id', 'p.paises_id')
                ->first();
        }

        if ($geo === null) {
            throw new \RuntimeException('Faltan ciudades. Ejecutá GeoSeeder antes.');
        }

        $busquedasDemo = [
            [
                'nombre_puesto' => 'Desarrollador Full Stack',
                'empresa' => 'Aurora Tech',
                'descripcion' => 'Desarrollo y mantenimiento de aplicaciones web con PHP y MySQL.',
                'cantidad_vacantes' => 2,
                'anios_experiencia' => 2,
                'modalidades_id' => 3,
                'habilidades' => ['PHP', 'MySQL', 'JavaScript'],
            ],
            [
                'nombre_puesto' => 'Diseñador UX/UI',
                'empresa' => 'Aurora Tech',
                'descripcion' => 'Diseño de interfaces y prototipos para productos digitales.',
                'cantidad_vacantes' => 1,
                'anios_experiencia' => 1,
                'modalidades_id' => 2,
                'habilidades' => ['Figma', 'HTML', 'CSS'],
            ],
            [
                'nombre_puesto' => 'Analista de Sistemas',
                'empresa' => 'Mate y Code',
                'descripcion' => 'Relevamiento de requerimientos y documentación funcional.',
                'cantidad_vacantes' => 1,
                'anios_experiencia' => 1,
                'modalidades_id' => 2,
                'habilidades' => ['Análisis funcional', 'UML'],
            ],
            [
                'nombre_puesto' => 'Especialista DevOps',
                'empresa' => 'Mate y Code',
                'descripcion' => 'Automatización de despliegues y monitoreo de infraestructura.',
                'cantidad_vacantes' => 1,
                'anios_experiencia' => 3,
                'modalidades_id' => 3,
                'habilidades' => ['Linux', 'Docker', 'CI/CD'],
            ],
            [
                'nombre_puesto' => 'Administrativo de RRHH',
                'empresa' => 'Pampa Foods',
                'descripcion' => 'Gestión de legajos, nómina y apoyo en procesos de selección.',
                'cantidad_vacantes' => 1,
                'anios_experiencia' => 0,
                'modalidades_id' => 1,
                'habilidades' => ['Excel', 'Atención al cliente'],
            ],
            [
                'nombre_puesto' => 'Operario de planta',
                'empresa' => 'Pampa Foods',
                'descripcion' => 'Tareas de producción y control de calidad en línea de elaboración.',
                'cantidad_vacantes' => 4,
                'anios_experiencia' => 0,
                'modalidades_id' => 1,
                'habilidades' => ['Trabajo en equipo'],
            ],
            [
                'nombre_puesto' => 'Chofer de reparto',
                'empresa' => 'Andes Cargo',
                'descripcion' => 'Distribución de mercadería en zona urbana y periférica.',
                'cantidad_vacantes' => 3,
                'anios_experiencia' => 1,
                'modalidades_id' => 1,
                'habilidades' => ['Licencia de conducir'],
            ],
            [
                'nombre_puesto' => 'Despachante de aduana',
                'empresa' => 'Andes Cargo',
                'descripcion' => 'Tramitación de importaciones y exportaciones.',
                'cantidad_vacantes' => 1,
                'anios_experiencia' => 2,
                'modalidades_id' => 1,
                'habilidades' => ['Comercio exterior', 'Documentación'],
            ],
            [
                'nombre_puesto' => 'Contador junior',
                'empresa' => 'Río Plata SA',
                'descripcion' => 'Registros contables, balances y liquidaciones impositivas.',
                'cantidad_vacantes' => 1,
                'anios_experiencia' => 1,
                'modalidades_id' => 3,
                'habilidades' => ['Contabilidad', 'Excel'],
            ],
            [
                'nombre_puesto' => 'Asistente comercial',
                'empresa' => 'Río Plata SA',
                'descripcion' => 'Seguimiento de clientes, presupuestos y apoyo al equipo de ventas.',
                'cantidad_vacantes' => 2,
                'anios_experiencia' => 0,
                'modalidades_id' => 2,
                'habilidades' => ['Ventas', 'Comunicación'],
            ],
        ];

        $puestos = array_column($busquedasDemo, 'nombre_puesto');

        $busquedaIds = DB::table('busquedas')->whereIn('nombre_puesto', $puestos)->pluck('id');
        if ($busquedaIds->isNotEmpty()) {
            SolicitudDocumento::query()
                ->whereIn('busqueda_id', $busquedaIds->map(fn ($id) => (int) $id)->all())
                ->delete();
            DB::table('busquedas')->whereIn('id', $busquedaIds)->delete();
        }

        foreach ($busquedasDemo as $demo) {
            $busquedaId = DB::table('busquedas')->insertGetId([
                'nombre_puesto' => $demo['nombre_puesto'],
                'empresas_id' => $empresasDemo[$demo['empresa']],
                'estado_busqueda_id' => 1,
            ]);

            SolicitudDocumento::guardar((int) $busquedaId, [
                'descripcion' => $demo['descripcion'],
                'cantidad_vacantes' => $demo['cantidad_vacantes'],
                'anios_experiencia' => $demo['anios_experiencia'],
                'modalidades_id' => $demo['modalidades_id'],
                'ciudades_id' => $geo->ciudad_id,
                'provincias_id' => $geo->provincias_id,
                'paises_id' => $geo->paises_id,
                'habilidades' => $demo['habilidades'],
            ]);
        }
    }

    private function empresaId(string $nombre): ?int
    {
        $id = DB::table('empresas')->where('nombre', $nombre)->value('id');

        return $id !== null ? (int) $id : null;
    }
}