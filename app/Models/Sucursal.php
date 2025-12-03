<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function supervisores()
    {
        return $this->users()->role('supervisor');
    }

    public function cajeros()
    {
        return $this->users()->role('cajero');
    }

    public function vendedores()
    {
        return $this->users()->role('vendedor');
    }

    public function administrativos()
    {
        return $this->users()->role('administrativo');
    }

    // Relación muchos a muchos con Prestadores
    public function prestadores()
    {
        return $this->belongsToMany(Prestador::class, 'prestador_sucursal')
            ->withPivot(['fecha_desde', 'fecha_hasta', 'estado', 'observaciones'])
            ->withTimestamps();
    }

    // Relación muchos a muchos con Planes
    public function planes()
    {
        return $this->belongsToMany(Plan::class, 'plan_sucursal')
            ->withPivot(['fecha_desde', 'fecha_hasta', 'estado', 'observaciones'])
            ->withTimestamps();
    }
}
