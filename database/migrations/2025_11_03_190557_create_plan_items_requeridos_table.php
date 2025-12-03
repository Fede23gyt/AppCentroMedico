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
        Schema::create('plan_items_requeridos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('planes')->onDelete('cascade');
            $table->string('item_codigo', 50); // Ejemplo: "NPIEVE", "NIVELX7"
            $table->boolean('es_obligatorio')->default(true);
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['plan_id', 'item_codigo']);
            $table->unique(['plan_id', 'item_codigo']); // Un item no puede repetirse en el mismo plan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_items_requeridos');
    }
};
