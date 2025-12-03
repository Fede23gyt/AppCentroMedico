<template>
    <Head :title="`Prestador: ${prestador.apellido}, ${prestador.nombre}`" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('prestadores.index')"
                        class="text-gray-600 hover:text-gray-800"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ prestador.apellido }}, {{ prestador.nombre }}
                        </h1>
                        <p class="text-gray-600">Información del prestador</p>
                    </div>
                    <span
                        :class="[
                            'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                            getEstadoClass(prestador.estado)
                        ]"
                    >
                        {{ capitalizeFirst(prestador.estado) }}
                    </span>
                </div>
            </div>

            <!-- Datos del Prestador -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Datos Personales</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Apellido</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.apellido }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">DNI</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ prestador.dni }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">CUIL</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ prestador.cuil }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.mail || 'No especificado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.telefono || 'No especificado' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Domicilio</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.domicilio || 'No especificado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Alta</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(prestador.fecha_alta) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Usuario Alta</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.usuario_alta || 'Sistema' }}</dd>
                    </div>
                    <div v-if="prestador.fecha_baja">
                        <dt class="text-sm font-medium text-gray-500">Fecha de Baja</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(prestador.fecha_baja) }}</dd>
                    </div>
                    <div v-if="prestador.usuario_baja">
                        <dt class="text-sm font-medium text-gray-500">Usuario Baja</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.usuario_baja }}</dd>
                    </div>
                    <div v-if="prestador.observaciones" class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Observaciones</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ prestador.observaciones }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Sucursales Asignadas -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Sucursales Asignadas</h2>
                <div v-if="prestador.sucursales && prestador.sucursales.length > 0" class="space-y-3">
                    <div v-for="sucursal in prestador.sucursales" :key="sucursal.id"
                         class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ sucursal.nombre }}</p>
                            <p class="text-sm text-gray-500">Código: {{ sucursal.codigo }}</p>
                        </div>
                        <span
                            :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                sucursal.pivot.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            ]"
                        >
                            {{ capitalizeFirst(sucursal.pivot.estado) }}
                        </span>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-gray-500">
                    No tiene sucursales asignadas
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-3">
                <Link
                    :href="route('prestadores.index')"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Volver
                </Link>
                <Link
                    :href="route('prestadores.edit', prestador.id)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                    Editar
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    prestador: Object,
})

const formatDate = (dateString) => {
    if (!dateString) return 'No disponible'
    const date = new Date(dateString)
    return date.toLocaleDateString('es-AR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
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
