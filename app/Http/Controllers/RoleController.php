<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users_count,
                'guard_name' => $role->guard_name,
                'created_at' => $role->created_at->format('d/m/Y'),
            ];
        });

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        })->map(function ($group, $module) {
            return [
                'module' => ucfirst($module),
                'permissions' => $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'label' => ucfirst(explode('.', $permission->name)[1]),
                    ];
                })->values()
            ];
        })->values();

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role = Role::create(['name' => $validated['name']]);

            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Rol creado exitosamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear el rol: ' . $e->getMessage()]);
        }
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');

        $permissionsGrouped = $role->permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        })->map(function ($group, $module) {
            return [
                'module' => ucfirst($module),
                'permissions' => $group->map(function ($permission) {
                    return [
                        'name' => $permission->name,
                        'label' => ucfirst(explode('.', $permission->name)[1]),
                    ];
                })->values()
            ];
        })->values();

        return Inertia::render('Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $permissionsGrouped,
                'users' => $role->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                }),
                'created_at' => $role->created_at->format('d/m/Y H:i'),
                'updated_at' => $role->updated_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        })->map(function ($group, $module) {
            return [
                'module' => ucfirst($module),
                'permissions' => $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'label' => ucfirst(explode('.', $permission->name)[1]),
                    ];
                })->values()
            ];
        })->values();

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $rolePermissions,
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role->update(['name' => $validated['name']]);

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            } else {
                $role->syncPermissions([]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Rol actualizado exitosamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el rol: ' . $e->getMessage()]);
        }
    }

    public function destroy(Role $role)
    {
        try {
            // Verificar si el rol tiene usuarios asignados
            if ($role->users()->count() > 0) {
                return back()->withErrors(['error' => 'No se puede eliminar el rol porque tiene usuarios asignados']);
            }

            $role->delete();

            return redirect()->route('roles.index')
                ->with('success', 'Rol eliminado exitosamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el rol: ' . $e->getMessage()]);
        }
    }
}
