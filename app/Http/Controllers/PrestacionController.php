<?php

namespace App\Http\Controllers;

use App\Models\Prestacion;
use App\Models\Rubro;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestacionController extends Controller
{
    public function index(Request $request)
    {
        $prestaciones = Prestacion::query()
            ->with('rubro')
            ->when($request->search, function($query, $search) {
                $query->buscar($search);
            })
            ->when($request->rubro_id, function($query, $rubroId) {
                $query->porRubro($rubroId);
            })
            ->when($request->estado, function($query, $estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('codigo')
            ->paginate(20)
            ->withQueryString();

        $rubros = Rubro::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Prestaciones/Index', [
            'prestaciones' => $prestaciones,
            'rubros' => $rubros,
            'filters' => $request->only(['search', 'rubro_id', 'estado'])
        ]);
    }

    public function create()
    {
        $rubros = Rubro::activos()->orderBy('nombre')->get(['id', 'nombre']);
        $siguienteCodigo = Prestacion::siguienteCodigo();

        return Inertia::render('Prestaciones/Create', [
            'rubros' => $rubros,
            'siguienteCodigo' => $siguienteCodigo
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|size:6|regex:/^\d{6}$/|unique:prestaciones',
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'rubro_id' => 'required|exists:rubros,id',
            'precio_general' => 'required|numeric|min:0',
            'valor_ips' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string'
        ]);

        Prestacion::create($validated);

        return redirect()->route('prestaciones.index')
            ->with('success', 'Prestación creada exitosamente');
    }

    public function show(Prestacion $prestacion)
    {
        $prestacion->load([
            'rubro',
            'planes' => function($query) {
                $query->orderBy('nombre');
            }
        ]);

        return Inertia::render('Prestaciones/Show', [
            'prestacion' => $prestacion
        ]);
    }

    public function edit(Prestacion $prestacion)
    {
        $rubros = Rubro::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Prestaciones/Edit', [
            'prestacion' => $prestacion,
            'rubros' => $rubros
        ]);
    }

    public function update(Request $request, Prestacion $prestacion)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|size:6|regex:/^\d{6}$/|unique:prestaciones,codigo,' . $prestacion->id,
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'rubro_id' => 'required|exists:rubros,id',
            'precio_general' => 'required|numeric|min:0',
            'valor_ips' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string'
        ]);

        $prestacion->update($validated);

        return redirect()->route('prestaciones.index')
            ->with('success', 'Prestación actualizada exitosamente');
    }

    public function destroy(Prestacion $prestacion)
    {
        if ($prestacion->planes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar la prestación porque está asociada a planes');
        }

        $prestacion->delete();

        return redirect()->route('prestaciones.index')
            ->with('success', 'Prestación eliminada exitosamente');
    }
    /**
 * Actualizar la asociación entre prestación y plan
 */
public function actualizarPlan(Request $request, Prestacion $prestacion, Plan $plan)
{
    $validated = $request->validate([
        'valor_afiliado' => 'required|numeric|min:0',
        'valor_particular' => 'required|numeric|min:0',
        'cant_max_individual' => 'nullable|integer|min:1',
        'cant_max_grupo' => 'nullable|integer|min:1',
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'nullable|date|after:fecha_desde',
        'estado' => 'required|in:activo,inactivo,suspendido',
        'observaciones' => 'nullable|string|max:1000'
    ]);

    // Verificar que existe la asociación
    $asociacion = $prestacion->planes()->where('plan_id', $plan->id)->first();
    if (!$asociacion) {
        return redirect()->back()
            ->with('error', 'La asociación no existe');
    }

    // Obtener valores anteriores para auditoría
    $valoresAnteriores = [
        'valor_afiliado' => $asociacion->pivot->valor_afiliado,
        'valor_particular' => $asociacion->pivot->valor_particular,
        'estado' => $asociacion->pivot->estado
    ];

    // Actualizar la asociación
    $prestacion->planes()->updateExistingPivot($plan->id, array_merge($validated, [
        'updated_at' => now()
    ]));

    // Registrar evento de auditoría (opcional)
    // activity()
    //     ->performedOn($prestacion)
    //     ->withProperties([
    //         'plan_id' => $plan->id,
    //         'plan_nombre' => $plan->nombre,
    //         'valores_anteriores' => $valoresAnteriores,
    //         'valores_nuevos' => [
    //             'afiliado' => $validated['valor_afiliado'],
    //             'particular' => $validated['valor_particular'],
    //             'estado' => $validated['estado']
    //         ]
    //     ])
    //     ->log('prestacion_plan_actualizada');

    return redirect()->back()
        ->with('success', "Asociación con plan \"{$plan->nombre}\" actualizada exitosamente");
}

