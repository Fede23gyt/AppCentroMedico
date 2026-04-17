<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalisisMigracionAfiliado extends Model
{
    use HasFactory;

    protected $table = 'analisis_migracion_afiliados';

    protected $fillable = [
        'id_ben_cp',
        'id_titular_cf',
        'dni',
        'apellido_nombre',
        'vinculo',
        'plan_salud',
        'plan_odo',
        'plan_pieve',
        'cobertura_inferida',
        'plan_centro_medico_id',
        'plan_centro_medico_nombre',
        'observaciones',
    ];

    public function planCentroMedico()
    {
        return $this->belongsTo(Plan::class, 'plan_centro_medico_id');
    }
}
