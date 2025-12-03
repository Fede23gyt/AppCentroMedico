<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_orden');
            $table->date('fec_ord');
            $table->foreignId('tipo_comprobante_id')->constrained('tipo_comprobante')->onDelete('restrict');
            $table->unsignedBigInteger('caja_diaria_id')->nullable();
            $table->foreignId('prestador_id')->nullable()->constrained('prestadores')->onDelete('set null');
            $table->foreignId('titular_id')->constrained('afiliados')->onDelete('restrict');
            $table->foreignId('beneficiario_id')->constrained('afiliados')->onDelete('restrict');
            $table->text('observacion')->nullable();
            $table->decimal('total', 10, 2);
            $table->decimal('total_prestador', 10, 2)->default(0);
            $table->decimal('total_afiliado', 10, 2);
            $table->string('usu_alta', 100);
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('restrict');
            $table->date('fec_anu')->nullable();
            $table->string('usu_anu', 100)->nullable();
            $table->integer('estado'); // 1=Pendiente, 2=Pagada, 3=Anulada
            $table->timestamps();

            $table->index(['sucursal_id', 'numero_orden']);
            $table->index('fec_ord');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
