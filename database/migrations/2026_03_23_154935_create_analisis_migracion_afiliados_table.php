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
        Schema::create('analisis_migracion_afiliados', function (Blueprint $table) {
            $table->id();
            $table->integer('id_ben_cp')->nullable()->index();
            $table->integer('id_titular_cf')->nullable()->index();
            $table->string('dni')->nullable()->index();
            $table->string('apellido_nombre')->nullable();
            $table->integer('vinculo')->nullable();
            $table->string('plan_salud')->nullable();
            $table->string('plan_odo')->nullable();
            $table->string('plan_pieve')->nullable();
            $table->string('cobertura_inferida')->nullable();
            $table->foreignId('plan_centro_medico_id')->nullable()->constrained('planes');
            $table->string('plan_centro_medico_nombre')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis_migracion_afiliados');
    }
};
