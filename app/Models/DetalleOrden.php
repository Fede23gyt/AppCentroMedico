<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleOrden extends Model
{
    protected $table = 'det_ordenes';

    protected $fillable = [
        'orden_id',
        'prestacion_id',
        'cobertura_id',
        'cantidad',
        'importe_uni',
        'importe_total',
        'neto_gravado',
        'iva',
        'total_afiliado',
        'total_prestador',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'importe_uni' => 'decimal:2',
        'importe_total' => 'decimal:2',
        'neto_gravado' => 'decimal:2',
        'iva' => 'decimal:2',
        'total_afiliado' => 'decimal:2',
        'total_prestador' => 'decimal:2',
    ];

    // Relaciones
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }

    public function prestacion(): BelongsTo
    {
        return $this->belongsTo(Prestacion::class);
    }
}
