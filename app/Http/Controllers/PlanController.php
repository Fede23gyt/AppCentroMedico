<?php

namespace App\Http\Controllers;

use App\Models\Plan;
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
            'descripcion' => 'nullable|string'
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
            }
        ]);

        return Inertia::render('Planes/Show', [
            'plan' => $plan
        ]);
    }

    public function edit(Plan $plan)
    {
        return Inertia::render('Planes/Edit', [
            'plan' => $plan
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
            'descripcion' => 'nullable|string'
        ]);

        $plan->update($validated);

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

    public function apiList()
    {
        return Plan::activos()->vigentes()->orderBy('nombre')->get(['id', 'nombre', 'nombre_corto']);
    }
}
