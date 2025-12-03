<?php

namespace App\Http\Controllers;

use App\Models\Prestador;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestadorController extends Controller
{
    public function index(Request $request)
    {
        $prestadores = Prestador::query()
            ->with('sucursales')
            ->when($request->search, function($query, $search) {
                $query->buscar($search);
            })
            ->when($request->sucursal_id, function($query, $sucursalId) {
                $query->porSucursal($sucursalId);
            })
            ->when($request->estado, function($query, $estado) {
                $query->where('estado', $estado);
            })
            ->withCount('sucursales')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $sucursales = Sucursal::where('is_active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return Inertia::render('Prestadores/Index', [
            'prestadores' => $prestadores,
            'sucursales' => $sucursales,
            'filters' => $request->only(['search', 'sucursal_id', 'estado'])
        ]);
    }

    public function create()
    {
        $sucursales = Sucursal::where('is_active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return Inertia::render('Prestadores/Create', [
            'sucursales' => $sucursales
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apellido' => 'required|string|max:100',
            'nombre' => 'required|string|max:100',
            'dni' => 'required|string|max:20|unique:prestadores,dni',
            'cuil' => 'required|string|max:20|unique:prestadores,cuil',
            'mail' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:50',
            'domicilio' => 'nullable|string|max:200',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'fecha_alta' => 'required|date',
            'observaciones' => 'nullable|string',
            'sucursales' => 'nullable|array',
            'sucursales.*.id' => 'required|exists:sucursales,id',
            'sucursales.*.fecha_desde' => 'nullable|date',
            'sucursales.*.fecha_hasta' => 'nullable|date|after:sucursales.*.fecha_desde',
            'sucursales.*.estado' => 'required|in:activo,inactivo',
        ]);

        $validated['usuario_alta'] = auth()->user()->name ?? 'Sistema';

        $prestador = Prestador::create($validated);

        // Asociar sucursales si se enviaron
        if (isset($validated['sucursales']) && count($validated['sucursales']) > 0) {
            foreach ($validated['sucursales'] as $sucursal) {
                $prestador->sucursales()->attach($sucursal['id'], [
                    'fecha_desde' => $sucursal['fecha_desde'] ?? null,
                    'fecha_hasta' => $sucursal['fecha_hasta'] ?? null,
                    'estado' => $sucursal['estado'] ?? 'activo',
                ]);
            }
        }

        return redirect()->route('prestadores.show', $prestador)
            ->with('success', 'Prestador creado exitosamente');
    }

    public function show(Prestador $prestador)
    {
        $prestador->load('sucursales');

        return Inertia::render('Prestadores/Show', [
            'prestador' => $prestador
        ]);
    }

    public function edit(Prestador $prestador)
    {
        $prestador->load('sucursales');

        $sucursales = Sucursal::where('is_active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return Inertia::render('Prestadores/Edit', [
            'prestador' => $prestador,
            'sucursales' => $sucursales
        ]);
    }

    public function update(Request $request, Prestador $prestador)
    {
        $validated = $request->validate([
            'apellido' => 'required|string|max:100',
            'nombre' => 'required|string|max:100',
            'dni' => 'required|string|max:20|unique:prestadores,dni,' . $prestador->id,
            'cuil' => 'required|string|max:20|unique:prestadores,cuil,' . $prestador->id,
            'mail' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:50',
            'domicilio' => 'nullable|string|max:200',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'observaciones' => 'nullable|string',
            'sucursales' => 'nullable|array',
            'sucursales.*.id' => 'required|exists:sucursales,id',
            'sucursales.*.fecha_desde' => 'nullable|date',
            'sucursales.*.fecha_hasta' => 'nullable|date',
            'sucursales.*.estado' => 'required|in:activo,inactivo',
        ]);

        // Si se está desactivando, guardar datos de baja
        if ($validated['estado'] === 'inactivo' && $prestador->estado === 'activo') {
            $validated['fecha_baja'] = now();
            $validated['usuario_baja'] = auth()->user()->name ?? 'Sistema';
        }

        // Si se está reactivando, limpiar datos de baja
        if ($validated['estado'] === 'activo' && $prestador->estado === 'inactivo') {
            $validated['fecha_baja'] = null;
            $validated['usuario_baja'] = null;
        }

        $prestador->update($validated);

        // Sincronizar sucursales si se enviaron
        if (isset($validated['sucursales'])) {
            $sync = [];
            foreach ($validated['sucursales'] as $sucursal) {
                $sync[$sucursal['id']] = [
                    'fecha_desde' => $sucursal['fecha_desde'] ?? null,
                    'fecha_hasta' => $sucursal['fecha_hasta'] ?? null,
                    'estado' => $sucursal['estado'] ?? 'activo',
                ];
            }
            $prestador->sucursales()->sync($sync);
        }

        return redirect()->route('prestadores.show', $prestador)
            ->with('success', 'Prestador actualizado exitosamente');
    }

    public function destroy(Prestador $prestador)
    {
        // Desasociar sucursales antes de eliminar
        $prestador->sucursales()->detach();

        $prestador->delete();

        return redirect()->route('prestadores.index')
            ->with('success', 'Prestador eliminado exitosamente');
    }

    // API para obtener lista de prestadores
    public function apiList()
    {
        return Prestador::activos()
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'dni', 'cuil']);
    }

    // Método para cambiar el estado
    public function toggleStatus(Prestador $prestador)
    {
        $nuevoEstado = $prestador->estado === 'activo' ? 'inactivo' : 'activo';

        $prestador->update([
            'estado' => $nuevoEstado,
            'fecha_baja' => $nuevoEstado === 'inactivo' ? now() : null,
            'usuario_baja' => $nuevoEstado === 'inactivo' ? (auth()->user()->name ?? 'Sistema') : null,
        ]);

        return back()->with('success', 'Estado actualizado exitosamente');
    }
}
