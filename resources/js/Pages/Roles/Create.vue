<template>
    <Head title="Crear Rol" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Crear Nuevo Rol</h1>
                <Link :href="route('roles.index')" class="text-gray-600 hover:text-gray-900">
                    Volver
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Rol</label>
                    <input v-model="form.name" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required/>
                    <span v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</span>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Gestión de Permisos</h3>
                        <div class="text-sm text-gray-600">
                            {{ getSelectedCount() }} de {{ getTotalPermissions() }} permisos seleccionados
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="module in permissions" :key="module.module"
                             class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors bg-gradient-to-br from-white to-gray-50">
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                <h4 class="font-semibold text-gray-800 text-base">{{ module.module }}</h4>
                                <button type="button"
                                        @click="toggleModulePermissions(module)"
                                        class="text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors font-medium">
                                    {{ isModuleFullySelected(module) ? 'Deseleccionar' : 'Seleccionar' }} todo
                                </button>
                            </div>
                            <div class="space-y-2">
                                <label v-for="permission in module.permissions"
                                       :key="permission.id"
                                       class="flex items-center p-2.5 rounded-lg hover:bg-white transition-colors cursor-pointer group">
                                    <input type="checkbox"
                                           :value="permission.id"
                                           v-model="form.permissions"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"/>
                                    <span class="ml-3 flex items-center gap-2 text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                        <svg :class="getPermissionIconColor(permission.label)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="permission.label.toLowerCase() === 'ver'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            <path v-else-if="permission.label.toLowerCase() === 'crear'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            <path v-else-if="permission.label.toLowerCase() === 'editar'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            <path v-else-if="permission.label.toLowerCase() === 'eliminar'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ permission.label }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <Link :href="route('roles.index')" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg disabled:opacity-50 font-medium transition-colors shadow-sm">
                        {{ form.processing ? 'Creando...' : 'Crear Rol' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    permissions: Array,
});

const form = useForm({
    name: '',
    permissions: [],
});

const submit = () => {
    form.post(route('roles.store'));
};

const toggleModulePermissions = (module) => {
    const modulePermissionIds = module.permissions.map(p => p.id);
    const allSelected = modulePermissionIds.every(id => form.permissions.includes(id));

    if (allSelected) {
        // Deseleccionar todos los permisos del módulo
        form.permissions = form.permissions.filter(id => !modulePermissionIds.includes(id));
    } else {
        // Seleccionar todos los permisos del módulo
        const newPermissions = [...new Set([...form.permissions, ...modulePermissionIds])];
        form.permissions = newPermissions;
    }
};

const isModuleFullySelected = (module) => {
    const modulePermissionIds = module.permissions.map(p => p.id);
    return modulePermissionIds.every(id => form.permissions.includes(id));
};

const getPermissionIconColor = (label) => {
    const colorMap = {
        'ver': 'text-blue-500',
        'crear': 'text-green-500',
        'editar': 'text-yellow-500',
        'eliminar': 'text-red-500',
    };
    return colorMap[label.toLowerCase()] || 'text-gray-500';
};

const getSelectedCount = () => {
    return form.permissions.length;
};

const getTotalPermissions = () => {
    return props.permissions.reduce((total, module) => total + module.permissions.length, 0);
};
</script>
