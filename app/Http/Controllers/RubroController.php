<?php

namespace App\Http\Controllers;

use App\Models\Rubro;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RubroController extends Controller
{
    public function index(Request $request)
    {
        $rubros = Rubro::query()
            ->when($request->search, function($query, $search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->when($request->estado, function($query, $estado) {
                $query->where('estado', $estado);
            })
            ->withCount('prestaciones')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Rubros/Index', [
            'rubros' => $rubros,
            'filters' => $request->only(['search', 'estado'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Rubros/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:rubros',
            'descripcion' => 'nullable|string',
            'porc' => 'required|numeric|min:0|max:100',
            'estado' => 'required|in:activo,inactivo'
        ]);

        Rubro::create($validated);

        return redirect()->route('rubros.index')
            ->with('success', 'Rubro creado exitosamente');
    }

    public function edit(Rubro $rubro)
    {
        return Inertia::render('Rubros/Edit', [
            'rubro' => $rubro
        ]);
    }

    public function update(Request $request, Rubro $rubro)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:rubros,nombre,' . $rubro->id,
            'descripcion' => 'nullable|string',
            'porc' => 'required|numeric|min:0|max:100',
            'estado' => 'required|in:activo,inactivo'
        ]);

        $rubro->update($validated);

        return redirect()->route('rubros.index')
            ->with('success', 'Rubro actualizado exitosamente');
    }

    public function destroy(Rubro $rubro)
    {
        if ($rubro->prestaciones()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el rubro porque tiene prestaciones asociadas');
        }

        $rubro->delete();

        return redirect()->route('rubros.index')
            ->with('success', 'Rubro eliminado exitosamente');
    }

    public function apiList()
    {
        return Rubro::activos()->orderBy('nombre')->get(['id', 'nombre']);
    }
}
