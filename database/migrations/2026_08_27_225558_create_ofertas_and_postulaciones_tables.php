<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('busquedas_id')->unique()->constrained('busquedas');
            $table->foreignId('personal_rrhh_id')->constrained('personal_rrhh');
            $table->foreignId('estado_ofertas_id')->constrained('estado_ofertas');
        });

        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ofertas_id')->constrained('ofertas');
            $table->foreignId('etapas_id')->constrained('etapas');
        });

        Schema::create('postulaciones_por_candidatos', function (Blueprint $table) {
            $table->foreignId('postulaciones_id')->constrained('postulaciones')->cascadeOnDelete();
            $table->foreignId('candidatos_id')->constrained('candidatos')->cascadeOnDelete();
            $table->primary(['postulaciones_id', 'candidatos_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones_por_candidatos');
        Schema::dropIfExists('postulaciones');
        Schema::dropIfExists('ofertas');
    }
};