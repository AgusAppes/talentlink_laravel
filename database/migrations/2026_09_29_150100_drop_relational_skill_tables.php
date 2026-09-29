<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('habilidades_por_busqueda');
        Schema::dropIfExists('habilidades_candidatos');
        Schema::dropIfExists('versiones_por_habilidades');
        Schema::dropIfExists('detalle_busquedas');
        Schema::dropIfExists('experiencias');
        Schema::dropIfExists('versiones');
        Schema::dropIfExists('habilidades');
    }

    public function down(): void
    {
        Schema::create('habilidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
        });

        Schema::create('versiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
        });

        Schema::create('versiones_por_habilidades', function (Blueprint $table) {
            $table->foreignId('habilidades_id')->constrained('habilidades')->cascadeOnDelete();
            $table->foreignId('versiones_id')->constrained('versiones')->cascadeOnDelete();
            $table->primary(['habilidades_id', 'versiones_id']);
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

        Schema::create('experiencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidatos_id')->constrained('candidatos')->cascadeOnDelete();
            $table->string('empresa', 100);
            $table->string('puesto', 100)->nullable();
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->text('descripcion')->nullable();
        });

        Schema::create('habilidades_candidatos', function (Blueprint $table) {
            $table->foreignId('candidatos_id')->constrained('candidatos')->cascadeOnDelete();
            $table->foreignId('habilidades_id')->constrained('habilidades')->cascadeOnDelete();
            $table->primary(['candidatos_id', 'habilidades_id']);
        });

        Schema::create('habilidades_por_busqueda', function (Blueprint $table) {
            $table->foreignId('busquedas_id')->constrained('busquedas')->cascadeOnDelete();
            $table->foreignId('habilidades_id')->constrained('habilidades')->cascadeOnDelete();
            $table->primary(['busquedas_id', 'habilidades_id']);
        });
    }
};
