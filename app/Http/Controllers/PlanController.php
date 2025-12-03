<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Prestacion;
use App\Models\Rubro;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PlanPrestacionesExport;
use App\Imports\PlanPrestacionesImport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $planes = Plan::query()
            ->when($request->search, function($query, $search) {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('nombre_corto', 'like', "%{$search}%");
            })
            ->when($request->estado, function($query, $estado) {
                $query->where('estado', $estado);
            })
            ->withCount('prestaciones')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Planes/Index', [
            'planes' => $planes,
            'filters' => $request->only(['search', 'estado'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Planes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'nombre_corto' => 'required|string|max:20|unique:planes',
            'vigencia_desde' => 'required|date',
            'vigencia_hasta' => 'nullable|date|after:vigencia_desde',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'descripcion' => 'nullable|string',
            'porc_salud' => 'nullable|numeric|min:0|max:100',
            'porc_odo' => 'nullable|numeric|min:0|max:100',
            'porc_ord' => 'nullable|numeric|min:0|max:100'
        ]);

        Plan::create($validated);

        return redirect()->route('planes.index')
            ->with('success', 'Plan creado exitosamente');
    }

    public function show(Plan $plan)
    {
        $plan->load([
            'prestaciones.rubro',
            'prestaciones' => function($query) {
                $query->orderBy('codigo');
            },
            'sucursales' => function($query) {
                $query->orderBy('nombre');
            },
            'itemsRequeridos' => function($query) {
                $query->orderBy('grupo')->orderBy('item_codigo');
            }
        ]);

        return Inertia::render('Planes/Show', [
            'plan' => $plan->toArray()
        ]);
    }

    public function edit(Plan $plan)
    {
        $plan->load([
            'sucursales' => function($query) {
                $query->orderBy('nombre');
            },
            'itemsRequeridos' => function($query) {
                $query->orderBy('grupo')->orderBy('item_codigo');
            }
        ]);

        $sucursalesDisponibles = Sucursal::where('is_active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        // Formatear el plan para el frontend
        $planData = $plan->toArray();

        // Asegurar que las fechas estén en formato correcto para input type="date"
        if ($planData['vigencia_desde']) {
            $planData['vigencia_desde'] = date('Y-m-d', strtotime($planData['vigencia_desde']));
        }
        if ($planData['vigencia_hasta']) {
            $planData['vigencia_hasta'] = date('Y-m-d', strtotime($planData['vigencia_hasta']));
        }

        return Inertia::render('Planes/Edit', [
            'plan' => $planData,
            'sucursalesDisponibles' => $sucursalesDisponibles
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'nombre_corto' => 'required|string|max:20|unique:planes,nombre_corto,' . $plan->id,
            'vigencia_desde' => 'required|date',
            'vigencia_hasta' => 'nullable|date|after:vigencia_desde',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'descripcion' => 'nullable|string',
            'porc_salud' => 'nullable|numeric|min:0|max:100',
            'porc_odo' => 'nullable|numeric|min:0|max:100',
            'porc_ord' => 'nullable|numeric|min:0|max:100',
            'sucursales' => 'nullable|array',
            'sucursales.*' => 'exists:sucursales,id',
            'items' => 'nullable|array',
            'items.*' => 'string|max:50'
        ]);

        $plan->update($validated);

        // Sincronizar sucursales
        if (isset($validated['sucursales'])) {
            $syncData = [];
            foreach ($validated['sucursales'] as $sucursalId) {
                $syncData[$sucursalId] = [
                    'estado' => 'activo',
                    'fecha_desde' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            $plan->sucursales()->sync($syncData);
        }

        return redirect()->route('planes.index')
            ->with('success', 'Plan actualizado exitosamente');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->prestaciones()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el plan porque tiene prestaciones asociadas');
        }

        $plan->delete();

        return redirect()->route('planes.index')
            ->with('success', 'Plan eliminado exitosamente');
    }


    /**
     * Vista principal de gestión de prestaciones del plan
     */
    public function prestaciones(Plan $plan)
    {
        $plan->load([
            'prestaciones.rubro',
            'prestaciones' => function($query) {
                $query->orderBy('codigo');
            }
        ]);

        $prestacionesDisponibles = Prestacion::with('rubro')
            ->whereNotIn('id', $plan->prestaciones->pluck('id'))
            ->where('estado', 'activo')
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'rubro_id', 'precio_general']);

        $rubros = Rubro::where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $planesParaCopiar = Plan::where('id', '!=', $plan->id)
            ->where('estado', 'activo')
            ->withCount('prestaciones')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nombre_corto']);

        return Inertia::render('Planes/Prestaciones/Index', [
            'plan' => $plan,
            'prestacionesDisponibles' => $prestacionesDisponibles,
            'rubros' => $rubros,
            'planesParaCopiar' => $planesParaCopiar
        ]);
    }

    /**
     * Asociar prestación al plan
     */
    public function asociarPrestacion(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'prestacion_id' => 'required|exists:prestaciones,id',
            'valor_afiliado' => 'required|numeric|min:0',
            'valor_particular' => 'required|numeric|min:0',
            'cant_max_individual' => 'nullable|integer|min:0',
            'cant_max_grupo' => 'nullable|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after:fecha_desde',
            'observaciones' => 'nullable|string|max:500'
        ]);

        // Verificar que la prestación no esté ya asociada
        if ($plan->prestaciones()->where('prestacion_id', $validated['prestacion_id'])->exists()) {
            return redirect()->back()
                ->with('error', 'La prestación ya está asociada a este plan');
        }

        $plan->prestaciones()->attach($validated['prestacion_id'], [
            'valor_afiliado' => $validated['valor_afiliado'],
            'valor_particular' => $validated['valor_particular'],
            'cant_max_individual' => $validated['cant_max_individual'],
            'cant_max_grupo' => $validated['cant_max_grupo'],
            'estado' => $validated['estado'],
            'fecha_desde' => $validated['fecha_desde'],
            'fecha_hasta' => $validated['fecha_hasta'],
            'observaciones' => $validated['observaciones'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Prestación asociada exitosamente al plan');
    }

    /**
     * Actualizar asociación prestación-plan
     */
    public function actualizarPrestacion(Request $request, Plan $plan, Prestacion $prestacion)
    {
        $validated = $request->validate([
            'valor_afiliado' => 'required|numeric|min:0',
            'valor_particular' => 'required|numeric|min:0',
            'cant_max_individual' => 'nullable|integer|min:0',
            'cant_max_grupo' => 'nullable|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after:fecha_desde',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $plan->prestaciones()->updateExistingPivot($prestacion->id, [
            'valor_afiliado' => $validated['valor_afiliado'],
            'valor_particular' => $validated['valor_particular'],
            'cant_max_individual' => $validated['cant_max_individual'],
            'cant_max_grupo' => $validated['cant_max_grupo'],
            'estado' => $validated['estado'],
            'fecha_desde' => $validated['fecha_desde'],
            'fecha_hasta' => $validated['fecha_hasta'],
            'observaciones' => $validated['observaciones'],
            'updated_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Asociación actualizada exitosamente');
    }

    /**
     * Desasociar prestación del plan
     */
    public function desasociarPrestacion(Plan $plan, Prestacion $prestacion)
    {
        $plan->prestaciones()->detach($prestacion->id);

        return redirect()->back()
            ->with('success', 'Prestación desasociada del plan exitosamente');
    }

    /**
     * Copiar prestaciones desde otro plan
     */
    public function copiarPrestaciones(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'plan_origen_id' => 'required|exists:planes,id',
            'incluir_precios' => 'boolean',
            'solo_activas' => 'boolean',
            'sobrescribir_existentes' => 'boolean'
        ]);

        $planOrigen = Plan::with(['prestaciones' => function($query) use ($validated) {
            if ($validated['solo_activas']) {
                $query->wherePivot('estado', 'activo');
            }
        }])->findOrFail($validated['plan_origen_id']);

        if ($planOrigen->prestaciones->isEmpty()) {
            return redirect()->back()
                ->with('error', 'El plan origen no tiene prestaciones para copiar');
        }

        $copiadas = 0;
        $actualizadas = 0;

        DB::transaction(function() use ($plan, $planOrigen, $validated, &$copiadas, &$actualizadas) {
            foreach ($planOrigen->prestaciones as $prestacion) {
                $existe = $plan->prestaciones()->where('prestacion_id', $prestacion->id)->exists();

                if ($existe && !$validated['sobrescribir_existentes']) {
                    continue;
                }

                $datosAsociacion = [
                    'valor_afiliado' => $validated['incluir_precios']
                        ? $prestacion->pivot->valor_afiliado
                        : $prestacion->precio_general,
                    'valor_particular' => $validated['incluir_precios']
                        ? $prestacion->pivot->valor_particular
                        : $prestacion->precio_general,
                    'cant_max_individual' => $prestacion->pivot->cant_max_individual,
                    'cant_max_grupo' => $prestacion->pivot->cant_max_grupo,
                    'estado' => $prestacion->pivot->estado,
                    'fecha_desde' => $prestacion->pivot->fecha_desde,
                    'fecha_hasta' => $prestacion->pivot->fecha_hasta,
                    'observaciones' => $prestacion->pivot->observaciones,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if ($existe) {
                    $plan->prestaciones()->updateExistingPivot($prestacion->id, $datosAsociacion);
                    $actualizadas++;
                } else {
                    $plan->prestaciones()->attach($prestacion->id, $datosAsociacion);
                    $copiadas++;
                }
            }
        });

        $mensaje = "Operación completada: {$copiadas} prestaciones copiadas";
        if ($actualizadas > 0) {
            $mensaje .= ", {$actualizadas} actualizadas";
        }

        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Ajuste masivo de precios
     */
    public function ajusteMasivoPrecios(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'tipo_ajuste' => 'required|in:porcentaje,monto_fijo',
            'valor_ajuste' => 'required|numeric',
            'aplicar_a' => 'required|in:ambos,afiliado,particular',
            'solo_activas' => 'boolean',
            'rubro_id' => 'nullable|exists:rubros,id'
        ]);

        $query = $plan->prestaciones();

        if ($validated['solo_activas']) {
            $query->wherePivot('estado', 'activo');
        }

        if ($validated['rubro_id']) {
            $query->where('prestaciones.rubro_id', $validated['rubro_id']);
        }

        $prestaciones = $query->get();

        if ($prestaciones->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No se encontraron prestaciones para ajustar');
        }

        $actualizadas = 0;

        DB::transaction(function() use ($plan, $prestaciones, $validated, &$actualizadas) {
            foreach ($prestaciones as $prestacion) {
                $valorAfiliado = $prestacion->pivot->valor_afiliado;
                $valorParticular = $prestacion->pivot->valor_particular;

                if ($validated['aplicar_a'] === 'ambos' || $validated['aplicar_a'] === 'afiliado') {
                    if ($validated['tipo_ajuste'] === 'porcentaje') {
                        $valorAfiliado = $valorAfiliado * (1 + $validated['valor_ajuste'] / 100);
                    } else {
                        $valorAfiliado = $valorAfiliado + $validated['valor_ajuste'];
                    }
                }

                if ($validated['aplicar_a'] === 'ambos' || $validated['aplicar_a'] === 'particular') {
                    if ($validated['tipo_ajuste'] === 'porcentaje') {
                        $valorParticular = $valorParticular * (1 + $validated['valor_ajuste'] / 100);
                    } else {
                        $valorParticular = $valorParticular + $validated['valor_ajuste'];
                    }
                }

                $plan->prestaciones()->updateExistingPivot($prestacion->id, [
                    'valor_afiliado' => round($valorAfiliado, 2),
                    'valor_particular' => round($valorParticular, 2),
                    'updated_at' => now()
                ]);

                $actualizadas++;
            }
        });

        return redirect()->back()
            ->with('success', "Precios actualizados en {$actualizadas} prestaciones");
    }

    /**
     * Exportar configuración a Excel
     */
    public function exportarExcel(Plan $plan)
    {
        $plan->load(['prestaciones.rubro']);

        return Excel::download(
            new PlanPrestacionesExport($plan),
            "plan_{$plan->nombre_corto}_prestaciones.xlsx"
        );
    }

    /**
     * Importar desde Excel
     */
    public function importarExcel(Request $request, Plan $plan)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
            'sobrescribir_existentes' => 'boolean'
        ]);

        try {
            $import = new PlanPrestacionesImport($plan, $request->boolean('sobrescribir_existentes'));
            Excel::import($import, $request->file('archivo'));

            return redirect()->back()
                ->with('success', "Importación completada: {$import->getImportadas()} prestaciones procesadas");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al importar archivo: ' . $e->getMessage());
        }
    }

