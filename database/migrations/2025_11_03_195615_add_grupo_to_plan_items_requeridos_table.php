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
        Schema::table('plan_items_requeridos', function (Blueprint $table) {
            // Primero eliminar el índice único existente
            $table->dropUnique(['plan_id', 'item_codigo']);

            // Agregar campos para grupos/combinaciones
            $table->string('grupo', 100)->default('default')->after('item_codigo')
                ->comment('Grupo/Combinación de items (ej: "combinacion_nueva", "combinacion_vieja")');

            $table->text('descripcion_grupo')->nullable()->after('grupo')
                ->comment('Descripción del grupo de items (ej: "Combinación para planes actuales")');

            // Crear nuevo índice único que incluya el grupo
            // Ahora un mismo item puede estar en diferentes grupos del mismo plan
            $table->unique(['plan_id', 'item_codigo', 'grupo'], 'plan_item_grupo_unique');

            // Agregar índice para búsquedas por grupo
            $table->index(['plan_id', 'grupo'], 'plan_grupo_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_items_requeridos', function (Blueprint $table) {
            // Eliminar índices creados
            $table->dropUnique('plan_item_grupo_unique');
            $table->dropIndex('plan_grupo_index');

            // Eliminar campos
            $table->dropColumn(['grupo', 'descripcion_grupo']);

            // Restaurar índice único original
            $table->unique(['plan_id', 'item_codigo']);
        });
    }
};
