<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'nombre_corto',
        'vigencia_desde',
        'vigencia_hasta',
        'estado',
        'descripcion',
        'porc_salud',
        'porc_odo',
        'porc_ord'
    ];

    protected $casts = [
        'vigencia_desde' => 'date',
        'vigencia_hasta' => 'date',
        'estado' => 'string',
        'porc_salud' => 'decimal:2',
        'porc_odo' => 'decimal:2',
        'porc_ord' => 'decimal:2'
    ];

    public function prestaciones()
    {
        return $this->belongsToMany(Prestacion::class, 'prestaciones_planes')
            ->withPivot([
                'valor_afiliado',
                'valor_particular',
                'cant_max_individual',
                'cant_max_grupo',
                'estado',
                'fecha_desde',
                'fecha_hasta',
                'observaciones'
            ])
            ->withTimestamps();
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'plan_sucursal')
            ->withPivot([
                'fecha_desde',
                'fecha_hasta',
                'estado',
                'observaciones'
            ])
            ->withTimestamps();
    }

    public function itemsRequeridos()
    {
        return $this->hasMany(PlanItemRequerido::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeVigentes($query)
    {
        $hoy = Carbon::now()->toDateString();
        return $query->where('vigencia_desde', '<=', $hoy)
            ->where(function($q) use ($hoy) {
                $q->whereNull('vigencia_hasta')
                    ->orWhere('vigencia_hasta', '>=', $hoy);
            });
    }

    public function getEsVigenteAttribute()
    {
        $hoy = Carbon::now();
        return $this->vigencia_desde <= $hoy &&
            ($this->vigencia_hasta === null || $this->vigencia_hasta >= $hoy);
    }
}