/**
 * Asociar una prestación a un plan
 */
public function asociarPlan(Request $request, Prestacion $prestacion)
{
    $validated = $request->validate([
        'plan_id' => 'required|exists:planes,id',
        'valor_afiliado' => 'required|numeric|min:0',
        'valor_particular' => 'required|numeric|min:0',
        'cant_max_individual' => 'nullable|integer|min:1',
        'cant_max_grupo' => 'nullable|integer|min:1',
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'nullable|date|after:fecha_desde',
        'estado' => 'required|in:activo,inactivo,suspendido',
        'observaciones' => 'nullable|string|max:1000'
    ]);

    // Verificar que no exista ya la asociación
    if ($prestacion->planes()->where('plan_id', $validated['plan_id'])->exists()) {
        return redirect()->back()
            ->with('error', 'La prestación ya está asociada a este plan');
    }

    // Verificar que el plan esté activo
    $plan = Plan::find($validated['plan_id']);
    if (!$plan || $plan->estado !== 'activo') {
        return redirect()->back()
            ->with('error', 'El plan seleccionado no está disponible');
    }

    // Crear la asociación
    $prestacion->planes()->attach($validated['plan_id'], [
        'valor_afiliado' => $validated['valor_afiliado'],
        'valor_particular' => $validated['valor_particular'],
        'cant_max_individual' => $validated['cant_max_individual'],
        'cant_max_grupo' => $validated['cant_max_grupo'],
        'fecha_desde' => $validated['fecha_desde'],
        'fecha_hasta' => $validated['fecha_hasta'],
        'estado' => $validated['estado'],
        'observaciones' => $validated['observaciones'],
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return redirect()->back()
        ->with('success', "Plan \"{$plan->nombre}\" asociado exitosamente");
}

/**
 * Desasociar una prestación de un plan
 */
public function desasociarPlan(Prestacion $prestacion, Plan $plan)
{
    // Verificar que existe la asociación
    $asociacion = $prestacion->planes()->where('plan_id', $plan->id)->first();
    if (!$asociacion) {
        return redirect()->back()
            ->with('error', 'La asociación no existe');
    }

    // Eliminar la asociación
    $prestacion->planes()->detach($plan->id);

    return redirect()->back()
        ->with('success', "Plan \"{$plan->nombre}\" desasociado exitosamente");
}


/**
 * Mostrar la vista de gestión de planes para una prestación
 */
public function planes(Prestacion $prestacion)
{
    // Cargar todas las relaciones necesarias
    $prestacion->load([
        'rubro:id,nombre',
        'planes' => function($query) {
            $query->select('planes.id', 'planes.nombre', 'planes.nombre_corto')
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
                  ->orderBy('planes.nombre');
        }
    ]);

    // Convertir la colección de planes a array con estructura consistente
    // Mapear los nombres de campos de la BD a los del frontend
    $planesAsociados = $prestacion->planes->map(function($plan) {
        return [
            'plan_id' => $plan->id, // ID del plan para las rutas
            'plan' => [
                'id' => $plan->id,
                'nombre' => $plan->nombre,
                'nombre_corto' => $plan->nombre_corto
            ],
            'valor_afiliado' => (float) $plan->pivot->valor_afiliado,
            'valor_particular' => (float) $plan->pivot->valor_particular,
            'cant_max_individual' => $plan->pivot->cant_max_individual,
            'cant_max_grupo' => $plan->pivot->cant_max_grupo,
            'estado' => $plan->pivot->estado,
            // Mapear los nombres de campos de BD a frontend
            'fecha_desde' => $plan->pivot->fecha__desde,
            'fecha_hasta' => $plan->pivot->fecha_hasta,
            'observaciones' => $plan->pivot->observaciones
        ];
    })->toArray();

    // Reemplazar la colección original con el array mapeado
    $prestacion->setAttribute('planes', $planesAsociados);

    // Obtener planes disponibles (que no estén ya asociados)
    $planesAsociadosIds = collect($planesAsociados)->pluck('plan_id');
    $planesDisponibles = Plan::where('estado', 'activo')
        ->whereNotIn('id', $planesAsociadosIds)
        ->orderBy('nombre')
        ->get(['id', 'nombre', 'nombre_corto']);

    return Inertia::render('Prestaciones/Planes', [
        'prestacion' => $prestacion,
        'planesDisponibles' => $planesDisponibles
    ]);
}


    /**
    * Obtener prestaciones con sus planes para API
    */
public function apiPrestacionesConPlanes(Request $request)
    {
        $query = Prestacion::with(['rubro', 'planes:id,nombre,nombre_corto']);

        if ($request->rubro_id) {
            $query->where('rubro_id', $request->rubro_id);
        }

        if ($request->plan_id) {
            $query->whereHas('planes', function($q) use ($request) {
                $q->where('planes.id', $request->plan_id);
            });
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->search}%")
                ->orWhere('nombre', 'like', "%{$request->search}%");
            });
        }

        $prestaciones = $query->activas()
            ->orderBy('codigo')
            ->paginate($request->per_page ?? 15);

        return response()->json($prestaciones);
    }

    /**
 * Copiar asociaciones de una prestación a otra
 */
