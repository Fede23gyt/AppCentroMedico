<template>
    <Head :title="`Editar Plan: ${plan?.nombre || 'Cargando...'}`" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
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
                        <h1 class="text-2xl font-bold text-gray-900">Editar Plan</h1>
                    </div>
                    <p class="text-gray-600">Modifica los datos del plan de salud</p>
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

            <!-- Formulario -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Plan
                            </label>
                            <input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                readonly
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"
                            />
                            <p class="text-sm text-gray-500 mt-1">El nombre no se puede modificar</p>
                        </div>

                        <!-- Nombre Corto -->
                        <div>
                            <label for="nombre_corto" class="block text-sm font-medium text-gray-700 mb-2">
                                Código Corto
                            </label>
                            <input
                                id="nombre_corto"
                                v-model="form.nombre_corto"
                                type="text"
                                readonly
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed font-mono"
                            />
                            <p class="text-sm text-gray-500 mt-1">El código no se puede modificar</p>
                        </div>

                        <!-- Vigencia Desde -->
                        <div>
                            <label for="vigencia_desde" class="block text-sm font-medium text-gray-700 mb-2">
                                Vigencia Desde <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="vigencia_desde"
                                v-model="form.vigencia_desde"
                                type="date"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.vigencia_desde }"
                            />
                            <div v-if="form.errors.vigencia_desde" class="text-red-500 text-sm mt-1">
                                {{ form.errors.vigencia_desde }}
                            </div>
                        </div>

                        <!-- Vigencia Hasta -->
                        <div>
                            <label for="vigencia_hasta" class="block text-sm font-medium text-gray-700 mb-2">
                                Vigencia Hasta
                            </label>
                            <input
                                id="vigencia_hasta"
                                v-model="form.vigencia_hasta"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.vigencia_hasta }"
                            />
                            <div v-if="form.errors.vigencia_hasta" class="text-red-500 text-sm mt-1">
                                {{ form.errors.vigencia_hasta }}
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Dejar vacío para vigencia indefinida</p>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="estado"
                                v-model="form.estado"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.estado }"
                            >
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="suspendido">Suspendido</option>
                            </select>
                            <div v-if="form.errors.estado" class="text-red-500 text-sm mt-1">
                                {{ form.errors.estado }}
                            </div>
                        </div>

                        <!-- Porcentaje Salud -->
                        <div>
                            <label for="porc_salud" class="block text-sm font-medium text-gray-700 mb-2">
                                % Salud
                            </label>
                            <input
                                id="porc_salud"
                                v-model="form.porc_salud"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.porc_salud }"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.porc_salud" class="text-red-500 text-sm mt-1">
                                {{ form.errors.porc_salud }}
                            </div>
                        </div>

                        <!-- Porcentaje Odontología -->
                        <div>
                            <label for="porc_odo" class="block text-sm font-medium text-gray-700 mb-2">
                                % Odontología
                            </label>
                            <input
                                id="porc_odo"
                                v-model="form.porc_odo"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.porc_odo }"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.porc_odo" class="text-red-500 text-sm mt-1">
                                {{ form.errors.porc_odo }}
                            </div>
                        </div>

                        <!-- Porcentaje Orden -->
                        <div>
                            <label for="porc_ord" class="block text-sm font-medium text-gray-700 mb-2">
                                % Orden
                            </label>
                            <input
                                id="porc_ord"
                                v-model="form.porc_ord"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.porc_ord }"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.porc_ord" class="text-red-500 text-sm mt-1">
                                {{ form.errors.porc_ord }}
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mt-6">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea
                            id="descripcion"
                            v-model="form.descripcion"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.descripcion }"
                            placeholder="Descripción opcional del plan"
                        ></textarea>
                        <div v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">
                            {{ form.errors.descripcion }}
                        </div>
                    </div>

                    <!-- Sucursales -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sucursales Asignadas
                        </label>
                        <div class="border border-gray-300 rounded-md p-4 bg-gray-50 max-h-64 overflow-y-auto">
                            <div v-if="sucursalesDisponibles && sucursalesDisponibles.length > 0" class="space-y-2">
                                <label
                                    v-for="sucursal in sucursalesDisponibles"
                                    :key="sucursal.id"
                                    class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="sucursal.id"
                                        v-model="form.sucursales"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="text-sm text-gray-700">
                                        <span class="font-medium">{{ sucursal.nombre }}</span>
                                        <span class="text-gray-500 ml-2">({{ sucursal.codigo }})</span>
                                    </span>
                                </label>
                            </div>
                            <div v-else class="text-sm text-gray-500 text-center py-4">
                                No hay sucursales disponibles
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            Selecciona las sucursales donde este plan estará disponible
                        </p>
                    </div>

                    <!-- Items Requeridos - Combinaciones -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Combinaciones de Items Requeridos
                        </label>
                        <p class="text-xs text-gray-500 mb-3">
                            Un plan puede tener múltiples combinaciones de items (ej: planes nuevos vs viejos). El afiliado será asignado si coincide con AL MENOS UNA combinación completa.
                        </p>

                        <!-- Debug -->
                        <div v-if="false" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs">
                            <div>Plan ID: {{ plan?.id }}</div>
                            <div>Items raw: {{ JSON.stringify(plan?.items_requeridos) }}</div>
                            <div>Combinaciones: {{ combinacionesItems.length }}</div>
                        </div>

                        <!-- Combinaciones existentes -->
                        <div v-if="combinacionesItems.length > 0" class="space-y-4 mb-4">
                            <div
                                v-for="(combinacion, index) in combinacionesItems"
                                :key="combinacion.grupo"
                                class="border border-gray-300 rounded-md p-4"
                                :class="combinacionSeleccionada === combinacion.grupo ? 'bg-blue-50 border-blue-400' : 'bg-gray-50'"
                            >
                                <!-- Encabezado de la combinación -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900">
                                            Combinación #{{ index + 1 }}: {{ combinacion.grupo }}
                                        </h4>
                                        <p v-if="combinacion.descripcion" class="text-xs text-gray-600 mt-1">
                                            {{ combinacion.descripcion }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="seleccionarCombinacion(combinacion.grupo)"
                                        class="px-3 py-1 text-xs border rounded"
                                        :class="combinacionSeleccionada === combinacion.grupo
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'"
                                    >
                                        {{ combinacionSeleccionada === combinacion.grupo ? 'Seleccionada' : 'Seleccionar' }}
                                    </button>
                                </div>

                                <!-- Items de la combinación -->
                                <div class="flex flex-wrap gap-2">
                                    <div
                                        v-for="item in combinacion.items"
                                        :key="item.id"
                                        class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md"
                                    >
                                        <span class="font-mono text-sm font-medium text-gray-900">
                                            {{ item.item_codigo }}
                                        </span>
                                        <button
                                            type="button"
                                            @click="eliminarItem(item.id)"
                                            class="ml-2 text-red-600 hover:text-red-800"
                                            :disabled="eliminandoItem === item.id"
                                            title="Eliminar item"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario para agregar nuevo item -->
                        <div class="border border-gray-300 rounded-md p-4 bg-white">
                            <h5 class="text-sm font-medium text-gray-700 mb-3">
                                {{ modoNuevaCombinacion ? 'Crear Nueva Combinación' : 'Agregar Item a Combinación' }}
                            </h5>

                            <!-- Toggle entre nueva combinación o agregar a existente -->
                            <div class="mb-3">
                                <button
                                    type="button"
                                    @click="toggleModoNuevaCombinacion"
                                    class="text-sm text-blue-600 hover:text-blue-800 underline"
                                >
                                    {{ modoNuevaCombinacion ? 'Agregar a combinación existente' : 'Crear nueva combinación' }}
                                </button>
                            </div>

                            <!-- Campos del formulario -->
                            <div class="space-y-3">
                                <!-- Selector o input de grupo -->
                                <div v-if="!modoNuevaCombinacion && combinacionesItems.length > 0">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Combinación</label>
                                    <select
                                        v-model="grupoSeleccionado"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    >
                                        <option value="">Seleccionar combinación...</option>
                                        <option v-for="(comb, index) in combinacionesItems" :key="comb.grupo" :value="comb.grupo">
                                            Combinación #{{ index + 1 }}: {{ comb.grupo }}
                                        </option>
                                    </select>
                                </div>

                                <div v-else>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nombre de la Combinación</label>
                                    <input
                                        v-model="nuevoGrupo"
                                        type="text"
                                        placeholder="Ej: combinacion_nueva, plan_viejo"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                        maxlength="100"
                                    />
                                </div>

                                <!-- Descripción (solo para nueva combinación) -->
                                <div v-if="modoNuevaCombinacion">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Descripción (opcional)</label>
                                    <input
                                        v-model="nuevaDescripcion"
                                        type="text"
                                        placeholder="Ej: Combinación para planes actuales"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    />
                                </div>

                                <!-- Item código -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Código del Item</label>
                                    <div class="flex gap-2">
                                        <input
                                            v-model="nuevoItem"
                                            type="text"
                                            placeholder="Ej: NPIEVE, NIVELX7, PRESTA0"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase text-sm"
                                            @keyup.enter="agregarItem"
                                            maxlength="50"
                                        />
                                        <button
                                            type="button"
                                            @click="agregarItem"
                                            :disabled="!puedeAgregarItem || agregandoItem"
                                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                        >
                                            <span v-if="agregandoItem">
                                                <svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                            <span v-else>Agregar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <Link
                            :href="route('planes.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
                        >
                            <span v-if="form.processing">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Guardando...
                            </span>
                            <span v-else>
                                Actualizar Plan
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { watch, ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    plan: Object,
    sucursalesDisponibles: Array,
})

