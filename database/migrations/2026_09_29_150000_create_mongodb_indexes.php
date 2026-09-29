<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->table('solicitudes', function (Blueprint $collection) {
            $collection->unique('busqueda_id', 'busqueda_id_unique');
        });

        Schema::connection('mongodb')->table('perfiles', function (Blueprint $collection) {
            $collection->unique('candidato_id', 'candidato_id_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('solicitudes', function (Blueprint $collection) {
            $collection->dropIndexIfExists('busqueda_id_unique');
        });

        Schema::connection('mongodb')->table('perfiles', function (Blueprint $collection) {
            $collection->dropIndexIfExists('candidato_id_unique');
        });
    }
};
