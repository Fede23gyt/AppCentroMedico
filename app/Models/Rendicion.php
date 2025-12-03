<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rendicion extends Model
{
    protected $table = 'rendiciones';

    protected $fillable = [
        'numero_rendicion',
        'numero_rendicion_completo',
        'sucursal_id',
        'prestador_id',
        'fecha_inicio',
        'fecha_cierre',
        'estado',
        'total',
        'usu_alta',
        'usu_cierre',
        'observacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_cierre' => 'date',
        'total' => 'decimal:2',
    ];

    // Relaciones
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(Prestador::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRendicion::class);
    }

    // Scopes
    public function scopeAbiertas($query)
    {
        return $query->where('estado', 1);
    }

    public function scopeCerradas($query)
    {
        return $query->where('estado', 2);
    }

    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    // Accessors
    public function getEstadoTextoAttribute()
    {
        $estados = [
            1 => 'Abierta',
            2 => 'Cerrada',
        ];
        return $estados[$this->estado] ?? 'Desconocido';
    }

    // Métodos helper
    public function esAbierta()
    {
        return $this->estado === 1;
    }

    public function esCerrada()
    {
        return $this->estado === 2;
    }

    // Generar siguiente número de rendición por sucursal
    public static function siguienteNumero($sucursalId)
    {
        $ultimo = static::where('sucursal_id', $sucursalId)
            ->max('numero_rendicion');

        return ($ultimo ?? 0) + 1;
    }
}
