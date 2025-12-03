<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orden extends Model
{
    protected $table = 'ordenes';

    protected $fillable = [
        'numero_orden',
        'numero_orden_completo',
        'fec_ord',
        'tipo_comprobante_id',
        'caja_diaria_id',
        'prestador_id',
        'titular_id',
        'beneficiario_id',
        'observacion',
        'motivo_anulacion',
        'total',
        'total_prestador',
        'total_afiliado',
        'usu_alta',
        'sucursal_id',
        'fec_anu',
        'usu_anu',
        'estado',
    ];

    protected $casts = [
        'fec_ord' => 'date',
        'fec_anu' => 'date',
        'total' => 'decimal:2',
        'total_prestador' => 'decimal:2',
        'total_afiliado' => 'decimal:2',
    ];

    // Relaciones
    public function tipoComprobante(): BelongsTo
    {
        return $this->belongsTo(TipoComprobante::class);
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(Prestador::class);
    }

    public function titular(): BelongsTo
    {
        return $this->belongsTo(Afiliado::class, 'titular_id');
    }

    public function beneficiario(): BelongsTo
    {
        return $this->belongsTo(Afiliado::class, 'beneficiario_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleOrden::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 1);
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 2);
    }

    public function scopeAnuladas($query)
    {
        return $query->where('estado', 3);
    }

    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    // Accessors
    public function getEstadoTextoAttribute()
    {
        $estados = [
            1 => 'Pendiente',
            2 => 'Pagada',
            3 => 'Anulada',
        ];
        return $estados[$this->estado] ?? 'Desconocido';
    }

    // Métodos helper
    public function esPendiente()
    {
        return $this->estado === 1;
    }

    public function esPagada()
    {
        return $this->estado === 2;
    }

    public function esAnulada()
    {
        return $this->estado === 3;
    }

    // Generar siguiente número de orden por sucursal
    public static function siguienteNumero($sucursalId)
    {
        $ultimo = static::where('sucursal_id', $sucursalId)
            ->max('numero_orden');

        return ($ultimo ?? 0) + 1;
    }

    // Alias para compatibilidad
    public static function obtenerSiguienteNumero($sucursalId)
    {
        return static::siguienteNumero($sucursalId);
    }

    // Accessor para obtener el número de orden formateado
    public function getNumeroOrdenFormateadoAttribute()
    {
        // Formato: TIPO-CODSUC-NUMORDEN
        // Ejemplo: OR-0001-00000123 o NC-0001-00000045

        $tipoCodigo = 'OR'; // Por defecto Orden
        if ($this->tipoComprobante) {
            $tipoCodigo = $this->tipoComprobante->codigo; // OR, NC, FAC, etc
        }

        $codigoSucursal = $this->sucursal ? str_pad($this->sucursal->codigo, 4, '0', STR_PAD_LEFT) : '0000';
        $numeroOrden = str_pad($this->numero_orden, 8, '0', STR_PAD_LEFT);

        return "{$tipoCodigo}-{$codigoSucursal}-{$numeroOrden}";
    }
}
