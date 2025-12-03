<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Afiliado extends Model
{
    protected $table = 'afiliados';

    protected $fillable = [
        'certificado',
        'apellido',
        'nombre',
        'dni',
        'vinculo',
        'plan_id',
        'tiene_cobertura',
        'items_actuales',
        'ultima_verificacion_items',
        'estado',
    ];

    protected $casts = [
        'tiene_cobertura' => 'boolean',
        'items_actuales' => 'array',
        'ultima_verificacion_items' => 'datetime',
    ];

    // Relaciones
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function itemsHistorico()
    {
        return $this->hasMany(AfiliadoItemHistorico::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorCertificado($query, $certificado)
    {
        return $query->where('certificado', $certificado);
    }

    public function scopeTitulares($query)
    {
        return $query->where('vinculo', 1);
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function getEsTitularAttribute()
    {
        return $this->vinculo === 1;
    }

    public function getVinculoTextoAttribute()
    {
        $vinculos = [
            1 => 'Titular',
            2 => 'Cónyuge',
            3 => 'Hijo/a',
            4 => 'Otro',
        ];
        return $vinculos[$this->vinculo] ?? 'Desconocido';
    }
}
