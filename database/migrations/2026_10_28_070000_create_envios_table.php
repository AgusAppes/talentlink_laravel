<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulaciones_id')->unique()->constrained('postulaciones')->cascadeOnDelete();
            $table->foreignId('personal_rrhh_id')->constrained('personal_rrhh');
            $table->string('telefono', 40)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
