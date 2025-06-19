<template>
    <Head title="Gestión de Prestaciones" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Prestaciones</h1>
                    <p class="text-gray-600">Administra las prestaciones médicas del sistema</p>
                </div>
                <Link
                    :href="route('prestaciones.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                >
                    + Nueva Prestación
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Código o nombre..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @input="debounceSearch"
                        />
                    </div>

                    <!-- Filtro por rubro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rubro</label>
                        <select
                            v-model="filters.rubro_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos los rubros</option>
                            <option v-for="rubro in rubros" :key="rubro.id" :value="rubro.id">
                                {{ rubro.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Filtro por estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select
                            v-model="filters.estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos los estados</option>
                            <option value="activo">Activos</option>
                            <option value="inactivo">Inactivos</option>
                            <option value="suspendido">Suspendidos</option>
                        </select>
                    </div>

                    <!-- Botón limpiar -->
                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="w-full px-3 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <div v-if="$page.props.flash?.success" class="mb-6">
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ $page.props.flash.success }}
                </div>
            </div>

            <div v-if="$page.props.flash?.error" class="mb-6">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ $page.props.flash.error }}
                </div>
            </div>

            <!-- Tabla de Prestaciones -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Código
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Precio General
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Planes
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Opciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="prestacion in prestaciones.data"
                                :key="prestacion.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <!-- Columna Código -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono bg-blue-100 text-blue-800 px-3 py-1 rounded font-semibold">
                                        {{ prestacion.codigo }}
                                    </span>
                                </td>

                                <!-- Columna Nombre -->
                                <td class="px-6 py-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ prestacion.nombre }}
                                            </div>
                                            <div v-if="prestacion.rubro" class="text-xs text-purple-600 mt-1">
                                                {{ prestacion.rubro.nombre }}
                                            </div>
                                            <div v-if="prestacion.descripcion" class="text-xs text-gray-500 mt-1 line-clamp-1">
                                                {{ prestacion.descripcion }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna Estado -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getEstadoClass(prestacion.estado)
                                        ]"
                                    >
                                        {{ capitalizeFirst(prestacion.estado) }}
                                    </span>
                                </td>

                                <!-- Columna Precio General -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(prestacion.precio_general) }}
                                    </div>
                                    <div v-if="prestacion.valor_ips" class="text-xs text-gray-500">
                                        IPS: {{ formatCurrency(prestacion.valor_ips) }}
                                    </div>
                                </td>

                                <!-- Columna Planes -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ prestacion.planes_count || 0 }}
                                    </span>
                                </td>

                                <!-- Columna Opciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Ver detalles -->
                                        <Link
                                            :href="route('prestaciones.show', prestacion.id)"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="Ver detalles"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </Link>

                                        <!-- Gestionar planes -->
                                        <Link
                                            :href="route('prestaciones.planes', prestacion.id)"
                                            class="text-purple-600 hover:text-purple-900"
                                            title="Gestionar planes"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                        </Link>

                                        <!-- Editar -->
                                        <Link
                                            :href="route('prestaciones.edit', prestacion.id)"
                                            class="text-gray-600 hover:text-gray-900"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </Link>

                                        <!-- Toggle Estado -->
                                        <button
                                            @click="togglePrestacionStatus(prestacion)"
                                            :class="[
                                                'hover:underline',
                                                prestacion.estado === 'activo'
                                                    ? 'text-orange-600 hover:text-orange-800'
                                                    : 'text-green-600 hover:text-green-800'
                                            ]"
                                            :title="prestacion.estado === 'activo' ? 'Desactivar' : 'Activar'"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                            </svg>
                                        </button>

                                        <!-- Eliminar -->
                                        <button
                                            @click="confirmDelete(prestacion)"
                                            class="text-red-600 hover:text-red-900"
                                            title="Eliminar"
                                            :disabled="(prestacion.planes_count || 0) > 0"
                                            :class="{ 'opacity-50 cursor-not-allowed': (prestacion.planes_count || 0) > 0 }"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje si no hay datos -->
                <div v-if="prestaciones.data && prestaciones.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay prestaciones</h3>
                    <p class="text-gray-500 mb-4">Comienza creando tu primera prestación médica</p>
                    <Link
                        :href="route('prestaciones.create')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                    >
                        Crear Primera Prestación
                    </Link>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="prestaciones.links" class="bg-white px-4 py-3 border-t border-gray-200 rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="prestaciones.prev_page_url"
                            :href="prestaciones.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="prestaciones.next_page_url"
                            :href="prestaciones.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Siguiente
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">{{ prestaciones.from }}</span>
                                a
                                <span class="font-medium">{{ prestaciones.to }}</span>
                                de
                                <span class="font-medium">{{ prestaciones.total }}</span>
                                resultados
                            </p>
                        </div>
                        <div class="flex space-x-1">
                            <Link
                                v-for="link in prestaciones.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    link.active
                                        ? 'bg-blue-600 border-blue-600 text-white'
                                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de confirmación -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showDeleteModal = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Eliminar Prestación</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que quieres eliminar la prestación <strong>{{ prestacionToDelete?.codigo }} - {{ prestacionToDelete?.nombre }}</strong>?
                                        <span v-if="(prestacionToDelete?.planes_count || 0) > 0" class="text-red-600">
                                            Esta prestación está asociada a {{ prestacionToDelete.planes_count }} planes.
                                        </span>
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="deletePrestacion"
                                type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                                :disabled="(prestacionToDelete?.planes_count || 0) > 0"
                                :class="{ 'opacity-50 cursor-not-allowed': (prestacionToDelete?.planes_count || 0) > 0 }"
                            >
                                Eliminar
                            </button>
                            <button
                                @click="showDeleteModal = false"
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    prestaciones: Object,
    rubros: Array,
    filters: Object,
})

