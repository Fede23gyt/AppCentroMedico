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
        Schema::table('rubros', function (Blueprint $table) {
            $table->decimal('porc', 5, 2)->default(0)->after('descripcion')->comment('Porcentaje de descuento sobre el valor IPS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubros', function (Blueprint $table) {
            $table->dropColumn('porc');
        });
    }
};
