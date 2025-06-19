<template>
    <Head title="Gestión de Sucursales" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Sucursales</h1>
                    <p class="text-gray-600">Administra las sucursales del sistema</p>
                </div>
                <Link
                    :href="route('sucursales.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                >
                    + Nueva Sucursal
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Nombre, código o dirección..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @input="debounceSearch"
                        />
                    </div>

                    <!-- Filtro por estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select
                            v-model="filters.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos los estados</option>
                            <option value="active">Activas</option>
                            <option value="inactive">Inactivas</option>
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
            <div v-if="$page.props.flash?.message" class="mb-6">
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ $page.props.flash.message }}
                </div>
            </div>

            <!-- Grid de sucursales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div
                    v-for="sucursal in sucursales.data"
                    :key="sucursal.id"
                    class="bg-white rounded-lg shadow hover:shadow-md transition-shadow overflow-hidden"
                >
                    <!-- Header de la tarjeta -->
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ sucursal.nombre }}</h3>
                                <p class="text-sm text-gray-500 font-mono">{{ sucursal.codigo }}</p>
                            </div>
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
                        </div>
                    </div>

                    <!-- Información -->
                    <div class="px-6 pb-4">
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="truncate">{{ sucursal.direccion }}</span>
                            </div>

                            <div v-if="sucursal.telefono" class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ sucursal.telefono }}</span>
                            </div>

                            <div v-if="sucursal.email" class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                                <span class="truncate">{{ sucursal.email }}</span>
                            </div>

                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                                <span>{{ sucursal.users_count }} usuarios</span>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                        <Link
                            :href="route('sucursales.show', sucursal.id)"
                            class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                        >
                            Ver detalles
                        </Link>

                        <div class="flex space-x-2">
                            <Link
                                :href="route('sucursales.edit', sucursal.id)"
                                class="text-gray-600 hover:text-gray-800"
                                title="Editar"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </Link>

                            <button
                                @click="toggleSucursalStatus(sucursal)"
                                :class="[
                  'hover:underline',
                  sucursal.is_active ? 'text-orange-600 hover:text-orange-800' : 'text-green-600 hover:text-green-800'
                ]"
                                :title="sucursal.is_active ? 'Desactivar' : 'Activar'"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </button>

                            <button
                                @click="confirmDelete(sucursal)"
                                class="text-red-600 hover:text-red-800"
                                title="Eliminar"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="sucursales.links" class="bg-white px-4 py-3 border-t border-gray-200 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="sucursales.prev_page_url"
                            :href="sucursales.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="sucursales.next_page_url"
                            :href="sucursales.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Siguiente
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">{{ sucursales.from }}</span>
                                a
                                <span class="font-medium">{{ sucursales.to }}</span>
                                de
                                <span class="font-medium">{{ sucursales.total }}</span>
                                resultados
                            </p>
                        </div>
                        <div class="flex space-x-1">
                            <Link
                                v-for="link in sucursales.links"
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Eliminar Sucursal</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que quieres eliminar la sucursal <strong>{{ sucursalToDelete?.nombre }}</strong>? Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="deleteSucursal"
                                type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
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
    sucursales: Object,
    filters: Object,
})

// Estado reactivo
const showDeleteModal = ref(false)
const sucursalToDelete = ref(null)

// Filtros reactivos
const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
})

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
    router.get(route('sucursales.index'), filters, {
        preserveState: true,
        replace: true,
    })
}

// Limpiar filtros
const clearFilters = () => {
    filters.search = ''
    filters.status = ''
    applyFilters()
}

// Confirmar eliminación
const confirmDelete = (sucursal) => {
    sucursalToDelete.value = sucursal
    showDeleteModal.value = true
}

// Eliminar sucursal
const deleteSucursal = () => {
    router.delete(route('sucursales.destroy', sucursalToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            sucursalToDelete.value = null
        }
    })
}

// Cambiar estado de la sucursal
const toggleSucursalStatus = (sucursal) => {
    router.patch(route('sucursales.toggle-status', sucursal.id))
}
</script>