public function copiarAsociaciones(Request $request, Prestacion $prestacionOrigen)
{
    $validated = $request->validate([
        'prestacion_destino_id' => 'required|exists:prestaciones,id',
        'incluir_precios' => 'boolean',
        'incluir_limites' => 'boolean',
        'aplicar_factor' => 'nullable|numeric|min:0.1|max:10'
    ]);

    $prestacionDestino = Prestacion::find($validated['prestacion_destino_id']);

    if ($prestacionOrigen->id === $prestacionDestino->id) {
        return redirect()->back()
            ->with('error', 'No se puede copiar a la misma prestación');
    }

    $asociacionesCopiadas = 0;
    $factor = $validated['aplicar_factor'] ?? 1;

    foreach ($prestacionOrigen->planes as $plan) {
        // Verificar si ya existe la asociación
        if ($prestacionDestino->planes()->where('plan_id', $plan->id)->exists()) {
            continue;
        }

        $datosAsociacion = [
            'fecha_desde' => $plan->pivot->fecha_desde,
            'fecha_hasta' => $plan->pivot->fecha_hasta,
            'estado' => $plan->pivot->estado,
            'observaciones' => $plan->pivot->observaciones . ' (Copiado)',
            'created_at' => now(),
            'updated_at' => now()
        ];

        if ($validated['incluir_precios']) {
            $datosAsociacion['valor_afiliado'] = round($plan->pivot->valor_afiliado * $factor, 2);
            $datosAsociacion['valor_particular'] = round($plan->pivot->valor_particular * $factor, 2);
        } else {
            // Usar precios base de la prestación destino
            $datosAsociacion['valor_afiliado'] = round($prestacionDestino->precio_general * 0.2, 2);
            $datosAsociacion['valor_particular'] = round($prestacionDestino->precio_general * 0.6, 2);
        }

        if ($validated['incluir_limites']) {
            $datosAsociacion['cant_max_individual'] = $plan->pivot->cant_max_individual;
            $datosAsociacion['cant_max_grupo'] = $plan->pivot->cant_max_grupo;
        }

        $prestacionDestino->planes()->attach($plan->id, $datosAsociacion);
        $asociacionesCopiadas++;
    }

    // Registrar evento de auditoría
    activity()
        ->performedOn($prestacionDestino)
        ->withProperties([
            'prestacion_origen_id' => $prestacionOrigen->id,
            'prestacion_origen_codigo' => $prestacionOrigen->codigo,
            'asociaciones_copiadas' => $asociacionesCopiadas,
            'opciones' => $validated
        ])
        ->log('prestacion_asociaciones_copiadas');

    return redirect()->back()
        ->with('success', "Se copiaron {$asociacionesCopiadas} asociaciones exitosamente");
}

