<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiarioExterno extends Model
{
    // Usar la conexión de SQL Server externa
    protected $connection = 'sqlsrv_externa';

    // Nombre de la tabla en MSSQL
    protected $table = 'Beneficiarios';

    // Deshabilitar timestamps (created_at, updated_at)
    public $timestamps = false;

    // Clave primaria
    protected $primaryKey = 'IdBenCP';

    // Campos que se pueden asignar
    protected $fillable = [
        'IdBenCP',
        'IdTitularCF',
        'Apellido',
        'Nombre',
        'Dni',
        'IdVinculoCf',
        'Existe',
    ];

    // Casteos de tipos
    protected $casts = [
        'IdVinculoCf' => 'integer',
        'Existe' => 'boolean',
        'Dni' => 'string',
    ];

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('Existe', 1);
    }

    public function scopePorCertificado($query, $certificado)
    {
        return $query->where('IdTitularCF', $certificado);
    }

    public function scopeTitulares($query)
    {
        return $query->where('IdVinculoCf', 1);
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return "{$this->Apellido}, {$this->Nombre}";
    }

    public function getEsTitularAttribute()
    {
        return $this->IdVinculoCf === 1;
    }

    public function getVinculoTextoAttribute()
    {
        $vinculos = [
            1 => 'Titular',
            2 => 'Cónyuge',
            3 => 'Hijo/a',
            4 => 'Otro',
        ];
        return $vinculos[$this->IdVinculoCf] ?? 'Desconocido';
    }

    // Método para buscar por certificado con titular y beneficiarios
    public static function buscarPorCertificado($certificado)
    {
        return static::activos()
            ->porCertificado($certificado)
            ->orderBy('IdVinculoCF') // Titular primero
            ->get();
    }

    // Método para obtener solo el titular
    public static function obtenerTitular($certificado)
    {
        return static::activos()
            ->porCertificado($certificado)
            ->titulares()
            ->first();
    }
}
