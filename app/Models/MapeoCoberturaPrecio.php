<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapeoCoberturaPrecio extends Model
{
    use HasFactory;

    protected $table = 'mapeo_coberturas_precios';

    protected $fillable = [
        'cobertura_codigo',
        'tipo_precio',
        'rubro_tipo',
    ];
}
