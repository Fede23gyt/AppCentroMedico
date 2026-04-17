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
        Schema::create('limites_cobertura_prestaciones', function (Blueprint $table) {
            $table->id();
            
            // Si la cobertura es nula, el límite aplica a TODOS los que tengan plan activo
            $table->string('cobertura_codigo')->nullable(); 
            
            // A qué prestación específica le estamos poniendo tope $0
            $table->foreignId('prestacion_id')->constrained('prestaciones')->onDelete('cascade');
            
            $table->integer('limite_mensual_individual')->default(0);
            $table->integer('limite_mensual_familia')->default(0);
            
            $table->boolean('estado')->default(true);
            $table->string('observacion')->nullable();

            $table->timestamps();

            // Para no duplicar la misma configuración:
            $table->unique(['cobertura_codigo', 'prestacion_id'], 'uk_lim_cob_prest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('limites_cobertura_prestaciones');
    }
};
