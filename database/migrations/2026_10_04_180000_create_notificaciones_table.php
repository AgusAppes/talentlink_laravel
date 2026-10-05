<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuarios_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('texto');
            $table->boolean('leida')->default(false);
            $table->string('clave')->nullable();
            $table->timestamps();

            $table->unique(['usuarios_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
