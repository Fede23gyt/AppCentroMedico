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
        return static::select('Beneficiarios.*')
            ->join('Fichas', 'Beneficiarios.IdTitularCF', '=', 'Fichas.IdTitularCp')
            ->where('Beneficiarios.IdTitularCF', $certificado)
            ->where('Beneficiarios.Existe', 1) // Beneficiario activo
            ->where('Fichas.Existe', 1) // Certificado/Ficha activo
            ->orderBy('Beneficiarios.IdVinculoCF') // Titular primero
            ->get();
    }

    // Método para obtener solo el titular
    public static function obtenerTitular($certificado)
    {
        return static::select('Beneficiarios.*')
            ->join('Fichas', 'Beneficiarios.IdTitularCF', '=', 'Fichas.IdTitularCp')
            ->where('Beneficiarios.IdTitularCF', $certificado)
            ->where('Beneficiarios.Existe', 1) // Beneficiario activo
            ->where('Fichas.Existe', 1) // Certificado/Ficha activo
            ->where('Beneficiarios.IdVinculoCf', 1) // Solo titular
            ->first();
    }

    // Método para verificar el estado del certificado
    public static function verificarEstadoCertificado($certificado)
    {
        return \DB::connection('sqlsrv_externa')
            ->table('Fichas')
            ->where('IdTitularCp', $certificado)
            ->select('IdTitularCp', 'Existe')
            ->first();
    }

    // Método para obtener los planes del afiliado desde PlanesFichas
    public static function obtenerPlanes($certificado)
    {
        $planes = \DB::connection('sqlsrv_externa')
            ->table('PlanesFichas')
            ->where('IdTitularCF', $certificado)
            ->select('Plan')
            ->get();

        // Concatenar los planes y retornar como string
        if ($planes->isEmpty()) {
            return 'Sin Plan';
        }

        return $planes->pluck('Plan')->implode(', ');
    }
}
