<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mapeo_coberturas_precios', function (Blueprint $table) {
            $table->id();
            $table->string('cobertura_codigo')->unique(); // Ej: INI, SINSAL, PIE
            $table->integer('tipo_precio'); // 1, 2, 3 o 4
            $table->enum('rubro_tipo', ['Salud', 'Odonto'])->default('Salud');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapeo_coberturas_precios');
    }
};
