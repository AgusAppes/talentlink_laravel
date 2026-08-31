<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = (int) DB::table('roles')->where('nombre', 'admin')->value('id');
        $rolEmpresa = (int) DB::table('roles')->where('nombre', 'empresa')->value('id');
        $rolCandidato = (int) DB::table('roles')->where('nombre', 'candidato')->value('id');

        if ($rolAdmin === 0 || $rolEmpresa === 0 || $rolCandidato === 0) {
            throw new \RuntimeException('Faltan roles. Ejecutá RoleSeeder antes.');
        }

        $ciudadId = DB::table('ciudades')->orderBy('id')->value('id');
        if ($ciudadId === null) {
            throw new \RuntimeException('No hay ciudades. Ejecutá GeoSeeder antes.');
        }

        $rrhhCorreos = [
            'rrhh.ana.gomez@talentlink.com',
            'rrhh.bruno.lopez@talentlink.com',
            'rrhh.carla.martinez@talentlink.com',
        ];

        $empresasCorreos = [
            'talento@auroratech.com',
            'contacto@mateycode.com',
            'rrhh@pampafoods.com',
            'jobs@andescargo.com',
            'empleos@rioplata.com',
        ];

        $candidatosCorreos = [
            'luna.perez@mail.com',
            'tomas.rojas@mail.com',
            'maria.garcia@mail.com',
            'juan.sosa@mail.com',
            'sofia.diaz@mail.com',
        ];

        $correos = array_merge($rrhhCorreos, $empresasCorreos, $candidatosCorreos);
        $password = Hash::make('12345678');

        DB::transaction(function () use (
            $correos,
            $password,
            $rolAdmin,
            $rolEmpresa,
            $rolCandidato,
            $ciudadId,
            $rrhhCorreos,
            $empresasCorreos,
            $candidatosCorreos
        ) {
            $this->limpiarDemo($correos);

            $nombresEmpresas = ['Aurora Tech', 'Mate y Code', 'Pampa Foods', 'Andes Cargo', 'Río Plata SA'];
            foreach ($empresasCorreos as $idx => $correo) {
                $usuarioId = DB::table('usuarios')->insertGetId([
                    'roles_id' => $rolEmpresa,
                    'correo' => $correo,
                    'password' => $password,
                ]);
                DB::table('empresas')->insert([
                    'nombre' => $nombresEmpresas[$idx],
                    'usuarios_id' => $usuarioId,
                ]);
            }

            $nombresCand = ['Luna', 'Tomás', 'María', 'Juan', 'Sofía'];
            $apellidosCand = ['Pérez', 'Rojas', 'García', 'Sosa', 'Díaz'];
            $fechasNac = [
                '1990-01-15 00:00:00',
                '1990-02-15 00:00:00',
                '1990-03-15 00:00:00',
                '1990-04-15 00:00:00',
                '1990-05-15 00:00:00',
            ];

            foreach ($candidatosCorreos as $idx => $correo) {
                $usuarioId = DB::table('usuarios')->insertGetId([
                    'roles_id' => $rolCandidato,
                    'correo' => $correo,
                    'password' => $password,
                ]);
                DB::table('candidatos')->insert([
                    'usuarios_id' => $usuarioId,
                    'nombre' => $nombresCand[$idx],
                    'apellido' => $apellidosCand[$idx],
                    'fecha_nac' => $fechasNac[$idx],
                    'ciudades_id' => $ciudadId,
                ]);
            }

            $nombresRrhh = ['Ana', 'Bruno', 'Carla'];
            $apellidosRrhh = ['Gomez', 'Lopez', 'Martinez'];

            foreach ($rrhhCorreos as $idx => $correo) {
                $usuarioId = DB::table('usuarios')->insertGetId([
                    'roles_id' => $rolAdmin,
                    'correo' => $correo,
                    'password' => $password,
                ]);
                DB::table('personal_rrhh')->insert([
                    'usuarios_id' => $usuarioId,
                    'nombre' => $nombresRrhh[$idx],
                    'apellido' => $apellidosRrhh[$idx],
                ]);
            }
        });
    }

    private function limpiarDemo(array $correos): void
    {
        $usuarioIds = DB::table('usuarios')->whereIn('correo', $correos)->pluck('id');
        if ($usuarioIds->isEmpty()) {
            return;
        }

        $empresaIds = DB::table('empresas')->whereIn('usuarios_id', $usuarioIds)->pluck('id');

        if ($empresaIds->isNotEmpty()) {
            $busquedaIds = DB::table('busquedas')->whereIn('empresas_id', $empresaIds)->pluck('id');

            if ($busquedaIds->isNotEmpty()) {
                $ofertaIds = DB::table('ofertas')->whereIn('busquedas_id', $busquedaIds)->pluck('id');

                if ($ofertaIds->isNotEmpty()) {
                    $postulacionIds = DB::table('postulaciones')->whereIn('ofertas_id', $ofertaIds)->pluck('id');

                    if ($postulacionIds->isNotEmpty()) {
                        DB::table('postulaciones_por_candidatos')->whereIn('postulaciones_id', $postulacionIds)->delete();
                        DB::table('postulaciones')->whereIn('id', $postulacionIds)->delete();
                    }

                    DB::table('ofertas')->whereIn('id', $ofertaIds)->delete();
                }

                DB::table('habilidades_por_busqueda')->whereIn('busquedas_id', $busquedaIds)->delete();
                DB::table('detalle_busquedas')->whereIn('busquedas_id', $busquedaIds)->delete();
                DB::table('busquedas')->whereIn('id', $busquedaIds)->delete();
            }
        }

        DB::table('candidatos')->whereIn('usuarios_id', $usuarioIds)->delete();
        DB::table('empresas')->whereIn('usuarios_id', $usuarioIds)->delete();
        DB::table('personal_rrhh')->whereIn('usuarios_id', $usuarioIds)->delete();
        DB::table('usuarios')->whereIn('id', $usuarioIds)->delete();
    }
}