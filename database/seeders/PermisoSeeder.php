<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = (int) DB::table('roles')->where('nombre', 'admin')->value('id');
        $rolEmpresa = (int) DB::table('roles')->where('nombre', 'empresa')->value('id');
        $rolCandidato = (int) DB::table('roles')->where('nombre', 'candidato')->value('id');

        if ($rolAdmin === 0 || $rolEmpresa === 0 || $rolCandidato === 0) {
            throw new \RuntimeException('Faltan roles. Ejecutá RoleSeeder antes.');
        }

        $permisos = [
            'dashboard.ver',
            'solicitudes.ver',
            'solicitudes.crear',
            'solicitudes.editar',
            'solicitudes.eliminar',
            'candidatos.ver',
            'candidatos.editar',
            'empresas.ver',
            'empresas.editar',
            'ofertas.ver',
            'ofertas.crear',
            'postulaciones.ver',
            'postulaciones.crear',
            'postulaciones.gestionar',
            'reportes.ver',
            'usuarios.ver',
            'usuarios.administrar',
        ];

        $permisosAdmin = array_values(array_diff($permisos, ['solicitudes.crear']));

        $asignacion = [
            $rolAdmin => $permisosAdmin,
            $rolEmpresa => [
                'solicitudes.crear',
                'empresas.editar',
            ],
            $rolCandidato => [
                'candidatos.editar',
                'ofertas.ver',
                'postulaciones.ver',
                'postulaciones.crear',
            ],
        ];

        DB::transaction(function () use ($permisos, $asignacion) {
            DB::table('permisos_por_roles')->delete();
            DB::table('permisos')->delete();

            $idsPermisos = [];
            foreach ($permisos as $nombre) {
                $id = DB::table('permisos')->insertGetId(['nombre' => $nombre]);
                $idsPermisos[$nombre] = $id;
            }

            foreach ($asignacion as $rolId => $listaNombres) {
                foreach ($listaNombres as $nombre) {
                    if (! isset($idsPermisos[$nombre])) {
                        continue;
                    }
                    DB::table('permisos_por_roles')->insert([
                        'permisos_id' => $idsPermisos[$nombre],
                        'roles_id' => $rolId,
                    ]);
                }
            }
        });
    }
}