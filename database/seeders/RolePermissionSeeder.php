<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir todos los módulos y sus permisos
        $modules = [
            'dashboard' => ['view'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete', 'assign'],
            'prestaciones' => ['view', 'create', 'edit', 'delete'],
            'planes' => ['view', 'create', 'edit', 'delete'],
            'rubros' => ['view', 'create', 'edit', 'delete'],
            'sucursales' => ['view', 'create', 'edit', 'delete'],
            'prestadores' => ['view', 'create', 'edit', 'delete'],
            'cajas' => ['view', 'create', 'edit', 'delete', 'open', 'close'],
            'ordenes' => ['view', 'create', 'edit', 'delete', 'anular', 'pdf'],
            'rendiciones' => ['view', 'create', 'edit', 'delete', 'pdf'],
            'reportes' => ['view', 'export'],
        ];

        // Crear permisos
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}"]);
            }
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);
        $operadorRole = Role::firstOrCreate(['name' => 'Operador']);

        // Limpiar permisos anteriores
        $adminRole->permissions()->detach();
        $supervisorRole->permissions()->detach();
        $operadorRole->permissions()->detach();

        // Administrador tiene todos los permisos
        $adminRole->givePermissionTo(Permission::all());

        // Supervisor tiene permisos limitados (no puede eliminar usuarios ni roles)
        $supervisorPermissions = Permission::whereNotIn('name', [
            'users.delete',
            'roles.delete',
            'roles.create',
            'roles.edit',
            'sucursales.delete',
        ])->get();
        $supervisorRole->givePermissionTo($supervisorPermissions);

        // Operador solo puede ver y crear órdenes y cajas
        $operadorPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'ordenes.view',
            'ordenes.create',
            'ordenes.pdf',
            'cajas.view',
            'cajas.create',
            'cajas.open',
            'cajas.close',
            'prestaciones.view',
            'prestadores.view',
            'reportes.view',
        ])->get();
        $operadorRole->givePermissionTo($operadorPermissions);

        $this->command->info('✓ Roles y permisos creados exitosamente!');
        $this->command->info('✓ Roles creados: Administrador, Supervisor, Operador');
        $this->command->info('✓ Total de permisos creados: ' . Permission::count());
    }
}
