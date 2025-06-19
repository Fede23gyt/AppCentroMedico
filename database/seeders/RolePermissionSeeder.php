<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            // Usuarios
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Roles y Permisos
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Sucursales
            'sucursales.view',
            'sucursales.create',
            'sucursales.edit',
            'sucursales.delete',

            // Ventas
            'ventas.view',
            'ventas.create',
            'ventas.edit',
            'ventas.delete',

            // Productos
            'productos.view',
            'productos.create',
            'productos.edit',
            'productos.delete',

            // Reportes
            'reportes.ventas',
            'reportes.usuarios',
            'reportes.sucursales',

            // Configuración
            'config.view',
            'config.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'administrador']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $cajeroRole = Role::firstOrCreate(['name' => 'cajero']);
        $vendedorRole = Role::firstOrCreate(['name' => 'vendedor']);
        $administrativoRole = Role::firstOrCreate(['name' => 'administrativo']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo(Permission::all());

        $supervisorRole->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'ventas.view',
            'ventas.create',
            'ventas.edit',
            'productos.view',
            'productos.create',
            'productos.edit',
            'reportes.ventas',
            'reportes.usuarios',
        ]);

        $cajeroRole->givePermissionTo([
            'ventas.view',
            'ventas.create',
            'productos.view',
        ]);

        $vendedorRole->givePermissionTo([
            'ventas.view',
            'ventas.create',
            'productos.view',
        ]);

        $administrativoRole->givePermissionTo([
            'users.view',
            'ventas.view',
            'productos.view',
            'reportes.ventas',
        ]);
    }
}
