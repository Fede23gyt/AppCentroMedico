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
        'descripcion'
    ];

    protected $casts = [
        'vigencia_desde' => 'date',
        'vigencia_hasta' => 'date',
        'estado' => 'string'
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
                'fecha_vigencia_desde',
                'fecha_vigencia_hasta',
                'observaciones'
            ])
            ->withTimestamps();
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
