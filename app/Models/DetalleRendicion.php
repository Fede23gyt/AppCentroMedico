<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleRendicion extends Model
{
    protected $table = 'detalle_rendiciones';

    protected $fillable = [
        'rendicion_id',
        'orden_id',
        'total_prestador',
    ];

    protected $casts = [
        'total_prestador' => 'decimal:2',
    ];

    // Relaciones
    public function rendicion(): BelongsTo
    {
        return $this->belongsTo(Rendicion::class);
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }
}
