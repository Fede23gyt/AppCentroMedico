<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'sucursal'])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when(request('role'), function ($query, $role) {
                $query->role($role);
            })
            ->when(request('sucursal'), function ($query, $sucursal) {
                $query->where('sucursal_id', $sucursal);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => request()->only(['search', 'role', 'sucursal']),
            'roles' => Role::all(),
            'sucursales' => Sucursal::where('is_active', true)->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
            'sucursales' => Sucursal::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'sucursal_id' => $validated['sucursal_id'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('message', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        return Inertia::render('Users/Show', [
            'user' => $user->load(['roles', 'sucursal']),
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->load(['roles', 'sucursal']),
            'roles' => Role::all(),
            'sucursales' => Sucursal::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'sucursal_id' => $validated['sucursal_id'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('message', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        // No permitir eliminar el usuario actual
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta.']);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('message', 'Usuario eliminado exitosamente.');
    }

    public function toggleStatus(User $user)
    {
        // No permitir desactivar el usuario actual
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes desactivar tu propia cuenta.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activado' : 'desactivado';

        return back()->with('message', "Usuario {$status} exitosamente.");
    }
}
