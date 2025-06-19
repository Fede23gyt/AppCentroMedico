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

    // Gestión de planes asociados
    public function planes(Prestacion $prestacion)
    {
        $prestacion->load([
            'rubro',
            'planes' => function($query) {
                $query->orderBy('nombre')->withPivot([
                    'valor_afiliado', 'valor_particular', 'cant_max_individual',
                    'cant_max_grupo', 'estado', 'fecha_desde',
                    'fecha_hasta', 'observaciones'
                ]);
            }
        ]);

        $planesDisponibles = Plan::activos()
            ->whereNotIn('id', $prestacion->planes->pluck('id'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nombre_corto']);

        return Inertia::render('Prestaciones/Planes', [
            'prestacion' => $prestacion,
            'planesDisponibles' => $planesDisponibles
        ]);
    }

    public function asociarPlan(Request $request, Prestacion $prestacion)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:planes,id',
            'valor_afiliado' => 'required|numeric|min:0',
            'valor_particular' => 'required|numeric|min:0',
            'cant_max_individual' => 'nullable|integer|min:1',
            'cant_max_grupo' => 'nullable|integer|min:1',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'nullable|date|after:fecha_desde',
            'observaciones' => 'nullable|string'
        ]);

        // Verificar que no exista ya la asociación
        if ($prestacion->planes()->where('plan_id', $validated['plan_id'])->exists()) {
            return redirect()->back()
                ->with('error', 'La prestación ya está asociada a este plan');
        }

        $prestacion->planes()->attach($validated['plan_id'], [
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
            ->with('success', 'Plan asociado exitosamente');
    }

    public function desasociarPlan(Prestacion $prestacion, Plan $plan)
    {
        $prestacion->planes()->detach($plan->id);

        return redirect()->back()
            ->with('success', 'Plan desasociado exitosamente');
    }

    public function actualizarPlan(Request $request, Prestacion $prestacion, Plan $plan)
    {
        $validated = $request->validate([
            'valor_afiliado' => 'required|numeric|min:0',
            'valor_particular' => 'required|numeric|min:0',
            'cant_max_individual' => 'nullable|integer|min:1',
            'cant_max_grupo' => 'nullable|integer|min:1',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'nullable|date|after:fecha_vigencia_desde',
            'observaciones' => 'nullable|string'
        ]);

        $prestacion->planes()->updateExistingPivot($plan->id, array_merge($validated, [
            'updated_at' => now()
        ]));

        return redirect()->back()
            ->with('success', 'Asociación actualizada exitosamente');
    }

    public function apiList(Request $request)
    {
        return Prestacion::activas()
            ->when($request->rubro_id, function($query, $rubroId) {
                $query->porRubro($rubroId);
            })
            ->when($request->search, function($query, $search) {
                $query->buscar($search);
            })
            ->with('rubro:id,nombre')
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'precio_general', 'rubro_id']);
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
