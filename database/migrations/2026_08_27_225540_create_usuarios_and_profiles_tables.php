<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roles_id')->constrained('roles');
            $table->string('correo', 100)->unique();
            $table->string('password');
        });

        Schema::create('personal_rrhh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuarios_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
        });

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('usuarios_id')->unique()->constrained('usuarios')->cascadeOnDelete();
        });

        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuarios_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->dateTime('fecha_nac')->nullable();
            $table->foreignId('ciudades_id')->nullable()->constrained('ciudades');
        });

        Schema::create('habilidades_candidatos', function (Blueprint $table) {
            $table->foreignId('candidatos_id')->constrained('candidatos')->cascadeOnDelete();
            $table->foreignId('habilidades_id')->constrained('habilidades')->cascadeOnDelete();
            $table->primary(['candidatos_id', 'habilidades_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habilidades_candidatos');
        Schema::dropIfExists('candidatos');
        Schema::dropIfExists('empresas');
        Schema::dropIfExists('personal_rrhh');
        Schema::dropIfExists('usuarios');
    }
};