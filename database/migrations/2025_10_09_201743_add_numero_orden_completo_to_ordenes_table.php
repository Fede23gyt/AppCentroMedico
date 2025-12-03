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
            $table->string('numero_orden_completo', 50)->nullable()->after('numero_orden')->index();
        });

        // Generar números completos para órdenes existentes
        $ordenes = DB::table('ordenes')
            ->join('tipo_comprobante', 'ordenes.tipo_comprobante_id', '=', 'tipo_comprobante.id')
            ->join('sucursales', 'ordenes.sucursal_id', '=', 'sucursales.id')
            ->select('ordenes.id', 'ordenes.numero_orden', 'tipo_comprobante.codigo as tipo_codigo', 'sucursales.codigo as sucursal_codigo')
            ->get();

        foreach ($ordenes as $orden) {
            $numeroCompleto = sprintf(
                '%s-%s-%s',
                $orden->tipo_codigo,
                str_pad($orden->sucursal_codigo, 4, '0', STR_PAD_LEFT),
                str_pad($orden->numero_orden, 8, '0', STR_PAD_LEFT)
            );

            DB::table('ordenes')
                ->where('id', $orden->id)
                ->update(['numero_orden_completo' => $numeroCompleto]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropColumn('numero_orden_completo');
        });
    }
};
