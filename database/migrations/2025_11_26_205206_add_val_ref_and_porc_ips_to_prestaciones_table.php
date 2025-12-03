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
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->decimal('val_ref', 10, 2)->default(0)->after('valor_ips')->comment('Valor Referencia (antes Precio Afiliado)');
            $table->decimal('porc_ips', 5, 2)->default(0)->after('val_ref')->comment('Porcentaje IPS (antes Precio General)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->dropColumn(['val_ref', 'porc_ips']);
        });
    }
};