// Estado para items y combinaciones
const nuevoItem = ref('')
const agregandoItem = ref(false)
const eliminandoItem = ref(null)
const modoNuevaCombinacion = ref(true)
const nuevoGrupo = ref('')
const nuevaDescripcion = ref('')
const grupoSeleccionado = ref('')
const combinacionSeleccionada = ref('')

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

// Computed: Verificar si se puede agregar el item
const puedeAgregarItem = computed(() => {
    if (!nuevoItem.value) return false

    if (modoNuevaCombinacion.value) {
        return nuevoGrupo.value.trim() !== ''
    } else {
        return grupoSeleccionado.value !== ''
    }
})

// Formatear fecha para input type="date"
const formatDateForInput = (dateString) => {
    if (!dateString) return ''
    return dateString.split(' ')[0] // tomar solo la parte de fecha
}

const form = useForm({
    nombre: props.plan?.nombre || '',
    nombre_corto: props.plan?.nombre_corto || '',
    vigencia_desde: formatDateForInput(props.plan?.vigencia_desde) || '',
    vigencia_hasta: formatDateForInput(props.plan?.vigencia_hasta) || '',
    estado: props.plan?.estado || 'activo',
    descripcion: props.plan?.descripcion || '',
    porc_salud: props.plan?.porc_salud || null,
    porc_odo: props.plan?.porc_odo || null,
    porc_ord: props.plan?.porc_ord || null,
    sucursales: props.plan?.sucursales?.map(s => s.id) || [],
})