// Estado reactivo
const showDeleteModal = ref(false)
const prestacionToDelete = ref(null)

// Filtros reactivos
const filters = reactive({
    search: props.filters?.search || '',
    rubro_id: props.filters?.rubro_id || '',
    estado: props.filters?.estado || '',
})

// Utilidades
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(amount)
}

const getEstadoClass = (estado) => {
    const classes = {
        activo: 'bg-green-100 text-green-800',
        inactivo: 'bg-red-100 text-red-800',
        suspendido: 'bg-yellow-100 text-yellow-800'
    }
    return classes[estado] || 'bg-gray-100 text-gray-800'
}

const capitalizeFirst = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1)
}

// Debounce para búsqueda
let searchTimeout = null
const debounceSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
}

// Aplicar filtros
const applyFilters = () => {
    router.get(route('prestaciones.index'), filters, {
        preserveState: true,
        replace: true,
    })
}

// Limpiar filtros
const clearFilters = () => {
    filters.search = ''
    filters.rubro_id = ''
    filters.estado = ''
    applyFilters()
}

// Confirmar eliminación
const confirmDelete = (prestacion) => {
    if ((prestacion.planes_count || 0) > 0) {
        alert(`No se puede eliminar la prestación "${prestacion.codigo} - ${prestacion.nombre}" porque está asociada a ${prestacion.planes_count} planes.`)
        return
    }
    prestacionToDelete.value = prestacion
    showDeleteModal.value = true
}

// Eliminar prestación
const deletePrestacion = () => {
    if ((prestacionToDelete.value?.planes_count || 0) > 0) {
        return
    }

    router.delete(route('prestaciones.destroy', prestacionToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            prestacionToDelete.value = null
        }
    })
}

// Cambiar estado de la prestación
const togglePrestacionStatus = (prestacion) => {
    const nuevoEstado = prestacion.estado === 'activo' ? 'inactivo' : 'activo'

    router.patch(route('prestaciones.update', prestacion.id), {
        ...prestacion,
        estado: nuevoEstado
    }, {
        preserveScroll: true
    })
}
</script>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
