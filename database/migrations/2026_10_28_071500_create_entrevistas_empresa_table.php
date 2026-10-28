<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrevistas_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envios_id')->unique()->constrained('envios')->cascadeOnDelete();
            $table->dateTime('inicio');
            $table->text('nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrevistas_empresa');
    }
};
