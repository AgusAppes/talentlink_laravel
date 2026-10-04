<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_rrhh', function (Blueprint $table) {
            $table->unsignedSmallInteger('duracion_entrevista')->default(30);
        });

        Schema::create('disponibilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_rrhh_id')->constrained('personal_rrhh')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
        });

        Schema::create('bloqueos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_rrhh_id')->constrained('personal_rrhh')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('nota', 200)->nullable();
        });

        Schema::create('entrevistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulaciones_id')->constrained('postulaciones')->cascadeOnDelete();
            $table->foreignId('personal_rrhh_id')->constrained('personal_rrhh');
            $table->foreignId('candidatos_id')->constrained('candidatos');
            $table->dateTime('inicio')->nullable();
            $table->unsignedSmallInteger('duracion_minutos')->nullable();
            $table->string('estado', 20);
            $table->string('cancelada_por', 20)->nullable();
            $table->string('motivo', 500)->nullable();
            $table->timestamps();
        });

        DB::table('etapas')->updateOrInsert(['id' => 5], ['nombre' => 'En entrevista']);
        DB::table('etapas')->updateOrInsert(['id' => 6], ['nombre' => 'Entrevista cancelada']);
    }

    public function down(): void
    {
        Schema::dropIfExists('entrevistas');
        Schema::dropIfExists('bloqueos');
        Schema::dropIfExists('disponibilidades');

        Schema::table('personal_rrhh', function (Blueprint $table) {
            $table->dropColumn('duracion_entrevista');
        });

        DB::table('etapas')->whereIn('id', [5, 6])->delete();
    }
};
