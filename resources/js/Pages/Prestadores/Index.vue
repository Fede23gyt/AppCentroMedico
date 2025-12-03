<template>
    <Head title="Prestadores" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Prestadores</h1>
                    <p class="text-gray-600">Gestiona los profesionales médicos del centro</p>
                </div>
                <Link
                    :href="route('prestadores.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    Nuevo Prestador
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Búsqueda -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Nombre, apellido, DNI o CUIL..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @input="debouncedFilter"
                        />
                    </div>

                    <!-- Filtro por Sucursal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
                        <select
                            v-model="filters.sucursal_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todas las sucursales</option>
                            <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                                {{ sucursal.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Filtro por Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select
                            v-model="filters.estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>
                </div>

                <!-- Botón limpiar filtros -->
                <div v-if="hasFilters" class="mt-4">
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800"
                    >
                        Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                                Prestador
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                                DNI / CUIL
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                                Contacto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                                Sucursales
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-200 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="prestador in prestadores.data" :key="prestador.id" class="hover:bg-gray-50">
                            <!-- Prestador -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ prestador.apellido }}, {{ prestador.nombre }}
                                </div>
                            </td>

                            <!-- DNI / CUIL -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">DNI: {{ prestador.dni }}</div>
                                <div class="text-sm text-gray-500">CUIL: {{ prestador.cuil }}</div>
                            </td>

                            <!-- Contacto -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="prestador.telefono" class="text-sm text-gray-900">
                                    {{ prestador.telefono }}
                                </div>
                                <div v-if="prestador.mail" class="text-sm text-gray-500">
                                    {{ prestador.mail }}
                                </div>
                                <div v-if="!prestador.telefono && !prestador.mail" class="text-sm text-gray-400">
                                    Sin datos
                                </div>
                            </td>

                            <!-- Sucursales -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ prestador.sucursales_count }} sucursal(es)
                                </span>
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                        getEstadoClass(prestador.estado)
                                    ]"
                                >
                                    {{ capitalizeFirst(prestador.estado) }}
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link
                                        :href="route('prestadores.show', prestador.id)"
                                        class="text-blue-600 hover:text-blue-800"
                                        title="Ver detalles"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('prestadores.edit', prestador.id)"
                                        class="text-purple-600 hover:text-purple-800"
                                        title="Editar"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </Link>
                                    <button
                                        @click="toggleStatus(prestador)"
                                        :class="[
                                            'transition-opacity hover:opacity-80',
                                            prestador.estado === 'activo'
                                                ? 'text-red-600 hover:text-red-700'
                                                : 'text-green-600 hover:text-green-700'
                                        ]"
                                        :title="prestador.estado === 'activo' ? 'Desactivar' : 'Activar'"
                                    >
                                        <svg v-if="prestador.estado === 'activo'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sin resultados -->
                <div v-if="prestadores.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay prestadores</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo prestador.</p>
                </div>

                <!-- Paginación -->
                <div v-if="prestadores.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando <span class="font-medium">{{ prestadores.from }}</span> a
                            <span class="font-medium">{{ prestadores.to }}</span> de
                            <span class="font-medium">{{ prestadores.total }}</span> resultados
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-for="link in prestadores.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 border rounded-md text-sm',
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    prestadores: Object,
    sucursales: Array,
    filters: Object,
})

const filters = reactive({
    search: props.filters.search || '',
    sucursal_id: props.filters.sucursal_id || '',
    estado: props.filters.estado || '',
})

const hasFilters = computed(() => {
    return filters.search || filters.sucursal_id || filters.estado
})

let debounceTimer = null
const debouncedFilter = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        applyFilters()
    }, 300)
}

const applyFilters = () => {
    router.get(route('prestadores.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    filters.search = ''
    filters.sucursal_id = ''
    filters.estado = ''
    applyFilters()
}

const toggleStatus = (prestador) => {
    if (confirm(`¿Estás seguro de ${prestador.estado === 'activo' ? 'desactivar' : 'activar'} a ${prestador.apellido}, ${prestador.nombre}?`)) {
        router.patch(route('prestadores.toggle-status', prestador.id), {}, {
            preserveScroll: true
        })
    }
}

const getEstadoClass = (estado) => {
    const classes = {
        'activo': 'bg-green-100 text-green-800',
        'inactivo': 'bg-red-100 text-red-800',
        'suspendido': 'bg-yellow-100 text-yellow-800',
    }
    return classes[estado] || 'bg-gray-100 text-gray-800'
}

const capitalizeFirst = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1)
}
</script>
