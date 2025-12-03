<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubro extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'porc',
        'estado'
    ];

    protected $casts = [
        'estado' => 'string'
    ];

    public function prestaciones()
    {
        return $this->hasMany(Prestacion::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
