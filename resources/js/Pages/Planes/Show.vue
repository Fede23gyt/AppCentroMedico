<template>
    <Head :title="`Plan: ${props.plan?.nombre || 'Cargando...'}`" />

    <AuthenticatedLayout>
        <div v-if="props.plan" class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <Link
                            :href="route('planes.index')"
                            class="text-gray-600 hover:text-gray-800"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900">{{ props.plan.nombre }}</h1>
                        <span
                            :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                getEstadoClass(props.plan.estado || 'activo')
                            ]"
                        >
                            {{ capitalizeFirst(props.plan.estado || 'activo') }}
                        </span>
                    </div>
                    <p class="text-gray-600">Detalles del plan de salud</p>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Información del Plan -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg shadow-sm border border-gray-600 p-6" style="background-color: #2D6660;">
                        <h2 class="text-lg font-semibold text-white mb-4">Información del Plan</h2>

                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-200">Nombre</dt>
                                <dd class="mt-1 text-sm text-white">{{ props.plan.nombre }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-200">Código Corto</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-2 py-1 rounded inline-block">
                                    {{ props.plan.nombre_corto }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-200">Vigencia</dt>
                                <dd class="mt-1 text-sm text-white">
                                    <div class="flex items-center mb-1">
                                        <svg class="h-4 w-4 mr-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Desde: {{ formatDate(props.plan.vigencia_desde) }}</span>
                                    </div>
                                    <div v-if="props.plan.vigencia_hasta" class="flex items-center">
                                        <svg class="h-4 w-4 mr-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Hasta: {{ formatDate(props.plan.vigencia_hasta) }}</span>
                                    </div>
                                    <div v-else class="flex items-center text-green-200">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Sin vencimiento</span>
                                    </div>
                                </dd>
                            </div>

                            <div v-if="props.plan.descripcion">
                                <dt class="text-sm font-medium text-gray-200">Descripción</dt>
                                <dd class="mt-1 text-sm text-white">{{ props.plan.descripcion }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-200">Estado</dt>
                                <dd class="mt-1">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getEstadoClass(props.plan.estado || 'activo')
                                        ]"
                                    >
                                        {{ capitalizeFirst(props.plan.estado || 'activo') }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg shadow-sm border border-gray-600 p-6" style="background-color: #2D6660;">
                        <h3 class="text-lg font-semibold text-white mb-4">Estadísticas</h3>

                        <div class="bg-white rounded-lg p-4 border-2 border-gray-300">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ props.plan.prestaciones?.length || 0 }}</div>
                                    <div class="text-sm text-gray-500">Prestaciones</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ prestacionesActivas }}</div>
                                    <div class="text-sm text-gray-500">Activas</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="estadisticasPorRubro.length > 0" class="mt-4 bg-white rounded-lg p-4 border-2 border-gray-300">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Por Rubro</h4>
                            <div class="space-y-2">
                                <div
                                    v-for="rubro in estadisticasPorRubro"
                                    :key="rubro.id"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">{{ rubro.nombre }}</span>
                                    <span class="font-medium">{{ rubro.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sucursales Asignadas -->
            <div class="mt-6">
                <div class="rounded-lg shadow-sm border border-gray-600" style="background-color: #2D6660;">
                    <div class="px-6 py-4 border-b border-gray-400">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-semibold text-white">Sucursales Asignadas</h2>
                                <p class="text-sm text-gray-200 mt-1">
                                    Sucursales donde este plan está disponible
                                </p>
                            </div>
                            <Link
                                :href="route('planes.edit', props.plan.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Gestionar Sucursales
                            </Link>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="bg-white rounded-lg p-4 border-2 border-gray-300">
                            <div v-if="props.plan.sucursales && props.plan.sucursales.length > 0">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div
                                        v-for="sucursal in props.plan.sucursales"
                                        :key="sucursal.id"
                                        class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-medium text-gray-900">{{ sucursal.nombre }}</h3>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    Código: <span class="font-mono">{{ sucursal.codigo }}</span>
                                                </p>
                                                <div v-if="sucursal.pivot" class="mt-2 space-y-1">
                                                    <div v-if="sucursal.pivot.fecha_desde" class="flex items-center text-xs text-gray-500">
                                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        Desde: {{ formatDate(sucursal.pivot.fecha_desde) }}
                                                    </div>
                                                    <span
                                                        :class="[
                                                            'inline-flex px-2 py-0.5 text-xs font-semibold rounded-full',
                                                            sucursal.pivot.estado === 'activo'
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-red-100 text-red-800'
                                                        ]"
                                                    >
                                                        {{ capitalizeFirst(sucursal.pivot.estado) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <Link
                                                :href="route('sucursales.show', sucursal.id)"
                                                class="text-blue-600 hover:text-blue-800 ml-2"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                </svg>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Este plan no tiene sucursales asignadas</p>
                                <p class="text-xs text-gray-400 mt-1">Haz clic en "Gestionar Sucursales" para asignar sucursales</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Requeridos -->
            <div class="mt-6">
                <div class="rounded-lg shadow-sm border border-gray-600" style="background-color: #2D6660;">
                    <div class="px-6 py-4 border-b border-gray-400">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-semibold text-white">Items Requeridos</h2>
                                <p class="text-sm text-gray-200 mt-1">
                                    Códigos de items necesarios para identificar este plan
                                </p>
                            </div>
                            <Link
                                :href="route('planes.edit', props.plan.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Gestionar Items
                            </Link>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="bg-white rounded-lg p-4 border-2 border-gray-300">
                            <div v-if="combinacionesItems.length > 0">
                                <!-- Mostrar combinaciones agrupadas -->
                                <div class="space-y-4">
                                    <div
                                        v-for="(combinacion, index) in combinacionesItems"
                                        :key="combinacion.grupo"
                                        class="border border-gray-200 rounded-lg p-4 bg-gray-50"
                                    >
                                        <!-- Encabezado de la combinación -->
                                        <div class="mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                                                    Combinación #{{ index + 1 }}
                                                </span>
                                                <h4 class="text-sm font-semibold text-gray-900">
                                                    {{ combinacion.grupo }}
                                                </h4>
                                            </div>
                                            <p v-if="combinacion.descripcion" class="text-xs text-gray-600 mt-1 ml-1">
                                                {{ combinacion.descripcion }}
                                            </p>
                                        </div>

                                        <!-- Items de la combinación -->
                                        <div class="flex flex-wrap gap-2">
                                            <div
                                                v-for="item in combinacion.items"
                                                :key="item.id"
                                                class="inline-flex items-center px-3 py-1.5 bg-white border border-blue-200 rounded-md"
                                            >
                                                <span class="font-mono text-sm font-medium text-blue-900">
                                                    {{ item.item_codigo }}
                                                </span>
                                                <span
                                                    v-if="!item.es_obligatorio"
                                                    class="ml-2 px-1.5 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded"
                                                    title="Opcional"
                                                >
                                                    Opcional
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info adicional -->
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <p class="text-xs text-blue-800">
                                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        El afiliado será asignado a este plan si sus items coinciden con <strong>AL MENOS UNA</strong> de estas combinaciones completas.
                                    </p>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Este plan no tiene combinaciones de items configuradas</p>
                                <p class="text-xs text-gray-400 mt-1">Haz clic en "Gestionar Items" para agregar combinaciones</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de carga -->
        <div v-else class="max-w-7xl mx-auto px-4 py-6 text-center">
            <div class="bg-white rounded-lg shadow p-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Cargando plan...</h3>
                <p class="text-gray-500">Por favor espera un momento</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, reactive } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    plan: Object,
})

