<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prestaciones', function (Blueprint $table) {
            $table->id();
            $table->char('codigo', 6)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->foreignId('rubro_id')->constrained('rubros');
            $table->decimal('precio_general', 10, 2)->default(0);
            $table->decimal('valor_ips', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('codigo');
            $table->index('nombre');
            $table->index('estado');
            $table->index('rubro_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prestaciones');
    }
};