// ===== MÉTODOS API =====

    /**
     * Prestaciones disponibles para asociar al plan
     */
    public function apiPrestacionesDisponibles(Plan $plan)
    {
        $prestacionesAsociadas = $plan->prestaciones->pluck('id');

        return Prestacion::with('rubro:id,nombre')
            ->whereNotIn('id', $prestacionesAsociadas)
            ->where('estado', 'activo')
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'rubro_id', 'precio_general']);
    }

    /**
     * Estadísticas de prestaciones del plan
     */
    public function apiEstadisticasPrestaciones(Plan $plan)
    {
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
    }

    /**
     * Precios de una prestación en el plan
     */
    public function apiPreciosPrestacion(Plan $plan, Prestacion $prestacion)
    {
        $asociacion = $plan->prestaciones()->where('prestacion_id', $prestacion->id)->first();

        if (!$asociacion) {
            return response()->json(['error' => 'Asociación no encontrada'], 404);
        }

        return response()->json([
            'valor_afiliado' => $asociacion->pivot->valor_afiliado,
            'valor_particular' => $asociacion->pivot->valor_particular,
            'cant_max_individual' => $asociacion->pivot->cant_max_individual,
            'cant_max_grupo' => $asociacion->pivot->cant_max_grupo,
            'estado' => $asociacion->pivot->estado,
            'fecha_desde' => $asociacion->pivot->fecha_desde,
            'fecha_hasta' => $asociacion->pivot->fecha_hasta,
            'observaciones' => $asociacion->pivot->observaciones
        ]);
    }

    /**
     * Buscar planes para autocompletar
     */
    public function apiSearch(Request $request)
    {
        return Plan::when($request->search, function($query, $search) {
            $query->where('nombre', 'like', "%{$search}%")
                ->orWhere('nombre_corto', 'like', "%{$search}%");
        })
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'nombre_corto']);
    }

    public function apiList()
    {
        return Plan::activos()->vigentes()->orderBy('nombre')->get(['id', 'nombre', 'nombre_corto']);
    }

    public function exportarPdf(Plan $plan)
    {
        $plan->load(['prestaciones' => function ($query) {
            $query->with('rubro')->orderBy('codigo');
        }]);

        $pdf = \PDF::loadView('pdf.plan-prestaciones', [
            'plan' => $plan,
            'fecha' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->download('plan-' . $plan->nombre_corto . '-prestaciones.pdf');
    }

    /**
     * Agregar item requerido al plan
     */
    public function agregarItem(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'item_codigo' => 'required|string|max:50',
            'es_obligatorio' => 'boolean',
            'grupo' => 'required|string|max:100',
            'descripcion_grupo' => 'nullable|string'
        ]);

        // Verificar si el item ya existe en el mismo grupo
        $existe = $plan->itemsRequeridos()
            ->where('item_codigo', strtoupper($validated['item_codigo']))
            ->where('grupo', $validated['grupo'])
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->with('error', 'El item ya está asociado a esta combinación');
        }

        $plan->itemsRequeridos()->create([
            'item_codigo' => strtoupper($validated['item_codigo']),
            'es_obligatorio' => $validated['es_obligatorio'] ?? true,
            'grupo' => $validated['grupo'],
            'descripcion_grupo' => $validated['descripcion_grupo']
        ]);

        return redirect()->back()
            ->with('success', 'Item agregado exitosamente a la combinación');
    }

    /**
     * Eliminar item requerido del plan
     */
    public function eliminarItem(Plan $plan, $itemId)
    {
        $item = $plan->itemsRequeridos()->find($itemId);

        if (!$item) {
            return redirect()->back()
                ->with('error', 'Item no encontrado');
        }

        $item->delete();

        return redirect()->back()
            ->with('success', 'Item eliminado del plan exitosamente');
    }
}