// Actualizar formulario cuando cambien las props
watch(() => props.plan, (newPlan) => {
    if (newPlan) {
        form.nombre = newPlan.nombre || ''
        form.nombre_corto = newPlan.nombre_corto || ''
        form.vigencia_desde = formatDateForInput(newPlan.vigencia_desde) || ''
        form.vigencia_hasta = formatDateForInput(newPlan.vigencia_hasta) || ''
        form.estado = newPlan.estado || 'activo'
        form.descripcion = newPlan.descripcion || ''
        form.porc_salud = newPlan.porc_salud || null
        form.porc_odo = newPlan.porc_odo || null
        form.porc_ord = newPlan.porc_ord || null
        form.sucursales = newPlan.sucursales?.map(s => s.id) || []
    }
}, { immediate: true })

const submit = () => {
    if (!props.plan?.id) {
        console.error('Plan ID no disponible')
        return
    }

    form.put(route('planes.update', props.plan.id), {
        onSuccess: () => {
            // Redirigir será manejado por el controlador
        }
    })
}

// Funciones para manejar combinaciones
const toggleModoNuevaCombinacion = () => {
    modoNuevaCombinacion.value = !modoNuevaCombinacion.value

    // Si cambiamos a agregar a existente y hay combinaciones, seleccionar la primera
    if (!modoNuevaCombinacion.value && combinacionesItems.value.length > 0) {
        grupoSeleccionado.value = combinacionesItems.value[0].grupo
    }

    // Si no hay combinaciones, forzar modo nueva
    if (combinacionesItems.value.length === 0) {
        modoNuevaCombinacion.value = true
    }
}

const seleccionarCombinacion = (grupo) => {
    if (combinacionSeleccionada.value === grupo) {
        combinacionSeleccionada.value = ''
    } else {
        combinacionSeleccionada.value = grupo
        grupoSeleccionado.value = grupo
        modoNuevaCombinacion.value = false
    }
}

// Funciones para items
const agregarItem = () => {
    if (!puedeAgregarItem.value || agregandoItem.value || !props.plan?.id) return

    agregandoItem.value = true

    const grupo = modoNuevaCombinacion.value ? nuevoGrupo.value.trim() : grupoSeleccionado.value
    const descripcion = modoNuevaCombinacion.value ? nuevaDescripcion.value : null

    router.post(route('planes.items.store', props.plan.id), {
        item_codigo: nuevoItem.value.trim().toUpperCase(),
        es_obligatorio: true,
        grupo: grupo,
        descripcion_grupo: descripcion
    }, {
        preserveScroll: true,
        preserveState: false, // Forzar la recarga de datos
        onSuccess: () => {
            nuevoItem.value = ''
            if (modoNuevaCombinacion.value) {
                // Después de crear la primera item de una nueva combinación, cambiar a modo agregar
                grupoSeleccionado.value = grupo
                modoNuevaCombinacion.value = false
                nuevaDescripcion.value = ''
            }
        },
        onFinish: () => {
            agregandoItem.value = false
        }
    })
}

const eliminarItem = (itemId) => {
    if (!props.plan?.id || eliminandoItem.value) return

    if (!confirm('¿Estás seguro de eliminar este item de la combinación?')) return

    eliminandoItem.value = itemId

    router.delete(route('planes.items.destroy', [props.plan.id, itemId]), {
        preserveScroll: true,
        preserveState: false, // Forzar la recarga de datos
        onFinish: () => {
            eliminandoItem.value = null
        }
    })
}
</script>