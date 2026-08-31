<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_busqueda', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
        });

        Schema::create('estado_ofertas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
        });

        Schema::create('modalidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
        });

        Schema::create('etapas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
        });

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
    }

    public function down(): void
    {
        Schema::dropIfExists('versiones_por_habilidades');
        Schema::dropIfExists('versiones');
        Schema::dropIfExists('habilidades');
        Schema::dropIfExists('etapas');
        Schema::dropIfExists('modalidades');
        Schema::dropIfExists('estado_ofertas');
        Schema::dropIfExists('estado_busqueda');
    }
};