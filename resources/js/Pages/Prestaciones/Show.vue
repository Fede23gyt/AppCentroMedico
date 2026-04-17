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

                            <h4 class="text-md font-semibold text-white mt-6 mb-3">Escala de Precios (Niveles)</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                    <dt class="text-xs font-bold text-gray-500 uppercase">Precio 1</dt>
                                    <dd class="text-lg font-bold text-gray-900">${{ formatCurrency(prestacion.precio_1 || 0) }}</dd>
                                </div>
                                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                    <dt class="text-xs font-bold text-gray-500 uppercase">Precio 2</dt>
                                    <dd class="text-lg font-bold text-gray-900">${{ formatCurrency(prestacion.precio_2 || 0) }}</dd>
                                </div>
                                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                    <dt class="text-xs font-bold text-gray-500 uppercase">Precio 3</dt>
                                    <dd class="text-lg font-bold text-gray-900">${{ formatCurrency(prestacion.precio_3 || 0) }}</dd>
                                </div>
                                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                    <dt class="text-xs font-bold text-gray-500 uppercase">Precio 4</dt>
                                    <dd class="text-lg font-bold text-gray-900">${{ formatCurrency(prestacion.precio_4 || 0) }}</dd>
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

            <!-- Excepciones y Mapeo -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Excepciones por sucursal -->
                <div class="rounded-lg shadow-sm border border-gray-600 p-8" style="background-color: #2D6660;">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-white">Excepciones por Sucursal</h3>
                        <Link :href="route('precios-sucursales.create', { prestacion_id: prestacion.id })" class="text-xs bg-white text-gray-800 px-2 py-1 rounded">
                            Añadir
                        </Link>
                    </div>

                    <div v-if="prestacion.precios_sucursales && prestacion.precios_sucursales.length > 0" class="space-y-4">
                        <div v-for="exc in prestacion.precios_sucursales" :key="exc.id" class="bg-gray-50 rounded p-3">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-2">
                                <span class="font-bold text-gray-800">{{ exc.sucursal.nombre }}</span>
                                <Link :href="route('precios-sucursales.edit', exc.id)" class="text-xs text-blue-600 hover:text-blue-800">
                                    Editar
                                </Link>
                            </div>
                            <div class="grid grid-cols-4 gap-2 text-xs text-center">
                                <div><div class="text-gray-500 mb-1">P1</div><div class="font-bold">${{ formatCurrency(exc.precio_1) }}</div></div>
                                <div><div class="text-gray-500 mb-1">P2</div><div class="font-bold">${{ formatCurrency(exc.precio_2) }}</div></div>
                                <div><div class="text-gray-500 mb-1">P3</div><div class="font-bold">${{ formatCurrency(exc.precio_3) }}</div></div>
                                <div><div class="text-gray-500 mb-1">P4</div><div class="font-bold">${{ formatCurrency(exc.precio_4) }}</div></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-300 italic">
                        No hay excepciones registradas para esta prestación.
                    </div>
                </div>

                <!-- Historial de Precios Base -->
                <div class="rounded-lg shadow-sm border border-gray-600 p-8 bg-white">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de Precios Base</h3>
                    
                    <div v-if="prestacion.historial_precios && prestacion.historial_precios.length > 0" class="space-y-4 max-h-96 overflow-y-auto pr-2">
                        <div v-for="historial in prestacion.historial_precios" :key="historial.id" class="border-l-4 border-blue-500 pl-3 py-1 bg-gray-50 rounded-r-md">
                            <div class="flex justify-between items-center text-sm mb-1">
                                <span class="font-bold text-gray-800">Nivel P{{ historial.nivel_precio }}</span>
                                <span class="text-xs text-gray-500 bg-gray-200 px-2 rounded">{{ formatShortDateTime(historial.created_at) }}</span>
                            </div>
                            <div class="text-sm text-gray-600 mb-1 flex items-center">
                                <span class="line-through text-red-500 mr-2">${{ formatCurrency(historial.valor_anterior) }}</span>
                                <span class="font-bold text-green-600">→ ${{ formatCurrency(historial.valor_nuevo) }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-2 text-xs text-gray-400">
                                <span>Por: {{ historial.user?.name || 'Sistema' }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500 italic">
                        No hay registros en el historial. El historial comenzó a registrarse en la última actualización.
                    </div>
                </div>
            </div>

            <!-- Límites Semestrales/Mensuales por Cobertura -->
            <div class="mt-8 rounded-lg shadow-sm border border-gray-600 p-8" style="background-color: #2b3a4a;">
                <h3 class="text-lg font-semibold text-white mb-4">Límites y Topes Gratuitos Mensuales</h3>
                
                <!-- Lista de límites cargados -->
                <div v-if="limites && limites.length > 0" class="mb-6 space-y-3">
                    <div v-for="limite in limites" :key="limite.id" class="bg-gray-50 flex justify-between items-center rounded p-3">
                        <div>
                            <span class="font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded text-sm">
                                Cobertura: {{ limite.cobertura_codigo || 'Genérica (Aplica a todas)' }}
                            </span>
                            <div class="text-sm mt-1 text-gray-700">
                                Tope Individual: <span class="font-bold">{{ limite.limite_mensual_individual || '0' }}</span> al mes | 
                                Tope Familiar: <span class="font-bold">{{ limite.limite_mensual_familia || '0' }}</span> al mes
                            </div>
                        </div>
                        <button @click="eliminarLimite(limite.id)" class="text-xs text-red-600 hover:text-red-800 bg-red-50 p-2 rounded">
                            Eliminar
                        </button>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-300 italic mb-6">
                    No hay topes gratuitos configurados para esta prestación.
                </div>

                <!-- Formulario de nuevo límite -->
                <div class="bg-gray-800 p-4 rounded-md border border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-300 mb-3">Agregar Nuevo Límite</h4>
                    <form @submit.prevent="guardarLimite" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Cobertura</label>
                            <select v-model="formLimite.cobertura_codigo" class="w-full text-sm rounded bg-gray-700 text-white border-gray-600 focus:ring-blue-500">
                                <option :value="null">Genérica (Todas)</option>
                                <option v-for="cob in coberturasDisponibles" :key="cob" :value="cob">{{ cob }}</option>
                            </select>
                            <div v-if="formLimite.errors.cobertura_codigo" class="text-xs text-red-400 mt-1">{{ formLimite.errors.cobertura_codigo }}</div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Tope Individual (/mes)</label>
                            <input type="number" min="0" v-model="formLimite.limite_mensual_individual" class="w-full text-sm rounded bg-gray-700 text-white border-gray-600 focus:ring-blue-500" placeholder="Ej: 2" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Tope Familiar (/mes)</label>
                            <input type="number" min="0" v-model="formLimite.limite_mensual_familia" class="w-full text-sm rounded bg-gray-700 text-white border-gray-600 focus:ring-blue-500" placeholder="Ej: 4" />
                        </div>
                        <div>
                            <button type="submit" :disabled="formLimite.processing" class="w-full bg-blue-600 text-white text-sm py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                                Guardar Tope
                            </button>
                        </div>
                    </form>
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
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
    prestacion: Object,
    limites: Array,
    coberturasDisponibles: Array
})

const formLimite = useForm({
    prestacion_id: props.prestacion?.id,
    cobertura_codigo: null,
    limite_mensual_individual: 0,
    limite_mensual_familia: 0
});

const guardarLimite = () => {
    formLimite.post(route('limites-cobertura.store'), {
        preserveScroll: true,
        onSuccess: () => {
            formLimite.reset('cobertura_codigo', 'limite_mensual_individual', 'limite_mensual_familia');
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Tope guardado correctamente',
                showConfirmButton: false,
                timer: 3000
            });
        }
    });
};

const eliminarLimite = (id) => {
    Swal.fire({
        title: '¿Eliminar tope?',
        text: "Se borrará esta regla de gratuidad",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('limites-cobertura.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Tope eliminado',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        }
    });
};

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
const formatShortDateTime = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const day = date.getDate().toString().padStart(2, '0')
    const month = (date.getMonth() + 1).toString().padStart(2, '0')
    const year = date.getFullYear()
    const hours = date.getHours().toString().padStart(2, '0')
    const minutes = date.getMinutes().toString().padStart(2, '0')
    return `${day}/${month}/${year} ${hours}:${minutes}`
}
</script>
