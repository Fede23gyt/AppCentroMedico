<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPrecioPrestacion extends Model
{
    protected $table = 'historial_precios_prestaciones';

    protected $fillable = [
        'prestacion_id',
        'nivel_precio',
        'valor_anterior',
        'valor_nuevo',
        'fecha_vigencia_desde',
        'fecha_vigencia_hasta',
        'user_id',
        'motivo',
    ];

    protected $casts = [
        'valor_anterior'      => 'decimal:2',
        'valor_nuevo'         => 'decimal:2',
        'fecha_vigencia_desde' => 'date',
        'fecha_vigencia_hasta' => 'date',
    ];

    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
