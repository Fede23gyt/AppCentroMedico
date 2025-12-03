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
        Schema::create('afiliado_items_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('afiliado_id')->constrained('afiliados')->onDelete('cascade');
            $table->string('item_codigo', 50);
            $table->timestamp('fecha_lectura')->useCurrent();
            $table->string('certificado', 50); // Para trazabilidad
            $table->timestamps();

            // Índices para consultas históricas
            $table->index(['afiliado_id', 'fecha_lectura']);
            $table->index('certificado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliado_items_historico');
    }
};
