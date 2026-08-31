<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('busquedas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_puesto', 100);
            $table->foreignId('empresas_id')->constrained('empresas');
            $table->foreignId('estado_busqueda_id')->constrained('estado_busqueda');
        });

        Schema::create('detalle_busquedas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('busquedas_id')->unique()->constrained('busquedas')->cascadeOnDelete();
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('cantidad_vacantes')->nullable();
            $table->unsignedSmallInteger('anios_experiencia')->nullable();
            $table->foreignId('modalidades_id')->nullable()->constrained('modalidades');
            $table->foreignId('ciudades_id')->nullable()->constrained('ciudades');
            $table->foreignId('provincias_id')->nullable()->constrained('provincias');
            $table->foreignId('paises_id')->nullable()->constrained('paises');
        });

        Schema::create('habilidades_por_busqueda', function (Blueprint $table) {
            $table->foreignId('busquedas_id')->constrained('busquedas')->cascadeOnDelete();
            $table->foreignId('habilidades_id')->constrained('habilidades')->cascadeOnDelete();
            $table->primary(['busquedas_id', 'habilidades_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habilidades_por_busqueda');
        Schema::dropIfExists('detalle_busquedas');
        Schema::dropIfExists('busquedas');
    }
};