<?php

namespace App\Http\Controllers;

use App\Models\MapeoPlanesCoberturas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MapeoSaludController extends Controller
{
  /**
   * Genera la tabla de mapeo con las equivalencias
   * Este método consulta datos desde SQL Server y los guarda en MySQL
   */
  public function generarMapeo()
  {
    try {
      // Iniciar transacción en la base de datos principal (MySQL)
      DB::beginTransaction();

      // Limpiar mapeos existentes (opcional)
      // MapeoPlanesCoberturas::truncate();

      // Obtener mapeos hardcodeados
      $mapeos = $this->extraerMapeosDelProcedure();

      // Opcional: También puedes consultar datos desde SQL Server
      $mapeosSqlServer = $this->consultarDatosSqlServer();

      // Combinar ambos arreglos si es necesario
      if (!empty($mapeosSqlServer)) {
        $mapeos = array_merge($mapeos, $mapeosSqlServer);
      }

      // Guardar en PostgreSQL
      foreach ($mapeos as $mapeo) {
        // Agregar timestamps manualmente ya que el modelo tiene $timestamps = false
        $mapeo['fecha_ultima_actualizacion'] = now();

        if (!isset($mapeo['fecha_creacion'])) {
          $mapeo['fecha_creacion'] = now();
        }

        MapeoPlanesCoberturas::updateOrCreate(
          [
            'id_rubro_origen' => $mapeo['id_rubro_origen'] ?? null,
            'plan_origen_codigo' => $mapeo['plan_origen_codigo'] ?? null,
            'tipo_origen' => $mapeo['tipo_origen'],
            'cobertura_destino' => $mapeo['cobertura_destino'],
          ],
          $mapeo
        );
      }

      DB::commit();

      return response()->json([
        'exito' => true,
        'mensaje' => 'Tabla de mapeo generada correctamente',
        'total_mapeos' => count($mapeos),
        'mapeos_hardcodeados' => count($this->extraerMapeosDelProcedure()),
        'mapeos_sqlserver' => count($mapeosSqlServer),
        'estadisticas' => $this->obtenerEstadisticas(),
      ], 200);

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error generando mapeo de planes: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());

      return response()->json([
        'exito' => false,
        'error' => $e->getMessage(),
        'linea' => $e->getLine(),
        'archivo' => $e->getFile(),
      ], 500);
    }
  }

  /**
   * Consulta datos desde SQL Server
   * Aquí puedes agregar las consultas específicas que necesites
   */
  private function consultarDatosSqlServer(): array
  {
    try {
      // Usar la conexión sqlsrv_externa configurada en config/database.php
      $sqlServerData = [];

      // Ejemplo 1: Consultar tabla de rubros desde SQL Server
      $rubros = DB::connection('sqlsrv_externa')
        ->table('dbo.tb_Rubro') // Ajusta el nombre de la tabla según tu esquema
        ->select('id_rubro', 'nombre', 'codigo')
        ->where('activo', 1)
        ->get();

      Log::info('Rubros obtenidos de SQL Server: ' . $rubros->count());

      // Ejemplo 2: Consultar planes desde SQL Server
      $planes = DB::connection('sqlsrv_externa')
        ->table('dbo.tb_Planes') // Ajusta el nombre de la tabla según tu esquema
        ->select('id_plan', 'codigo_plan', 'id_rubro', 'nombre')
        ->where('activo', 1)
        ->get();

      Log::info('Planes obtenidos de SQL Server: ' . $planes->count());

      // Ejemplo 3: Consultar tabla de coberturas
      $coberturas = DB::connection('sqlsrv_externa')
        ->table('dbo.tb_Tabla_Cobertura') // Ajusta el nombre
        ->select('id_cobertura', 'codigo_cobertura', 'descripcion')
        ->get();

      Log::info('Coberturas obtenidas de SQL Server: ' . $coberturas->count());

      // Aquí puedes procesar los datos y crear mapeos dinámicos
      // Por ejemplo, crear mapeos basados en los datos obtenidos
      foreach ($planes as $plan) {
        // Lógica para determinar el mapeo
        // Este es solo un ejemplo, ajusta según tu lógica de negocio
        $sqlServerData[] = [
          'id_rubro_origen' => $plan->id_rubro,
          'plan_origen_codigo' => $plan->codigo_plan,
          'cobertura_destino' => $plan->codigo_plan, // O la lógica que necesites
          'tipo_origen' => 2,
          'es_wildcard' => false,
          'activo' => true,
          'notas' => 'Importado desde SQL Server: ' . $plan->nombre,
        ];
      }

      return $sqlServerData;

    } catch (\Exception $e) {
      Log::error('Error consultando SQL Server: ' . $e->getMessage());
      Log::warning('Continuando con mapeos hardcodeados solamente');

      // Retornar array vacío si falla, para que continúe con los mapeos hardcodeados
      return [];
    }
  }

  /**
   * Extrae los mapeos identificados en el procedure
   */
  private function extraerMapeosDelProcedure(): array
  {
    return [
      // Rubro 3 - Odontología
      [
        'id_rubro_origen' => 3,
        'plan_origen_codigo' => '%',
        'cobertura_destino' => 'ODO',
        'tipo_origen' => 2,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Todos los planes de Odontología',
      ],

      // Rubro 1 - Sin Salud
      [
        'id_rubro_origen' => 1,
        'plan_origen_codigo' => '%',
        'cobertura_destino' => 'SINSAL',
        'tipo_origen' => 2,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Sin cobertura de salud',
      ],

      // Rubro 2 - Salud (NIV → INI)
      [
        'id_rubro_origen' => 2,
        'plan_origen_codigo' => 'NIV',
        'cobertura_destino' => 'INI',
        'tipo_origen' => 2,
        'es_wildcard' => false,
        'activo' => true,
        'notas' => 'Plan NIV se mapea a INI',
      ],

      // Rubro 7 - Planes PIEVE
      [
        'id_rubro_origen' => 7,
        'plan_origen_codigo' => 'PIE',
        'cobertura_destino' => 'PIE',
        'tipo_origen' => 2,
        'es_wildcard' => false,
        'activo' => true,
        'notas' => 'Plan PIE original',
      ],

      [
        'id_rubro_origen' => 7,
        'plan_origen_codigo' => 'PIEANTA%',
        'cobertura_destino' => 'PJV',
        'tipo_origen' => 2,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Planes PIEANTA antiguos mapean a PJV',
      ],

      [
        'id_rubro_origen' => 7,
        'plan_origen_codigo' => 'PIEVEANTA%',
        'cobertura_destino' => 'PJV',
        'tipo_origen' => 2,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Planes PIEVEANTA mapean a PJV',
      ],

      // Rubro 6 - Pieve
      [
        'id_rubro_origen' => 6,
        'plan_origen_codigo' => '%',
        'cobertura_destino' => 'PIE',
        'tipo_origen' => 2,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Rubro 6 mapea a PIE',
      ],

      // Tipo Origen 1 - Adicionales
      [
        'id_adicional_origen' => 0,
        'cobertura_destino' => '%',
        'tipo_origen' => 1,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Usar código de tabla tb_Tabla_Cobertura directamente',
      ],

      // Tipo Origen 4 - Planes Oran
      [
        'plan_origen_codigo' => 'CMO%',
        'cobertura_destino' => '%',
        'tipo_origen' => 4,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Planes Oran (Clínica del Sagrado Corazón)',
      ],

      [
        'plan_origen_codigo' => 'NORTE%',
        'cobertura_destino' => '%',
        'tipo_origen' => 4,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Planes Oran región Norte',
      ],

      [
        'plan_origen_codigo' => 'INICIAL_O%',
        'cobertura_destino' => '%',
        'tipo_origen' => 4,
        'es_wildcard' => true,
        'activo' => true,
        'notas' => 'Planes iniciales Oran',
      ],

      // Coberturas por localidad
      [
        'cobertura_destino' => 'SALBASIC',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Cobertura básica de salud para todos',
      ],

      [
        'cobertura_destino' => 'PIEVETAR',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Plan Pieve Tartagal',
      ],

      [
        'cobertura_destino' => 'PIEVECACHI',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Plan Pieve Cachi',
      ],

      [
        'cobertura_destino' => 'PIEVECAF',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Plan Pieve Cafayate',
      ],

      [
        'cobertura_destino' => 'ODONCAF',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Odontología Cafayate',
      ],

      [
        'cobertura_destino' => 'PIEVERF',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Plan Pieve Rosario de la Frontera',
      ],

      [
        'cobertura_destino' => 'ODORF',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Odontología Rosario de la Frontera',
      ],

      [
        'cobertura_destino' => 'PIEVEMETAN',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Plan Pieve Metán',
      ],

      [
        'cobertura_destino' => 'SINSALMETAN',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Sin salud Metán',
      ],

      [
        'cobertura_destino' => 'ODONMETAN',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Odontología Metán',
      ],

      [
        'cobertura_destino' => 'PIEVEQUEB',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Plan Pieve Qubracho',
      ],

      [
        'cobertura_destino' => 'ODONQUEB',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Odontología Qubracho',
      ],

      [
        'cobertura_destino' => 'NORSAL',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Salud Norte (Oran, Embarcación, Colonia)',
      ],

      [
        'cobertura_destino' => 'NORSALBASIC',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Salud Norte básica',
      ],

      [
        'cobertura_destino' => 'ODONORA',
        'tipo_origen' => 4,
        'activo' => true,
        'notas' => 'Odontología convenio Oran',
      ],

      [
        'cobertura_destino' => 'ODO_BASE',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Odontología base para todos',
      ],

      [
        'cobertura_destino' => 'ODO_02',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Odontología nivel 2',
      ],

      [
        'cobertura_destino' => 'SSALPJV',
        'tipo_origen' => 2,
        'activo' => true,
        'notas' => 'Sin salud + PJV (combinado)',
      ],
    ];
  }

  /**
   * Obtiene estadísticas de los mapeos
   */
  public function obtenerEstadisticas()
  {
    return [
      'total_mapeos' => MapeoPlanesCoberturas::count(),
      'mapeos_activos' => MapeoPlanesCoberturas::activos()->count(),
      'coberturas_diferentes' => MapeoPlanesCoberturas::activos()->distinct()->count('cobertura_destino'),
      'tipos_origen' => MapeoPlanesCoberturas::activos()->distinct()->count('tipo_origen'),
      'rubros_diferentes' => MapeoPlanesCoberturas::activos()->distinct()->count('id_rubro_origen'),
    ];
  }

  /**
   * Obtiene todos los mapeos (útil para debugging)
   */
  public function listarMapeos()
  {
    return response()->json(
      MapeoPlanesCoberturas::obtenerTodos(),
      200
    );
  }

  /**
   * Busca la cobertura equivalente para un plan
   */
  public function buscarCobertura(Request $request)
  {
    $validated = $request->validate([
      'id_rubro' => 'required|integer',
      'plan_codigo' => 'required|string',
    ]);

    $cobertura = MapeoPlanesCoberturas::obtenerCobertura(
      $validated['id_rubro'],
      $validated['plan_codigo']
    );

    return response()->json([
      'plan_codigo' => $validated['plan_codigo'],
      'id_rubro' => $validated['id_rubro'],
      'cobertura_equivalente' => $cobertura ?? 'NO_ENCONTRADO',
    ], 200);
  }

  /**
   * Desactiva un mapeo
   */
  public function desactivarMapeo($mapeoId)
  {
    try {
      $mapeo = MapeoPlanesCoberturas::findOrFail($mapeoId);
      $mapeo->update(['activo' => false]);

      return response()->json([
        'exito' => true,
        'mensaje' => 'Mapeo desactivado correctamente',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'exito' => false,
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Activa un mapeo
   */
  public function activarMapeo($mapeoId)
  {
    try {
      $mapeo = MapeoPlanesCoberturas::findOrFail($mapeoId);
      $mapeo->update(['activo' => true]);

      return response()->json([
        'exito' => true,
        'mensaje' => 'Mapeo activado correctamente',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'exito' => false,
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Consulta con JOIN entre SQL Server y base de datos local
   * Simula el JOIN haciendo las consultas por separado y combinándolas en PHP
   */
  public function consultarConMapeo(Request $request)
  {
    try {
      $idTitular = $request->input('id_titular', 110060);

      // PASO 1: Consultar datos desde SQL Server (PieveSalud)
      $datosSqlServer = DB::connection('sqlsrv_externa')
        ->table('dbo.PlanesFichas as pf')
        ->join('dbo.Beneficiarios as b', 'b.IdTitularCf', '=', 'pf.IdTitularCf')
        ->select(
          'pf.IdTitularCf',
          'b.IdBenCp',
          'pf.IdRubroCf',
          'pf.Plan'
        )
        ->where('pf.IdTitularCf', $idTitular)
        ->get();

      Log::info("Registros obtenidos de SQL Server: " . $datosSqlServer->count());

      // PASO 2: Obtener todos los mapeos activos de la base local
      $mapeos = MapeoPlanesCoberturas::activos()
        ->get(['id_rubro_origen', 'plan_origen_codigo', 'cobertura_destino', 'es_wildcard']);

      // PASO 3: Combinar los datos (hacer el JOIN en PHP)
      $resultados = $datosSqlServer->map(function ($registro) use ($mapeos) {
        // Buscar el mapeo correspondiente
        $mapeo = $mapeos->first(function ($m) use ($registro) {
          // Verificar si el rubro coincide
          if ($m->id_rubro_origen != $registro->IdRubroCf) {
            return false;
          }

          // Si es wildcard, usar LIKE
          if ($m->es_wildcard) {
            $patron = str_replace('%', '.*', preg_quote($m->plan_origen_codigo, '/'));
            return preg_match("/^{$patron}$/i", $registro->Plan);
          }

          // Si no es wildcard, comparación exacta
          return $m->plan_origen_codigo === $registro->Plan;
        });

        return [
          'IdTitularCf' => $registro->IdTitularCf,
          'IdBenCp' => $registro->IdBenCp,
          'IdRubroCf' => $registro->IdRubroCf,
          'Plan' => $registro->Plan,
          'CoberturaSEO' => $mapeo ? $mapeo->cobertura_destino : 'NO_MAPEADO',
        ];
      });

      return response()->json([
        'exito' => true,
        'total_registros' => $resultados->count(),
        'datos' => $resultados,
      ], 200);

    } catch (\Exception $e) {
      Log::error('Error en consulta con mapeo: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());

      return response()->json([
        'exito' => false,
        'error' => $e->getMessage(),
        'linea' => $e->getLine(),
      ], 500);
    }
  }
}