// Computed: Agrupar items por combinación
const combinacionesItems = computed(() => {
    // Inertia convierte itemsRequeridos a items_requeridos
    const items = props.plan?.items_requeridos || props.plan?.itemsRequeridos || []

    if (!items || items.length === 0) return []

    const grupos = {}
    items.forEach(item => {
        const grupo = item.grupo || 'default'
        if (!grupos[grupo]) {
            grupos[grupo] = {
                grupo: grupo,
                descripcion: item.descripcion_grupo,
                items: []
            }
        }
        grupos[grupo].items.push(item)
    })

    return Object.values(grupos)
})

// Estado reactivo
const showDesasociarModal = ref(false)
const prestacionToDesasociar = ref(null)
const filtroEstado = ref('')
const filtroRubro = ref('')
const paginaActual = ref(1)
const itemsPorPagina = 10

// Computadas
const prestacionesActivas = computed(() => {
    if (!props.plan?.prestaciones) return 0
    return props.plan.prestaciones.filter(p => (p.pivot?.estado || 'activo') === 'activo').length
})

const estadisticasPorRubro = computed(() => {
    if (!props.plan?.prestaciones) return []

    const rubros = {}
    props.plan.prestaciones.forEach(prestacion => {
        if (prestacion.rubro) {
            if (!rubros[prestacion.rubro.id]) {
                rubros[prestacion.rubro.id] = {
                    id: prestacion.rubro.id,
                    nombre: prestacion.rubro.nombre,
                    count: 0
                }
            }
            rubros[prestacion.rubro.id].count++
        }
    })

    return Object.values(rubros).sort((a, b) => b.count - a.count)
})

