<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecioSucursal extends Model
{
    protected $table = 'precios_sucursales';

    protected $fillable = [
        'sucursal_id',
        'prestacion_id',
        'precio_1',
        'precio_2',
        'precio_3',
        'precio_4',
    ];

    protected $casts = [
        'precio_1' => 'decimal:2',
        'precio_2' => 'decimal:2',
        'precio_3' => 'decimal:2',
        'precio_4' => 'decimal:2',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialPrecioSucursal::class, 'sucursal_id', 'sucursal_id')
                    ->where('prestacion_id', $this->prestacion_id)
                    ->orderByDesc('created_at');
    }
}
