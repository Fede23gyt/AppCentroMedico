<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prestaciones_planes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestacion_id')->constrained('prestaciones')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('planes')->onDelete('cascade');
            $table->decimal('valor_afiliado', 10, 2)->default(0);
            $table->decimal('valor_particular', 10, 2)->default(0);
            $table->integer('cant_max_individual')->nullable();
            $table->integer('cant_max_grupo')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['prestacion_id', 'plan_id']);
            $table->index('prestacion_id');
            $table->index('plan_id');
            $table->index('estado');
            $table->index(['fecha_desde', 'fecha_hasta']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('prestaciones_planes');
    }
};
