<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Usuarios</h1>
                    <p class="text-gray-600">Administra los usuarios del sistema</p>
                </div>
                <Link
                    :href="route('users.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                >
                    + Nuevo Usuario
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
                            placeholder="Nombre o email..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @input="debounceSearch"
                        />
                    </div>

                    <!-- Filtro por rol -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                        <select
                            v-model="filters.role"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos los roles</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">
                                {{ role.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Filtro por sucursal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sucursal</label>
                        <select
                            v-model="filters.sucursal"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todas las sucursales</option>
                            <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                                {{ sucursal.nombre }}
                            </option>
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

            <!-- Tabla de usuarios -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sucursal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ user.roles[0]?.name || 'Sin rol' }}
                  </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ user.sucursal?.nombre || 'Sin asignar' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link
                                    :href="route('users.show', user.id)"
                                    class="text-blue-600 hover:text-blue-800"
                                >
                                    Ver
                                </Link>
                                <Link
                                    :href="route('users.edit', user.id)"
                                    class="text-green-600 hover:text-green-800"
                                >
                                    Editar
                                </Link>
                                <button
                                    @click="toggleUserStatus(user)"
                                    :class="[
                      'hover:underline',
                      user.is_active ? 'text-orange-600 hover:text-orange-800' : 'text-blue-600 hover:text-blue-800'
                    ]"
                                >
                                    {{ user.is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button
                                    @click="confirmDelete(user)"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="users.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="users.prev_page_url"
                                :href="users.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Anterior
                            </Link>
                            <Link
                                v-if="users.next_page_url"
                                :href="users.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando
                                    <span class="font-medium">{{ users.from }}</span>
                                    a
                                    <span class="font-medium">{{ users.to }}</span>
                                    de
                                    <span class="font-medium">{{ users.total }}</span>
                                    resultados
                                </p>
                            </div>
                            <div class="flex space-x-1">
                                <Link
                                    v-for="link in users.links"
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Eliminar Usuario</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que quieres eliminar al usuario <strong>{{ userToDelete?.name }}</strong>? Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="deleteUser"
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
    users: Object,
    filters: Object,
    roles: Array,
    sucursales: Array,
})

// Estado reactivo
const showDeleteModal = ref(false)
const userToDelete = ref(null)

// Filtros reactivos
const filters = reactive({
    search: props.filters.search || '',
    role: props.filters.role || '',
    sucursal: props.filters.sucursal || '',
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
    router.get(route('users.index'), filters, {
        preserveState: true,
        replace: true,
    })
}

// Limpiar filtros
const clearFilters = () => {
    filters.search = ''
    filters.role = ''
    filters.sucursal = ''
    applyFilters()
}

// Confirmar eliminación
const confirmDelete = (user) => {
    userToDelete.value = user
    showDeleteModal.value = true
}

// Eliminar usuario
const deleteUser = () => {
    router.delete(route('users.destroy', userToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            userToDelete.value = null
        }
    })
}

// Cambiar estado del usuario
const toggleUserStatus = (user) => {
    router.patch(route('users.toggle-status', user.id))
}
</script>
