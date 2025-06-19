<template>
    <Head :title="`Usuario: ${user.name}`" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-4">
                    <Link
                        :href="route('users.index')"
                        class="text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        ← Volver a usuarios
                    </Link>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
              <span class="text-2xl font-medium text-blue-600">
                {{ user.name.charAt(0).toUpperCase() }}
              </span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
                            <p class="text-gray-600">{{ user.email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
            <span
                :class="[
                'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                user.is_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800'
              ]"
            >
              {{ user.is_active ? 'Activo' : 'Inactivo' }}
            </span>
                        <Link
                            :href="route('users.edit', user.id)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                        >
                            Editar Usuario
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Información del usuario -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Detalles personales -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre completo</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ user.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Correo electrónico</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ user.email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ user.phone || 'No especificado' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="text-sm mt-1">
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
                                </dd>
                            </div>
                        </dl>

                        <div v-if="user.address" class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ user.address }}</dd>
                        </div>
                    </div>

                    <!-- Información del sistema -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración del Sistema</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rol</dt>
                                <dd class="text-sm mt-1">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ user.roles[0]?.name || 'Sin rol' }}
                  </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sucursal asignada</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ user.sucursal?.nombre || 'Sin asignar' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de registro</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ formatDate(user.created_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ formatDate(user.updated_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Panel lateral -->
                <div class="space-y-6">
                    <!-- Acciones rápidas -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
                        <div class="space-y-3">
                            <Link
                                :href="route('users.edit', user.id)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors block text-center"
                            >
                                Editar Usuario
                            </Link>

                            <button
                                @click="toggleUserStatus"
                                :class="[
                  'w-full px-4 py-2 rounded-md font-medium transition-colors',
                  user.is_active
                    ? 'bg-orange-600 hover:bg-orange-700 text-white'
                    : 'bg-green-600 hover:bg-green-700 text-white'
                ]"
                            >
                                {{ user.is_active ? 'Desactivar Usuario' : 'Activar Usuario' }}
                            </button>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tiempo en el sistema</dt>
                                <dd class="text-sm text-gray-900">{{ getTimeInSystem(user.created_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ID de usuario</dt>
                                <dd class="text-sm text-gray-900">#{{ user.id }}</dd>
                            </div>
                        </dl>
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
    user: Object,
})

const toggleUserStatus = () => {
    router.patch(route('users.toggle-status', props.user.id))
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

const getTimeInSystem = (createdAt) => {
    const now = new Date()
    const created = new Date(createdAt)
    const diffTime = Math.abs(now - created)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    if (diffDays < 30) {
        return `${diffDays} días`
    } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30)
        return `${months} mes${months > 1 ? 'es' : ''}`
    } else {
        const years = Math.floor(diffDays / 365)
        return `${years} año${years > 1 ? 's' : ''}`
    }
}
</script>
