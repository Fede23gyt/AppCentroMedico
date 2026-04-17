<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimiteCoberturaPrestacion extends Model
{
    protected $table = 'limites_cobertura_prestaciones';
    
    protected $fillable = [
        'cobertura_codigo',
        'prestacion_id',
        'limite_mensual_individual',
        'limite_mensual_familia',
        'estado',
        'observacion',
    ];

    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class);
    }
}