/**
 * Aplicar ajuste masivo de precios
 */
public function ajusteMasivoPrecios(Request $request, Prestacion $prestacion)
{
    $validated = $request->validate([
        'tipo_ajuste' => 'required|in:porcentaje,monto_fijo,precio_base',
        'valor_ajuste' => 'required|numeric',
        'aplicar_a' => 'required|in:afiliado,particular,ambos',
        'planes_seleccionados' => 'array',
        'planes_seleccionados.*' => 'exists:planes,id'
    ]);

    $planesIds = $validated['planes_seleccionados'] ??
                 $prestacion->planes->pluck('id')->toArray();

    $actualizados = 0;

    foreach ($planesIds as $planId) {
        $asociacion = $prestacion->planes()->where('plan_id', $planId)->first();
        if (!$asociacion) continue;

        $nuevosValores = [];

        switch ($validated['tipo_ajuste']) {
            case 'porcentaje':
                $factor = 1 + ($validated['valor_ajuste'] / 100);
                if (in_array($validated['aplicar_a'], ['afiliado', 'ambos'])) {
                    $nuevosValores['valor_afiliado'] = round($asociacion->pivot->valor_afiliado * $factor, 2);
                }
                if (in_array($validated['aplicar_a'], ['particular', 'ambos'])) {
                    $nuevosValores['valor_particular'] = round($asociacion->pivot->valor_particular * $factor, 2);
                }
                break;

            case 'monto_fijo':
                if (in_array($validated['aplicar_a'], ['afiliado', 'ambos'])) {
                    $nuevosValores['valor_afiliado'] = max(0, $asociacion->pivot->valor_afiliado + $validated['valor_ajuste']);
                }
                if (in_array($validated['aplicar_a'], ['particular', 'ambos'])) {
                    $nuevosValores['valor_particular'] = max(0, $asociacion->pivot->valor_particular + $validated['valor_ajuste']);
                }
                break;

            case 'precio_base':
                $factor = $validated['valor_ajuste'] / 100;
                if (in_array($validated['aplicar_a'], ['afiliado', 'ambos'])) {
                    $nuevosValores['valor_afiliado'] = round($prestacion->precio_general * $factor, 2);
                }
                if (in_array($validated['aplicar_a'], ['particular', 'ambos'])) {
                    $nuevosValores['valor_particular'] = round($prestacion->precio_general * ($factor * 2), 2);
                }
                break;
        }

        if (!empty($nuevosValores)) {
            $nuevosValores['updated_at'] = now();
            $prestacion->planes()->updateExistingPivot($planId, $nuevosValores);
            $actualizados++;
        }
    }

    // Registrar evento de auditoría
    activity()
        ->performedOn($prestacion)
        ->withProperties([
            'tipo_ajuste' => $validated['tipo_ajuste'],
            'valor_ajuste' => $validated['valor_ajuste'],
            'aplicar_a' => $validated['aplicar_a'],
            'planes_actualizados' => $actualizados
        ])
        ->log('prestacion_ajuste_masivo_precios');

    return redirect()->back()
        ->with('success', "Se actualizaron los precios de {$actualizados} planes");
    }

    /**
     * Verificar movimientos activos (para implementar según necesidades)
     */
    private function verificarMovimientosActivos(Prestacion $prestacion, Plan $plan)
    {
    // Aquí podrías verificar:
    // - Órdenes pendientes
    // - Facturación del período
    // - Autorizaciones vigentes
    // - etc.

    return false; // Por ahora retorna false
    }
    /**
     * Verificar si un código de prestación ya existe
     */
    public function checkCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|size:6|regex:/^\d{6}$/'
        ]);

        $exists = Prestacion::where('codigo', $request->codigo)->exists();

        return response()->json([
            'exists' => $exists,
            'codigo' => $request->codigo
        ]);
    }
}
