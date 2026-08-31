<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'nombre' => 'admin'],
            ['id' => 2, 'nombre' => 'empresa'],
            ['id' => 3, 'nombre' => 'candidato'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['id' => $rol['id']],
                ['nombre' => $rol['nombre']]
            );
        }
    }
}