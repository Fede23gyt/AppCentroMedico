<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\PrestacionController;
use App\Http\Controllers\PrestacionPlanController;
use App\Http\Controllers\PrestadorController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\RendicionController;
use App\Http\Controllers\MapeoSaludController;
use App\Http\Controllers\ReporteController;
use App\Models\Prestacion;
use App\Models\Plan;
use App\Models\Rubro;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login')->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

  Route::match(['GET', 'POST'], 'mapeo/generar', [\App\Http\Controllers\MapeoSaludController::class, 'generarMapeo'])->name('mapeo.generar');
  Route::get('mapeo/estadisticas', [\App\Http\Controllers\MapeoSaludController::class, 'obtenerEstadisticas'])->name('mapeo.estadisticas');
  Route::get('mapeo/listar', [\App\Http\Controllers\MapeoSaludController::class, 'listarMapeos'])->name('mapeo.listar');
  Route::post('mapeo/buscar', [\App\Http\Controllers\MapeoSaludController::class, 'buscarCobertura'])->name('mapeo.buscar');
  Route::match(['GET', 'POST'], 'mapeo/consultar-con-mapeo', [\App\Http\Controllers\MapeoSaludController::class, 'consultarConMapeo'])->name('mapeo.consultar-con-mapeo');
  Route::put('mapeo/{mapeoId}/desactivar', [\App\Http\Controllers\MapeoSaludController::class, 'desactivarMapeo'])->name('mapeo.desactivar');
  Route::put('mapeo/{mapeoId}/activar', [\App\Http\Controllers\MapeoSaludController::class, 'activarMapeo'])->name('mapeo.activar');



    // Perfil de usuario
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de usuarios
    Route::middleware(['permission:users.view'])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
    });

    // Rutas de roles y permisos
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Rutas de sucursales
    Route::resource('sucursales', SucursalController::class)
        ->parameters(['sucursales' => 'sucursal']);

    Route::patch('sucursales/{sucursal}/toggle-status', [SucursalController::class, 'toggleStatus'])
        ->name('sucursales.toggle-status');
    Route::get('sucursales/{sucursal}/users', [SucursalController::class, 'getUsers'])
        ->name('sucursales.users');

    // Rubros
    Route::resource('rubros', RubroController::class);
    Route::get('api/rubros', [RubroController::class, 'apiList'])->name('api.rubros');

    // Prestadores
    Route::resource('prestadores', PrestadorController::class)
        ->parameters(['prestadores' => 'prestador']);
    Route::patch('prestadores/{prestador}/toggle-status', [PrestadorController::class, 'toggleStatus'])
        ->name('prestadores.toggle-status');
    Route::get('api/prestadores', [PrestadorController::class, 'apiList'])->name('api.prestadores');

    // Ordenes
    Route::resource('ordenes', OrdenController::class)
        ->except(['edit', 'update'])
        ->parameters(['ordenes' => 'orden']);
    Route::post('ordenes/{orden}/cambiar-estado', [OrdenController::class, 'cambiarEstado'])
        ->name('ordenes.cambiar-estado');
    Route::get('ordenes/{orden}/pdf', [OrdenController::class, 'exportarPdf'])
        ->name('ordenes.pdf');

    // API de órdenes
    Route::post('api/ordenes/buscar-afiliados', [OrdenController::class, 'buscarAfiliados'])
        ->name('api.ordenes.buscar-afiliados');
    Route::post('api/ordenes/obtener-precio-prestacion', [OrdenController::class, 'obtenerPrecioPrestacion'])
        ->name('api.ordenes.obtener-precio-prestacion');

    // Rendiciones
    Route::resource('rendiciones', RendicionController::class)
        ->except(['edit', 'update'])
        ->parameters(['rendiciones' => 'rendicion']);
    Route::post('rendiciones/{rendicion}/cerrar', [RendicionController::class, 'cerrar'])
        ->name('rendiciones.cerrar');
    Route::get('rendiciones/{rendicion}/pdf', [RendicionController::class, 'exportarPdf'])
        ->name('rendiciones.pdf');

    // API de rendiciones
    Route::post('api/rendiciones/buscar-orden', [RendicionController::class, 'buscarOrden'])
        ->name('api.rendiciones.buscar-orden');
    Route::post('rendiciones/{rendicion}/agregar-orden', [RendicionController::class, 'agregarOrden'])
        ->name('rendiciones.agregar-orden');
    Route::post('rendiciones/{rendicion}/quitar-orden', [RendicionController::class, 'quitarOrden'])
        ->name('rendiciones.quitar-orden');

    // Planes
    Route::resource('planes', PlanController::class)
        ->parameters(['planes' => 'plan']);
    Route::get('api/planes', [PlanController::class, 'apiList'])->name('api.planes');

    // Prestaciones
    // Ruta para verificar código de prestación
    Route::get('prestaciones/check-codigo', [PrestacionController::class, 'checkCodigo'])
        ->name('prestaciones.check-codigo');

    Route::resource('prestaciones', PrestacionController::class)
        ->parameters(['prestaciones' => 'prestacion']);

    // ===== GESTIÓN DE PRESTACIONES-PLANES (EXISTENTES + NUEVAS) =====
    Route::prefix('prestaciones/{prestacion}')->group(function () {
        // Rutas existentes
        Route::get('planes', [PrestacionController::class, 'planes'])
            ->name('prestaciones.planes');
        Route::post('planes', [PrestacionController::class, 'asociarPlan'])
            ->name('prestaciones.asociar-plan');
        Route::delete('planes/{plan}', [PrestacionController::class, 'desasociarPlan'])
            ->name('prestaciones.desasociar-plan');
        Route::put('planes/{plan}', [PrestacionController::class, 'actualizarPlan'])
            ->name('prestaciones.actualizar-plan');

        // ===== NUEVAS RUTAS - ACCIONES MASIVAS =====
        // Copiar asociaciones desde otra prestación
        Route::post('copiar-asociaciones', [PrestacionController::class, 'copiarAsociaciones'])
            ->name('prestaciones.copiar-asociaciones');

        // Ajuste masivo de precios
        Route::patch('ajuste-masivo-precios', [PrestacionController::class, 'ajusteMasivoPrecios'])
            ->name('prestaciones.ajuste-masivo-precios');

        // Exportar configuración a Excel
        Route::get('export-excel', [PrestacionController::class, 'exportarExcel'])
            ->name('prestaciones.export-excel');

        // Importar desde Excel
        Route::post('import-excel', [PrestacionController::class, 'importarExcel'])
            ->name('prestaciones.import-excel');

        // Gestión avanzada (opcional - página separada)
        Route::get('planes/avanzado', [PrestacionController::class, 'planesAvanzado'])
            ->name('prestaciones.planes-avanzado');





    });

    // ===== GESTIÓN DE PLANES-PRESTACIONES (DESDE PLANES) =====
    Route::prefix('planes/{plan}')->group(function () {
        // Vista principal de gestión de prestaciones del plan
        Route::get('prestaciones', [PlanController::class, 'prestaciones'])
            ->name('planes.prestaciones.index');

        // Asociar prestación al plan
        Route::post('prestaciones', [PlanController::class, 'asociarPrestacion'])
            ->name('planes.prestaciones.store');

        // Actualizar asociación prestación-plan
        Route::put('prestaciones/{prestacion}', [PlanController::class, 'actualizarPrestacion'])
            ->name('planes.prestaciones.update');

        // Desasociar prestación del plan
        Route::delete('prestaciones/{prestacion}', [PlanController::class, 'desasociarPrestacion'])
            ->name('planes.prestaciones.destroy');

        // ===== ACCIONES MASIVAS =====
        // Copiar prestaciones desde otro plan
        Route::post('copiar-prestaciones', [PlanController::class, 'copiarPrestaciones'])
            ->name('planes.copiar-prestaciones');

        // Ajuste masivo de precios
        Route::patch('ajuste-masivo-precios', [PlanController::class, 'ajusteMasivoPrecios'])
            ->name('planes.ajuste-masivo-precios');

        // Exportar configuración a Excel
        Route::get('export-excel', [PlanController::class, 'exportarExcel'])
            ->name('planes.export-excel');

        // Exportar configuración a PDF
        Route::get('export-pdf', [PlanController::class, 'exportarPdf'])
            ->name('planes.export-pdf');

        // Importar desde Excel
        Route::post('import-excel', [PlanController::class, 'importarExcel'])
            ->name('planes.import-excel');

        // ===== GESTIÓN DE ITEMS REQUERIDOS =====
        // Agregar item al plan
        Route::post('items', [PlanController::class, 'agregarItem'])
            ->name('planes.items.store');

        // Eliminar item del plan
        Route::delete('items/{item}', [PlanController::class, 'eliminarItem'])
            ->name('planes.items.destroy');
    });


  });




  // API de prestaciones (existente)
    Route::get('api/prestaciones', [PrestacionController::class, 'apiList'])
        ->name('api.prestaciones');

    // ===== NUEVAS RUTAS API =====
    Route::prefix('api')->group(function () {
        // Prestaciones con sus planes
        Route::get('prestaciones-planes', [PrestacionController::class, 'apiPrestacionesConPlanes'])
            ->name('api.prestaciones-planes');

        // Planes disponibles para una prestación específica
        Route::get('prestaciones/{prestacion}/planes-disponibles', function(Prestacion $prestacion) {
            $planesAsociados = $prestacion->planes->pluck('id');
            return Plan::activos()
                ->whereNotIn('id', $planesAsociados)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'nombre_corto']);
        })->name('api.prestaciones.planes-disponibles');

        // Precios de una prestación en un plan específico
        Route::get('prestaciones/{prestacion}/planes/{plan}/precios', function(Prestacion $prestacion, Plan $plan) {
            $asociacion = $prestacion->planes()->where('plan_id', $plan->id)->first();
            if (!$asociacion) {
                return response()->json(['error' => 'Asociación no encontrada'], 404);
            }

            return response()->json([
                'valor_afiliado' => $asociacion->pivot->valor_afiliado,
                'valor_particular' => $asociacion->pivot->valor_particular,
                'cant_max_individual' => $asociacion->pivot->cant_max_individual,
                'cant_max_grupo' => $asociacion->pivot->cant_max_grupo,
                'estado' => $asociacion->pivot->estado,
                'vigencia_desde' => $asociacion->pivot->fecha_vigencia_desde,
                'vigencia_hasta' => $asociacion->pivot->fecha_vigencia_hasta,
                'observaciones' => $asociacion->pivot->observaciones
            ]);
        })->name('api.prestaciones.precios-plan');

        // Buscar prestaciones para autocompletar
        Route::get('prestaciones/search', function(Request $request) {
            return Prestacion::with('rubro:id,nombre')
                ->when($request->search, function($query, $search) {
                    $query->where('codigo', 'like', "%{$search}%")
                          ->orWhere('nombre', 'like', "%{$search}%");
                })
                ->activas()
                ->orderBy('codigo')
                ->limit(10)
                ->get(['id', 'codigo', 'nombre', 'rubro_id', 'precio_general']);
        })->name('api.prestaciones.search');

        // Buscar prestaciones para órdenes (con plan específico)
        Route::get('prestaciones/buscar', function(Request $request) {
            $query = Prestacion::query();

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('codigo', 'like', "%{$request->search}%")
                      ->orWhere('descripcion', 'like', "%{$request->search}%");
                });
            }

            if ($request->filled('plan_id')) {
                $query->whereHas('planes', function($q) use ($request) {
                    $q->where('plan_id', $request->plan_id)
                      ->where('prestaciones_planes.estado', 'activo');
                });
            }

            if ($request->filled('activas')) {
                $query->where('estado', 'activo');
            }

            return $query->orderBy('codigo')
                ->limit(20)
                ->get()
                ->map(function($prestacion) use ($request) {
                    $precio = null;
                    if ($request->filled('plan_id')) {
                        $asociacion = $prestacion->planes()
                            ->where('plan_id', $request->plan_id)
                            ->first();
                        $precio = $asociacion?->pivot->precio;
                    }
                    return [
                        'id' => $prestacion->id,
                        'codigo' => $prestacion->codigo,
                        'descripcion' => $prestacion->descripcion,
                        'precio_plan' => $precio ?? $prestacion->precio_general,
                    ];
                });
        })->name('api.prestaciones.buscar');

        // Estadísticas de prestaciones por plan
        Route::get('planes/{plan}/prestaciones/estadisticas', function(Plan $plan) {
            $stats = [
                'total_prestaciones' => $plan->prestaciones()->count(),
                'prestaciones_activas' => $plan->prestaciones()->wherePivot('estado', 'activo')->count(),
                'promedio_valor_afiliado' => $plan->prestaciones()->avg('prestaciones_planes.valor_afiliado'),
                'promedio_valor_particular' => $plan->prestaciones()->avg('prestaciones_planes.valor_particular'),
                'por_rubro' => $plan->prestaciones()
                    ->join('rubros', 'prestaciones.rubro_id', '=', 'rubros.id')
                    ->groupBy('rubros.id', 'rubros.nombre')
                    ->selectRaw('rubros.nombre, count(*) as cantidad')
                    ->pluck('cantidad', 'nombre')
            ];

            return response()->json($stats);
        })->name('api.planes.prestaciones.estadisticas');

        // Siguiente código disponible para prestaciones
        Route::get('prestaciones/siguiente-codigo', function() {
            $ultimo = Prestacion::orderBy('codigo', 'desc')->first();
            if (!$ultimo) {
                return response()->json(['codigo' => '000001']);
            }
            $siguienteNumero = intval($ultimo->codigo) + 1;
            return response()->json(['codigo' => str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT)]);
        })->name('api.prestaciones.siguiente-codigo');
    });

    // ===== RUTAS DE REPORTES =====
    Route::prefix('reportes')->middleware(['permission:reportes.view'])->group(function () {
        Route::get('ordenes', [ReporteController::class, 'ordenes'])->name('reportes.ordenes');
        Route::get('prestaciones', [ReporteController::class, 'prestaciones'])->name('reportes.prestaciones');
        Route::get('sucursales', [ReporteController::class, 'sucursales'])->name('reportes.sucursales');
        Route::get('cajas', [ReporteController::class, 'cajas'])->name('reportes.cajas');
        Route::get('usuarios', [ReporteController::class, 'usuarios'])->name('reportes.usuarios');
        Route::get('planes', [ReporteController::class, 'planes'])->name('reportes.planes');
        Route::get('ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    });


    // ===== NUEVAS RUTAS API PARA PLANES =====
    Route::prefix('api/planes')->group(function () {
        // Prestaciones disponibles para un plan específico
        Route::get('{plan}/prestaciones-disponibles', [PlanController::class, 'apiPrestacionesDisponibles'])
            ->name('api.planes.prestaciones-disponibles');

        // Estadísticas de prestaciones de un plan
        Route::get('{plan}/prestaciones/estadisticas', [PlanController::class, 'apiEstadisticasPrestaciones'])
            ->name('api.planes.prestaciones.estadisticas');

        // Precios de una prestación en el plan
        Route::get('{plan}/prestaciones/{prestacion}/precios', [PlanController::class, 'apiPreciosPrestacion'])
            ->name('api.planes.prestaciones.precios');

        // Buscar planes para autocompletar
        Route::get('search', [PlanController::class, 'apiSearch'])
            ->name('api.planes.search');
    });

