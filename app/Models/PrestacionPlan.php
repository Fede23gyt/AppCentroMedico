<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PrestacionPlan extends Pivot
{
    protected $table = 'prestaciones_planes';

    protected $fillable = [
        'prestacion_id',
        'plan_id',
        'valor_afiliado',
        'valor_particular',
        'cant_max_individual',
        'cant_max_grupo',
        'estado',
        'fecha_vigencia_desde',
        'fecha_vigencia_hasta',
        'observaciones'
    ];

    protected $casts = [
        'valor_afiliado' => 'decimal:2',
        'valor_particular' => 'decimal:2',
        'fecha_vigencia_desde' => 'date',
        'fecha_vigencia_hasta' => 'date',
        'estado' => 'string'
    ];

    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
