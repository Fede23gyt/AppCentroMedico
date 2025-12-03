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
        Schema::create('afiliados', function (Blueprint $table) {
            $table->id();
            $table->string('certificado', 50);
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('dni', 20)->unique();
            $table->integer('vinculo'); // 1=Titular, 2=Cónyuge, 3=Hijo, etc.
            $table->foreignId('plan_id')->constrained('planes')->onDelete('restrict');
            $table->boolean('tiene_cobertura')->default(true); // true=usa precio_afiliado, false=usa precio_general
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->index('certificado');
            $table->index('dni');
            $table->index(['certificado', 'vinculo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliados');
    }
};
