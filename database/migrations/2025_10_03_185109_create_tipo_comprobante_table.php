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
        Schema::create('tipo_comprobante', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 10)->unique();
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        // Insertar tipos de comprobante por defecto
        DB::table('tipo_comprobante')->insert([
            ['nombre' => 'Orden de Atención', 'codigo' => 'ORD', 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Factura', 'codigo' => 'FAC', 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Recibo', 'codigo' => 'REC', 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_comprobante');
    }
};
