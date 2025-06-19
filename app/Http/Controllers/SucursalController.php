<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::withCount(['users'])
            ->when(request('search'), function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('direccion', 'like', "%{$search}%");
            })
            ->when(request('status'), function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Sucursales/Index', [
            'sucursales' => $sucursales,
            'filters' => request()->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Sucursales/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:10|unique:sucursales',
            'direccion' => 'required|string|max:500',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        Sucursal::create($validated);

        return redirect()->route('sucursales.index')
            ->with('message', 'Sucursal creada exitosamente.');
    }

    public function show(Sucursal $sucursal)
    {
        // Debug: verificar que lleguen los datos
        \Log::info('Mostrando sucursal:', $sucursal->toArray());

        $sucursal->load(['users.roles']);

        // Estadísticas de la sucursal
        $stats = [
            'total_usuarios' => $sucursal->users()->count(),
            'usuarios_activos' => $sucursal->users()->where('is_active', true)->count(),
            'supervisores' => $sucursal->users()->role('supervisor')->count(),
            'cajeros' => $sucursal->users()->role('cajero')->count(),
            'vendedores' => $sucursal->users()->role('vendedor')->count(),
            'administrativos' => $sucursal->users()->role('administrativo')->count(),
        ];

        return Inertia::render('Sucursales/Show', [
            'sucursal' => [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'codigo' => $sucursal->codigo,
                'direccion' => $sucursal->direccion,
                'telefono' => $sucursal->telefono,
                'email' => $sucursal->email,
                'is_active' => $sucursal->is_active,
                /*'created_at' => $sucursal->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $sucursal->updated_at->format('Y-m-d H:i:s'),*/
                'users' => $sucursal->users
            ],
            'stats' => $stats,
        ]);
    }

    public function edit(Sucursal $sucursal)
    {
        return Inertia::render('Sucursales/Edit', [
            'sucursales' => $sucursal,
        ]);
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:10|unique:sucursales,codigo,' . $sucursal->id,
            'direccion' => 'required|string|max:500',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $sucursal->update($validated);

        return redirect()->route('sucursales.index')
            ->with('message', 'Sucursal actualizada exitosamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        // Verificar si tiene usuarios asignados
        if ($sucursal->users()->count() > 0) {
            return back()->withErrors([
                'error' => 'No se puede eliminar la sucursal porque tiene usuarios asignados.'
            ]);
        }

        $sucursal->delete();

        return redirect()->route('sucursales.index')
            ->with('message', 'Sucursal eliminada exitosamente.');
    }

    public function toggleStatus(Sucursal $sucursal)
    {
        $sucursal->update(['is_active' => !$sucursal->is_active]);

        $status = $sucursal->is_active ? 'activada' : 'desactivada';

        return back()->with('message', "Sucursal {$status} exitosamente.");
    }

    public function getUsers(Sucursal $sucursal)
    {
        $users = $sucursal->users()
            ->with('roles')
            ->when(request('role'), function ($query, $role) {
                $query->role($role);
            })
            ->get();

        return response()->json($users);
    }
}
