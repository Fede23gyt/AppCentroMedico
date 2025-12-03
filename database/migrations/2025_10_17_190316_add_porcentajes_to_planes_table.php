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
        Schema::table('planes', function (Blueprint $table) {
            $table->decimal('porc_salud', 5, 2)->nullable()->after('descripcion')->comment('Porcentaje de salud');
            $table->decimal('porc_odo', 5, 2)->nullable()->after('porc_salud')->comment('Porcentaje de odontología');
            $table->decimal('porc_ord', 5, 2)->nullable()->after('porc_odo')->comment('Porcentaje de orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn(['porc_salud', 'porc_odo', 'porc_ord']);
        });
    }
};
