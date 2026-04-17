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
        Schema::create('precios_sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('prestacion_id')->constrained('prestaciones');
            $table->decimal('precio_1', 12, 2)->default(0);
            $table->decimal('precio_2', 12, 2)->default(0);
            $table->decimal('precio_3', 12, 2)->default(0);
            $table->decimal('precio_4', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['sucursal_id', 'prestacion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precios_sucursales');
    }
};
