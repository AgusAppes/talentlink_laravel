<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
        });

        Schema::create('permisos_por_roles', function (Blueprint $table) {
            $table->foreignId('permisos_id')->constrained('permisos')->cascadeOnDelete();
            $table->foreignId('roles_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['permisos_id', 'roles_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_por_roles');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
    }
};