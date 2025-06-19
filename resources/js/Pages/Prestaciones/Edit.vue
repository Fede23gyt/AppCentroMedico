<!-- resources/js/Pages/Prestaciones/Edit.vue -->
<template>
    <AuthenticatedLayout>
        <Head :title="`Editar Prestación - ${prestacion.nombre}`" />

        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiPencil"
                :title="`Editar Prestación #${prestacion.codigo}`"
                main
            >
                <div class="flex gap-2">
                    <BaseButton
                        :route-name="route('prestaciones.show', prestacion.id)"
                        :icon="mdiEye"
                        label="Ver"
                        color="info"
                        outline
                        rounded-full
                        small
                    />
                    <BaseButton
                        :route-name="route('prestaciones.index')"
                        :icon="mdiArrowLeft"
                        label="Volver"
                        color="contrast"
                        rounded-full
                        small
                    />
                </div>
            </SectionTitleLineWithButton>

            <!-- Estado de la prestación -->
            <div class="mb-4">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Estado actual:</span>
                    <span :class="getEstadoBadgeClass(prestacion.estado)"
                          class="px-2 py-1 rounded-full text-xs font-medium">
                        {{ getEstadoLabel(prestacion.estado) }}
                    </span>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="form.processing" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>

            <CardBox v-else form @submit.prevent="submit">
                <!-- Información Básica -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                        <Icon :path="mdiInformation" class="w-5 h-5 mr-2" />
                        Información Básica
                    </h3>

                    <div class="space-y-6">
                        <!-- Código y Estado en la misma fila -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Código de Prestación -->
                            <div class="flex items-center gap-4">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0">
                                    Código
                                </label>
                                <div class="flex-1 max-w-xs">
                                    <input
                                        :value="prestacion.codigo"
                                        type="text"
                                        readonly
                                        class="w-24 px-3 py-2 text-center font-mono text-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">No se puede modificar</p>
                                </div>
                            </div>

                            <!-- Estado como Checkbox -->
                            <div class="flex items-center gap-4">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0">
                                    Estado
                                </label>
                                <div class="flex-1 max-w-xs">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            v-model="activo"
                                            type="checkbox"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            Prestación Activa
                                        </span>
                                    </label>
                                    <div v-if="form.errors.estado" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.estado }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nombre de la Prestación -->
                        <div class="flex items-start gap-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1 max-w-md">
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    placeholder="Ej: Consulta médica general"
                                    required
                                    autofocus
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': form.errors.nombre }"
                                />
                                <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.nombre }}
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="flex items-start gap-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                                Descripción
                            </label>
                            <div class="flex-1 max-w-md">
                                <textarea
                                    v-model="form.descripcion"
                                    rows="3"
                                    placeholder="Describe en detalle qué incluye esta prestación..."
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-vertical"
                                    :class="{ 'border-red-500': form.errors.descripcion }"
                                ></textarea>
                                <div v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.descripcion }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Categorización -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                        <Icon :path="mdiTag" class="w-5 h-5 mr-2" />
                        Categorización
                    </h3>

                    <div class="flex items-start gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                            Rubro <span class="text-red-500">*</span>
                        </label>
                        <div class="flex-1 max-w-sm">
                            <select
                                v-model="form.rubro_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': form.errors.rubro_id }"
                            >
                                <option value="">Seleccione un rubro</option>
                                <option
                                    v-for="rubro in rubros"
                                    :key="rubro.id"
                                    :value="rubro.id"
                                >
                                    {{ rubro.nombre }}
                                </option>
                            </select>
                            <div v-if="form.errors.rubro_id" class="text-red-500 text-sm mt-1">
                                {{ form.errors.rubro_id }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Selecciona la categoría correspondiente</div>
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Precios -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                        <Icon :path="mdiCurrencyUsd" class="w-5 h-5 mr-2" />
                        Configuración de Precios
                    </h3>

                    <div class="space-y-6">
                        <!-- Precio General -->
                        <div class="flex items-start gap-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                                Precio General <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1 max-w-xs">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                    <input
                                        v-model="form.precio_general"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0"
                                        required
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': form.errors.precio_general }"
                                    />
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Precio base de la prestación</div>
                                <div v-if="form.errors.precio_general" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.precio_general }}
                                </div>
                            </div>
                        </div>

                        <!-- Valor IPS -->
                        <div class="flex items-start gap-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                                Valor IPS
                            </label>
                            <div class="flex-1 max-w-xs">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                    <input
                                        v-model="form.valor_ips"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': form.errors.valor_ips }"
                                    />
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Valor para Instituto de Previsión Social</div>
                                <div v-if="form.errors.valor_ips" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.valor_ips }}
                                </div>
                            </div>
                        </div>

                        <!-- Comparación de precios -->
                        <div v-if="hasChangedPrices" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Cambios en precios:</h4>
                            <div class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                <div v-if="form.precio_general != prestacion.precio_general">
                                    Precio General: ${{ formatCurrency(prestacion.precio_general) }} → ${{ formatCurrency(form.precio_general) }}
                                </div>
                                <div v-if="form.valor_ips != prestacion.valor_ips">
                                    Valor IPS: ${{ formatCurrency(prestacion.valor_ips) }} → ${{ formatCurrency(form.valor_ips) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Información Adicional -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                        <Icon :path="mdiNoteText" class="w-5 h-5 mr-2" />
                        Información Adicional
                    </h3>

                    <div class="flex items-start gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                            Observaciones
                        </label>
                        <div class="flex-1 max-w-md">
                            <textarea
                                v-model="form.observaciones"
                                rows="3"
                                placeholder="Ej: Requiere ayuno de 12 horas, incluye material descartable..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-vertical"
                                :class="{ 'border-red-500': form.errors.observaciones }"
                            ></textarea>
                            <div class="text-xs text-gray-500 mt-1">Notas, restricciones o información especial</div>
                            <div v-if="form.errors.observaciones" class="text-red-500 text-sm mt-1">
                                {{ form.errors.observaciones }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de auditoría -->
                <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Información de registro:</h4>
                    <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                        <div>Creado: {{ formatDate(prestacion.created_at) }}</div>
                        <div v-if="prestacion.updated_at !== prestacion.created_at">
                            Última modificación: {{ formatDate(prestacion.updated_at) }}
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Botones de acción -->
                <div class="flex justify-end items-center gap-3 pt-6">
                    <button
                        type="button"
                        @click="$inertia.visit(route('prestaciones.index'))"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <Icon :path="mdiClose" class="w-4 h-4" />
                        Cancelar
                    </button>

                    <button
                        type="button"
                        @click="$inertia.visit(route('prestaciones.show', prestacion.id))"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-300 rounded-md shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-200 dark:border-blue-700 dark:hover:bg-blue-800"
                    >
                        <Icon :path="mdiEye" class="w-4 h-4" />
                        Ver Prestación
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing || !hasChanges"
                        :class="[
                            'inline-flex items-center gap-2 px-6 py-2 text-sm font-medium text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500',
                            form.processing || !hasChanges
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-green-600 hover:bg-green-700'
                        ]"
                    >
                        <Icon
                            :path="form.processing ? mdiLoading : mdiCheck"
                            :spin="form.processing"
                            class="w-4 h-4"
                        />
                        {{ form.processing ? 'Guardando...' : 'Actualizar' }}
                    </button>
                </div>
            </CardBox>
        </SectionMain>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import {
    mdiPencil,
    mdiArrowLeft,
    mdiEye,
    mdiInformation,
    mdiTag,
    mdiCurrencyUsd,
    mdiNoteText,
    mdiCheck,
    mdiClose,
    mdiLoading
} from '@mdi/js'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseDivider from '@/components/BaseDivider.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
    prestacion: Object,
    rubros: Array
})

