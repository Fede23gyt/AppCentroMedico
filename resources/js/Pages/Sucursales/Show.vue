<template>
    <Head :title="`Sucursal: ${sucursal.nombre}`" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-4">
                    <Link
                        :href="route('sucursales.index')"
                        class="text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        ← Volver a sucursales
                    </Link>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ sucursal.nombre }}</h1>
                            <p class="text-gray-600 font-mono text-sm">{{ sucursal.codigo }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
            <span
                :class="[
                'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                sucursal.is_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800'
              ]"
            >
              {{ sucursal.is_active ? 'Activa' : 'Inactiva' }}
            </span>
                        <Link
                            v-if="sucursal && sucursal.id"
                            :href="route('sucursales.edit', sucursal.id)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            Editar Sucursal
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Total Usuarios</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_usuarios }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Usuarios Activos</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.usuarios_activos }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Supervisores</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.supervisores }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="h-4 w-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Cajeros</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.cajeros }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Vendedores</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.vendedores }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información de la sucursal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Detalles de contacto -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información de Contacto</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Código</dt>
                                <dd class="text-sm text-gray-900 mt-1 font-mono">{{ sucursal.codigo }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="text-sm mt-1">
                  <span
                      :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      sucursal.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ sucursal.is_active ? 'Activa' : 'Inactiva' }}
                  </span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ sucursal.direccion }}</dd>
                            </div>
                            <div v-if="sucursal.telefono">
                                <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ sucursal.telefono }}</dd>
                            </div>
                            <div v-if="sucursal.email">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ sucursal.email }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Lista de usuarios -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Usuarios Asignados</h3>
                        </div>
                        <div v-if="sucursal.users.length > 0" class="divide-y divide-gray-200">
                            <div
                                v-for="user in sucursal.users"
                                :key="user.id"
                                class="px-6 py-4 hover:bg-gray-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ user.roles[0]?.name || 'Sin rol' }}
                    </span>
                                        <span
                                            :class="[
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                        user.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                                        >
                      {{ user.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="px-6 py-4 text-center text-gray-500">
                            No hay usuarios asignados a esta sucursal
                        </div>
                    </div>
                </div>

                <!-- Panel lateral -->
                <div class="space-y-6">
                    <!-- Acciones rápidas -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
                        <div class="space-y-3">
                            <Link
                                v-if="sucursal && sucursal.id"
                                :href="route('sucursales.edit', sucursal.id)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors block text-center"
                            >
                                Editar Sucursal
                            </Link>

                            <button
                                @click="toggleSucursalStatus"
                                :class="[
                  'w-full px-4 py-2 rounded-md font-medium transition-colors',
                  sucursal.is_active
                    ? 'bg-orange-600 hover:bg-orange-700 text-white'
                    : 'bg-green-600 hover:bg-green-700 text-white'
                ]"
                            >
                                {{ sucursal.is_active ? 'Desactivar Sucursal' : 'Activar Sucursal' }}
                            </button>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Sistema</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de creación</dt>
                                <dd class="text-sm text-gray-900">{{ formatDate(sucursal.created_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                                <dd class="text-sm text-gray-900">{{ formatDate(sucursal.updated_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ID de sucursal</dt>
                                <dd class="text-sm text-gray-900 font-mono">#{{ sucursal.id }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Distribución por roles -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Distribución por Roles</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Supervisores</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                  {{ stats.supervisores }}
                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Cajeros</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                  {{ stats.cajeros }}
                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Vendedores</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                  {{ stats.vendedores }}
                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Administrativos</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                  {{ stats.administrativos }}
                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    sucursal: Object,
    stats: Object,
})

const toggleSucursalStatus = () => {
    router.patch(route('sucursales.toggle-status', props.sucursal.id))
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
