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
        // Actualizar códigos de sucursales al formato 0001, 0002, etc.
        $sucursales = DB::table('sucursales')->orderBy('id')->get();

        foreach ($sucursales as $index => $sucursal) {
            $nuevoCodigo = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            DB::table('sucursales')
                ->where('id', $sucursal->id)
                ->update(['codigo' => $nuevoCodigo]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay rollback necesario para esta migración
    }
};
