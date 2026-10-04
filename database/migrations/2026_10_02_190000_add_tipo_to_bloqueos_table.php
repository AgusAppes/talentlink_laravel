<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bloqueos', function (Blueprint $table) {
            $table->string('tipo', 20)->default('no_disponible');
        });
    }

    public function down(): void
    {
        Schema::table('bloqueos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
