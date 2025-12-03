<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('det_ordenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            $table->foreignId('prestacion_id')->constrained('prestaciones')->onDelete('restrict');
            $table->unsignedBigInteger('cobertura_id')->nullable(); // Para futuro
            $table->integer('cantidad')->default(1);
            $table->decimal('importe_uni', 10, 2);
            $table->decimal('importe_total', 10, 2);
            $table->decimal('neto_gravado', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('total_afiliado', 10, 2);
            $table->decimal('total_prestador', 10, 2)->default(0);
            $table->timestamps();

            $table->index('orden_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('det_ordenes');
    }
};
