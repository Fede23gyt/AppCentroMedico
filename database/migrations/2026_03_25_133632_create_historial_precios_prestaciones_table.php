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
        Schema::create('historial_precios_prestaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestacion_id')->constrained('prestaciones')->cascadeOnDelete();
            $table->tinyInteger('nivel_precio'); // 1, 2, 3 o 4
            $table->decimal('valor_anterior', 12, 2)->default(0);
            $table->decimal('valor_nuevo', 12, 2)->default(0);
            $table->date('fecha_vigencia_desde');
            $table->date('fecha_vigencia_hasta')->nullable(); // null = precio vigente actual
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->index(['prestacion_id', 'nivel_precio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_precios_prestaciones');
    }
};
