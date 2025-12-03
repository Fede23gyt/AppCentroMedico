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
        Schema::create('prestadores', function (Blueprint $table) {
            $table->id();
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('dni', 20)->unique();
            $table->string('cuil', 20)->unique();
            $table->string('mail', 150)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('domicilio', 200)->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->date('fecha_alta');
            $table->string('usuario_alta', 100)->nullable();
            $table->date('fecha_baja')->nullable();
            $table->string('usuario_baja', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('dni');
            $table->index('cuil');
            $table->index('estado');
        });

        // Tabla pivot para relación muchos a muchos entre prestadores y sucursales
        Schema::create('prestador_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestador_id')->constrained('prestadores')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['prestador_id', 'sucursal_id']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestador_sucursal');
        Schema::dropIfExists('prestadores');
    }
};