const rubrosDisponibles = computed(() => {
    if (!props.plan?.prestaciones) return []
    
    const rubros = new Map()
    props.plan.prestaciones.forEach(prestacion => {
        if (prestacion.rubro) {
            rubros.set(prestacion.rubro.id, prestacion.rubro)
        }
    })
    return Array.from(rubros.values()).sort((a, b) => a.nombre.localeCompare(b.nombre))
})

const prestacionesFiltradas = computed(() => {
    if (!props.plan?.prestaciones) return []

    return props.plan.prestaciones.filter(prestacion => {
        // Filtro por estado
        if (filtroEstado.value) {
            const estado = prestacion.pivot?.estado || 'activo'
            if (estado !== filtroEstado.value) return false
        }

        // Filtro por rubro
        if (filtroRubro.value) {
            if (prestacion.rubro?.id !== parseInt(filtroRubro.value)) return false
        }

        return true
    })
})

const totalPaginas = computed(() => {
    return Math.ceil(prestacionesFiltradas.value.length / itemsPorPagina)
})

const prestacionesPaginadas = computed(() => {
    const inicio = (paginaActual.value - 1) * itemsPorPagina
    const fin = inicio + itemsPorPagina
    return prestacionesFiltradas.value.slice(inicio, fin)
})

const paginasVisibles = computed(() => {
    const total = totalPaginas.value
    const actual = paginaActual.value
    const paginas = []

    const inicio = Math.max(1, actual - 2)
    const fin = Math.min(total, actual + 2)

    for (let i = inicio; i <= fin; i++) {
        paginas.push(i)
    }

    return paginas
})

// Utilidades
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-AR')
}

const formatCurrency = (value) => {
    if (!value) return '0.00'
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value)
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
    if (!str || typeof str !== 'string') return ''
    return str.charAt(0).toUpperCase() + str.slice(1)
}

// Funciones
const editarAsociacion = (prestacion) => {
    // Redirigir a la página de gestión de prestaciones del plan
    if (props.plan?.id) {
        router.visit(route('planes.prestaciones.index', props.plan.id), {
            state: { editando: prestacion.id }
        })
    }
}

const confirmarDesasociacion = (prestacion) => {
    prestacionToDesasociar.value = prestacion
    showDesasociarModal.value = true
}

const desasociarPrestacion = () => {
    if (!prestacionToDesasociar.value || !props.plan?.id) return

    router.delete(route('planes.prestaciones.destroy', [props.plan.id, prestacionToDesasociar.value.id]), {
        onSuccess: () => {
            showDesasociarModal.value = false
            prestacionToDesasociar.value = null
        }
    })
}
</script>
