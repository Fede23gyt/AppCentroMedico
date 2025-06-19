<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('nombre_corto', 20);
            $table->date('vigencia_desde');
            $table->date('vigencia_hasta')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index('nombre_corto');
            $table->index('estado');
            $table->index(['vigencia_desde', 'vigencia_hasta']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('planes');
    }
};
