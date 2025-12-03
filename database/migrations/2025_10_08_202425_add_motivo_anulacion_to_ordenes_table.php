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
        Schema::table('ordenes', function (Blueprint $table) {
            $table->text('motivo_anulacion')->nullable()->after('observacion');
        });

        // Agregar tipo de comprobante "Nota de Crédito"
        DB::table('tipo_comprobante')->insert([
            'nombre' => 'Nota de Crédito',
            'codigo' => 'NC',
            'descripcion' => 'Nota de crédito generada por anulación de orden pagada',
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropColumn('motivo_anulacion');
        });

        DB::table('tipo_comprobante')->where('codigo', 'NC')->delete();
    }
};