const form = useForm({
    nombre: props.prestacion.nombre,
    descripcion: props.prestacion.descripcion,
    estado: props.prestacion.estado,
    rubro_id: props.prestacion.rubro_id,
    precio_general: props.prestacion.precio_general,
    valor_ips: props.prestacion.valor_ips,
    observaciones: props.prestacion.observaciones
})

// Campo reactivo para el checkbox basado en estado
const activo = computed({
    get: () => form.estado === 'activo',
    set: (value) => {
        form.estado = value ? 'activo' : 'inactivo'
    }
})

// Detectar cambios en el formulario
const hasChanges = computed(() => {
    return form.nombre !== props.prestacion.nombre ||
        form.descripcion !== props.prestacion.descripcion ||
        form.estado !== props.prestacion.estado ||
        form.rubro_id !== props.prestacion.rubro_id ||
        parseFloat(form.precio_general) !== parseFloat(props.prestacion.precio_general) ||
        parseFloat(form.valor_ips) !== parseFloat(props.prestacion.valor_ips) ||
        form.observaciones !== props.prestacion.observaciones
})

// Detectar cambios específicos en precios
const hasChangedPrices = computed(() => {
    return parseFloat(form.precio_general) !== parseFloat(props.prestacion.precio_general) ||
        parseFloat(form.valor_ips) !== parseFloat(props.prestacion.valor_ips)
})

// Funciones de utilidad
const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('es-AR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getEstadoLabel = (estado) => {
    const labels = {
        activo: 'Activo',
        inactivo: 'Inactivo',
        suspendido: 'Suspendido'
    }
    return labels[estado] || estado
}

const getEstadoBadgeClass = (estado) => {
    const classes = {
        activo: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        inactivo: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        suspendido: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    }
    return classes[estado] || 'bg-gray-100 text-gray-800'
}

// Validar campos numéricos
watch(() => form.precio_general, (newValue) => {
    if (newValue && newValue < 0) {
        form.precio_general = 0
    }
})

watch(() => form.valor_ips, (newValue) => {
    if (newValue && newValue < 0) {
        form.valor_ips = 0
    }
})

const submit = () => {
    console.log('Actualizando prestación...')

    form.put(route('prestaciones.update', props.prestacion.id), {
        onSuccess: () => {
            console.log('¡Prestación actualizada exitosamente!')
        },
        onError: (errors) => {
            console.log('Errores:', errors)
        }
    })
}
</script>
