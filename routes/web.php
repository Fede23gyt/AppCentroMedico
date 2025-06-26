<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\PrestacionController;
use App\Http\Controllers\PrestacionPlanController;
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

    // Rutas de usuarios
    Route::middleware(['permission:users.view'])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
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

    // Planes
    Route::resource('planes', PlanController::class);
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

    // ===== RUTAS DE REPORTES (OPCIONALES) =====
    Route::prefix('reportes')->group(function () {
        // Reporte de cobertura por plan
        Route::get('cobertura-planes', function() {
            $planes = Plan::with(['prestaciones.rubro'])
                ->activos()
                ->get()
                ->map(function($plan) {
                    return [
                        'plan' => $plan->nombre,
                        'codigo' => $plan->nombre_corto,
                        'total_prestaciones' => $plan->prestaciones->count(),
                        'prestaciones_por_rubro' => $plan->prestaciones
                            ->groupBy('rubro.nombre')
                            ->map->count(),
                        'promedio_afiliado' => $plan->prestaciones->avg('pivot.valor_afiliado'),
                        'promedio_particular' => $plan->prestaciones->avg('pivot.valor_particular')
                    ];
                });

            return Inertia::render('Reportes/CoberturaPlan', [
                'planes' => $planes
            ]);
        })->name('reportes.cobertura-planes');

        // Reporte de prestaciones sin cobertura
        Route::get('prestaciones-sin-cobertura', function() {
            $prestacionesSinCobertura = Prestacion::doesntHave('planes')
                ->with('rubro')
                ->where('estado', 'activo')
                ->orderBy('codigo')
                ->get();

            return Inertia::render('Reportes/PrestacionesSinCobertura', [
                'prestaciones' => $prestacionesSinCobertura
            ]);
        })->name('reportes.prestaciones-sin-cobertura');

        // Reporte de matriz prestaciones-planes
        Route::get('matriz-prestaciones-planes', function(Request $request) {
            $planes = Plan::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre', 'nombre_corto']);
            $prestaciones = Prestacion::with(['planes', 'rubro'])
                ->when($request->rubro_id, function($query, $rubroId) {
                    $query->where('rubro_id', $rubroId);
                })
                ->where('estado', 'activo')
                ->orderBy('codigo')
                ->paginate(50);

            $rubros = Rubro::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);

            return Inertia::render('Reportes/MatrizPrestacionesPlan', [
                'planes' => $planes,
                'prestaciones' => $prestaciones,
                'rubros' => $rubros,
                'filtros' => $request->only(['rubro_id'])
            ]);
        })->name('reportes.matriz-prestaciones-planes');
    });
});
