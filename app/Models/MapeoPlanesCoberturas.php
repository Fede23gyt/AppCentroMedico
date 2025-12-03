<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MapeoPlanesCoberturas extends Model
{
  use HasFactory;

  // Especificar que use la conexión por defecto (MySQL en el .env actual)
  // Si necesitas PostgreSQL, cambia esta línea a: protected $connection = 'pgsql';
  // o actualiza DB_CONNECTION=pgsql en tu .env
  protected $connection = null; // Usa la conexión por defecto

  protected $table = 'mapeo_planes_coberturas';
  protected $primaryKey = 'mapeo_id';
  public $timestamps = false;

  protected $fillable = [
    'id_rubro_origen',
    'plan_origen_codigo',
    'id_adicional_origen',
    'cobertura_destino',
    'tipo_origen',
    'es_wildcard',
    'fecha_vigencia_desde',
    'fecha_vigencia_hasta',
    'activo',
    'notas',
  ];

  protected $casts = [
    'id_rubro_origen' => 'integer',
    'id_adicional_origen' => 'integer',
    'tipo_origen' => 'integer',
    'es_wildcard' => 'boolean',
    'activo' => 'boolean',
    'fecha_vigencia_desde' => 'datetime',
    'fecha_vigencia_hasta' => 'datetime',
    'fecha_creacion' => 'datetime',
    'fecha_ultima_actualizacion' => 'datetime',
  ];

  // Scopes útiles
  public function scopeActivos($query)
  {
    return $query->where('activo', true);
  }

  public function scopePorRubro($query, int $idRubro)
  {
    return $query->where('id_rubro_origen', $idRubro);
  }

  public function scopePorPlan($query, string $planCodigo)
  {
    return $query->where('plan_origen_codigo', $planCodigo)
      ->orWhere(function ($q) use ($planCodigo) {
        $q->where('es_wildcard', true)
          ->whereRaw("? LIKE plan_origen_codigo", [$planCodigo]);
      });
  }

  public function scopePorTipoOrigen($query, int $tipoOrigen)
  {
    return $query->where('tipo_origen', $tipoOrigen);
  }

  // Métodos útiles
  public static function obtenerCobertura(int $idRubro, string $planCodigo): ?string
  {
    $mapeo = self::activos()
      ->porRubro($idRubro)
      ->porPlan($planCodigo)
      ->orderBy('es_wildcard', 'asc')
      ->orderBy('mapeo_id', 'desc')
      ->first();

    return $mapeo?->cobertura_destino;
  }

  public static function obtenerTodos()
  {
    return self::activos()
      ->orderBy('id_rubro_origen')
      ->orderBy('plan_origen_codigo')
      ->get();
  }
}
