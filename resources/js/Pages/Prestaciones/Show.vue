<template>
    <Head :title="`Prestación: ${prestacion?.codigo} - ${prestacion?.nombre}`" />

    <AuthenticatedLayout>
        <div v-if="prestacion" class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <Link
                            :href="route('prestaciones.index')"
                            class="text-gray-600 hover:text-gray-800"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900">Detalle de Prestación</h1>
                        <span
                            :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                getEstadoClass(prestacion.estado)
                            ]"
                        >
                            {{ capitalizeFirst(prestacion.estado) }}
                        </span>
                    </div>
                    <p class="text-gray-600">Información completa de la prestación médica</p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="route('prestaciones.index')"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                    >
                        Volver
                    </Link>
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

            <!-- Información Unificada -->
            <div class="rounded-lg shadow-sm border border-gray-600 p-8" style="background-color: #2D6660;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Columna Izquierda -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-4">Información Básica</h3>

                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-200">Código</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-1 rounded inline-block">
                                        {{ prestacion.codigo }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-200">Nombre</dt>
                                    <dd class="mt-1 text-sm text-white font-semibold">
                                        {{ prestacion.nombre }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-200">Rubro</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            {{ prestacion.rubro?.nombre || 'Sin rubro' }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-200">Estado</dt>
                                    <dd class="mt-1">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                getEstadoClass(prestacion.estado)
                                            ]"
                                        >
                                            {{ capitalizeFirst(prestacion.estado) }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <div v-if="prestacion.descripcion">
                            <dt class="text-sm font-medium text-gray-200 mb-2">Descripción</dt>
                            <dd class="text-sm text-gray-900 bg-white p-3 rounded">
                                {{ prestacion.descripcion }}
                            </dd>
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-4">Información Económica</h3>

                            <div class="bg-white rounded-lg p-4 border-2 border-gray-300">
                                <div class="space-y-4">
                                    <!-- Valor IPS y Porcentaje IPS en la misma fila -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Valor IPS</dt>
                                            <dd class="mt-1 text-lg font-bold text-green-600">
                                                ${{ formatCurrency(prestacion.valor_ips || 0) }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Porcentaje IPS</dt>
                                            <dd class="mt-1 text-lg font-bold text-purple-600">
                                                {{ prestacion.porc_ips || 0 }}%
                                            </dd>
                                        </div>
                                    </div>

                                    <div>
                                      <dt class="text-sm font-medium text-gray-500">Valor Referencia</dt>
                                      <dd class="mt-1 text-lg font-bold text-blue-600">
                                        ${{ formatCurrency(prestacion.val_ref || 0) }}
                                      </dd>
                                    </div>

                                    <div v-if="prestacion.uvr">
                                        <dt class="text-sm font-medium text-gray-500">UVR</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ prestacion.uvr }}
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-white mb-4">Información Adicional</h3>

                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-200">Creado</dt>
                                    <dd class="mt-1 text-sm text-white">
                                        {{ formatDate(prestacion.created_at) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-200">Última modificación</dt>
                                    <dd class="mt-1 text-sm text-white">
                                        {{ formatDate(prestacion.updated_at) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-200">Planes asociados</dt>
                                    <dd class="mt-1 text-sm">
                                        <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                            {{ planesAsociadosCount }} plan(es)
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <div v-if="prestacion.observaciones">
                            <dt class="text-sm font-medium text-gray-200 mb-2">Observaciones</dt>
                            <dd class="text-sm text-gray-900 bg-white p-3 rounded">
                                {{ prestacion.observaciones }}
                            </dd>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Estado de carga -->
        <div v-else class="max-w-4xl mx-auto px-4 py-6 text-center">
            <div class="bg-white rounded-lg shadow p-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Cargando prestación...</h3>
                <p class="text-gray-500">Por favor espera un momento</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    prestacion: Object,
})

// Computed properties
const planesAsociadosCount = computed(() => {
    return props.prestacion?.planes?.length || 0
})

// Funciones
const getEstadoClass = (estado) => {
    const classes = {
        'activo': 'bg-green-100 text-green-800',
        'inactivo': 'bg-red-100 text-red-800',
        'suspendido': 'bg-yellow-100 text-yellow-800'
    }
    return classes[estado] || 'bg-gray-100 text-gray-800'
}

const capitalizeFirst = (str) => {
    if (!str) return ''
    return str.charAt(0).toUpperCase() + str.slice(1)
}

const formatCurrency = (amount) => {
    if (!amount) return '0'
    return parseFloat(amount).toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    })
}

const formatDate = (dateString) => {
    if (!dateString) return 'No disponible'
    const date = new Date(dateString)
    return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
