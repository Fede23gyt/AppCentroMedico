<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Asignar rol de Administrador al primer usuario
        $user = User::first();

        if ($user) {
            $adminRole = Role::where('name', 'Administrador')->first();

            if ($adminRole) {
                $user->syncRoles(['Administrador']);
                $this->command->info("✓ Rol 'Administrador' asignado a: {$user->email}");
            } else {
                $this->command->error("✗ No se encontró el rol 'Administrador'. Ejecuta primero RolePermissionSeeder.");
            }
        } else {
            $this->command->error('✗ No hay usuarios en la base de datos.');
        }
    }
}
