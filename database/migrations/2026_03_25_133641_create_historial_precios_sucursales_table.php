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
        Schema::create('historial_precios_sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('prestacion_id')->constrained('prestaciones')->cascadeOnDelete();
            $table->tinyInteger('nivel_precio'); // 1, 2, 3 o 4
            $table->decimal('valor_anterior', 12, 2)->default(0);
            $table->decimal('valor_nuevo', 12, 2)->default(0);
            $table->date('fecha_vigencia_desde');
            $table->date('fecha_vigencia_hasta')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->index(['sucursal_id', 'prestacion_id', 'nivel_precio'], 'idx_hist_suc_prest_nivel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_precios_sucursales');
    }
};
