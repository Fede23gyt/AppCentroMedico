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
        Schema::create('rendiciones', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_rendicion');
            $table->string('numero_rendicion_completo', 50)->index();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('prestador_id')->constrained('prestadores');
            $table->date('fecha_inicio');
            $table->date('fecha_cierre')->nullable();
            $table->tinyInteger('estado')->default(1)->comment('1=Abierta, 2=Cerrada');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('usu_alta', 100);
            $table->string('usu_cierre', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['sucursal_id', 'numero_rendicion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendiciones');
    }
};
