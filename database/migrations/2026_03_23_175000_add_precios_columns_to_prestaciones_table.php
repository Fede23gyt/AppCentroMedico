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
            // Añadimos las 4 columnas de precios (IVA Incluido)
            $table->decimal('precio_1', 12, 2)->default(0)->after('precio_general');
            $table->decimal('precio_2', 12, 2)->default(0)->after('precio_1');
            $table->decimal('precio_3', 12, 2)->default(0)->after('precio_2');
            $table->decimal('precio_4', 12, 2)->default(0)->after('precio_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->dropColumn(['precio_1', 'precio_2', 'precio_3', 'precio_4']);
        });
    }
};
