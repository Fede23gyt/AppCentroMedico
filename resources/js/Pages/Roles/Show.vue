<template>
    <Head title="Detalle del Rol" />
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Rol: {{ role.name }}</h1>
                <div class="flex gap-2">
                    <Link :href="route('roles.edit', role.id)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                        Editar
                    </Link>
                    <Link :href="route('roles.index')" class="text-gray-600 hover:text-gray-900 px-4 py-2.5 border border-gray-300 rounded-lg font-medium transition-colors">
                        Volver
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información del Rol -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Información del Rol</h3>
                        <dl class="space-y-4">
                            <div class="pb-3 border-b border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase mb-1">Nombre</dt>
                                <dd class="text-sm font-semibold text-gray-900">{{ role.name }}</dd>
                            </div>
                            <div class="pb-3 border-b border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase mb-1">Guard</dt>
                                <dd class="text-sm text-gray-700">{{ role.guard_name }}</dd>
                            </div>
                            <div class="pb-3 border-b border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase mb-1">Creado</dt>
                                <dd class="text-sm text-gray-700">{{ role.created_at }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase mb-1">Actualizado</dt>
                                <dd class="text-sm text-gray-700">{{ role.updated_at }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Usuarios con este Rol -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">
                            Usuarios Asignados
                            <span class="ml-2 text-sm font-normal text-gray-500">({{ role.users.length }})</span>
                        </h3>
                        <div v-if="role.users.length > 0" class="space-y-2">
                            <div v-for="user in role.users" :key="user.id"
                                 class="flex items-center gap-3 p-3 bg-gradient-to-r from-blue-50 to-transparent rounded-lg border border-blue-100">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">{{ getUserInitials(user.name) }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">{{ user.name }}</div>
                                    <div class="text-xs text-gray-600">{{ user.email }}</div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 text-sm">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            No hay usuarios asignados a este rol
                        </div>
                    </div>
                </div>

                <!-- Permisos Asignados -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-6 text-gray-900">Permisos Asignados</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="module in role.permissions" :key="module.module"
                                 class="border border-gray-200 rounded-lg p-4 bg-gradient-to-br from-white to-gray-50">
                                <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-200 text-base">
                                    {{ module.module }}
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="permission in module.permissions" :key="permission.name"
                                         :class="getPermissionBadgeClass(permission.label)"
                                         class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium mr-2 mb-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="permission.label.toLowerCase() === 'ver'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            <path v-else-if="permission.label.toLowerCase() === 'crear'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            <path v-else-if="permission.label.toLowerCase() === 'editar'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            <path v-else-if="permission.label.toLowerCase() === 'eliminar'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ permission.label }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    role: Object,
});

const getPermissionBadgeClass = (label) => {
    const classMap = {
        'ver': 'bg-blue-100 text-blue-700 border border-blue-200',
        'crear': 'bg-green-100 text-green-700 border border-green-200',
        'editar': 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        'eliminar': 'bg-red-100 text-red-700 border border-red-200',
    };
    return classMap[label.toLowerCase()] || 'bg-gray-100 text-gray-700 border border-gray-200';
};

const getUserInitials = (name) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};
</script>
