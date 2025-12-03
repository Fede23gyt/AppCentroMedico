<template>
    <Head title="Roles y Permisos" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Roles y Permisos</h1>
                    <p class="text-gray-600">Gestiona los roles y permisos del sistema</p>
                </div>
                <Link
                    :href="route('roles.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                >
                    Nuevo Rol
                </Link>
            </div>

            <!-- Tabla de Roles -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Nombre del Rol
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                                Permisos
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                                Usuarios
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                                Fecha Creación
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ role.permissions_count }} permisos
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ role.users_count }} usuarios
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ role.created_at }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium">
                                <div class="flex justify-center gap-2">
                                    <Link
                                        :href="route('roles.show', role.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Ver"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('roles.edit', role.id)"
                                        class="text-green-600 hover:text-green-900"
                                        title="Editar"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </Link>
                                    <button
                                        @click="deleteRole(role)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Eliminar"
                                        :disabled="role.users_count > 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': role.users_count > 0 }"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="roles.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No hay roles registrados
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    roles: Array,
});

const deleteRole = (role) => {
    if (role.users_count > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No se puede eliminar',
            text: 'Este rol tiene usuarios asignados. Debes reasignar los usuarios antes de eliminarlo.',
            confirmButtonColor: '#2563eb',
        });
        return;
    }

    Swal.fire({
        title: '¿Eliminar rol?',
        text: `¿Estás seguro de eliminar el rol "${role.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('roles.destroy', role.id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rol eliminado',
                        text: 'El rol ha sido eliminado exitosamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
};
</script>
