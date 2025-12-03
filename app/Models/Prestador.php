<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prestadores';

    protected $fillable = [
        'apellido',
        'nombre',
        'dni',
        'cuil',
        'mail',
        'telefono',
        'domicilio',
        'estado',
        'fecha_alta',
        'usuario_alta',
        'fecha_baja',
        'usuario_baja',
        'observaciones',
    ];

    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
    ];

    // Relación muchos a muchos con Sucursales
    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'prestador_sucursal')
            ->withPivot(['fecha_desde', 'fecha_hasta', 'estado', 'observaciones'])
            ->withTimestamps();
    }

    // Scope para prestadores activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Scope para prestadores inactivos
    public function scopeInactivos($query)
    {
        return $query->where('estado', 'inactivo');
    }

    // Scope para buscar por nombre, apellido, DNI o CUIL
    public function scopeBuscar($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('apellido', 'like', "%{$search}%")
              ->orWhere('dni', 'like', "%{$search}%")
              ->orWhere('cuil', 'like', "%{$search}%");
        });
    }

    // Scope para filtrar por sucursal
    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->whereHas('sucursales', function($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        });
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute()
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    // Verificar si el prestador está activo
    public function isActivo()
    {
        return $this->estado === 'activo';
    }

    // Verificar si el prestador tiene sucursales asignadas
    public function tieneSucursales()
    {
        return $this->sucursales()->count() > 0;
    }
}
