<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
        });

        Schema::create('provincias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('paises_id')->constrained('paises');
            $table->unique(['nombre', 'paises_id']);
        });

        Schema::create('ciudades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('provincias_id')->constrained('provincias');
            $table->unique(['nombre', 'provincias_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades');
        Schema::dropIfExists('provincias');
        Schema::dropIfExists('paises');
    }
};