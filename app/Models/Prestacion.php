<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestacion extends Model
{
    use HasFactory;

    protected $table = 'prestaciones';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
        'rubro_id',
        'precio_general',
        'precio_afiliado',
        'valor_ips',
        'val_ref',
        'porc_ips',
        'precio_1',
        'precio_2',
        'precio_3',
        'precio_4',
        'observaciones'
    ];

    protected $casts = [
        'precio_general' => 'decimal:2',
        'precio_afiliado' => 'decimal:2',
        'valor_ips' => 'decimal:2',
        'val_ref' => 'decimal:2',
        'porc_ips' => 'decimal:2',
        'precio_1' => 'decimal:2',
        'precio_2' => 'decimal:2',
        'precio_3' => 'decimal:2',
        'precio_4' => 'decimal:2',
        'estado' => 'string'
    ];

    // Validación del código antes de guardar
    public static function boot()
    {
        parent::boot();

        static::saving(function ($prestacion) {
            if (!preg_match('/^\d{6}$/', $prestacion->codigo)) {
                throw new \InvalidArgumentException('El código debe tener exactamente 6 dígitos');
            }
        });
    }

    public function rubro()
    {
        return $this->belongsTo(Rubro::class);
    }

    public function planes()
    {
        return $this->belongsToMany(Plan::class, 'prestaciones_planes')
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

    public function preciosSucursales()
    {
        return $this->hasMany(PrecioSucursal::class);
    }

    public function historialPrecios()
    {
        return $this->hasMany(HistorialPrecioPrestacion::class)->orderByDesc('created_at');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorRubro($query, $rubroId)
    {
        return $query->where('rubro_id', $rubroId);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
                ->orWhere('nombre', 'like', "%{$termino}%");
        });
    }

    // Generar próximo código disponible
    public static function siguienteCodigo()
    {
        $ultimo = static::orderBy('codigo', 'desc')->first();

        if (!$ultimo) {
            return '000001';
        }

        $siguienteNumero = intval($ultimo->codigo) + 1;
        return str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Resuelve el precio activo para una prestacion en un nivel y sucursal
     * @param int $sucursalId
     * @param int $nivel (1, 2, 3, 4)
     * @return float
     */
    public function obtenerPrecio(int $sucursalId, int $nivel = 4): float
    {
        $nivel = min(max($nivel, 1), 4);
        
        $campoPrecio = "precio_" . $nivel;
        
        // 1. Buscar Excepcion de Sucursal Activa
        $excepcion = $this->preciosSucursales()
            ->where('sucursal_id', $sucursalId)
            ->first();

        if ($excepcion) {
            return (float) $excepcion->$campoPrecio;
        }

        // 2. Si no hay excepcion, devolver precio base
        return (float) $this->$campoPrecio;
    }
}
