<!-- resources/js/Pages/Prestaciones/Planes.vue -->
<template>
    <Head :title="`Planes - ${prestacion.nombre}`" />

    <SectionMain>
        <SectionTitleLineWithButton
            :icon="mdiCreditCard"
            :title="`Gestión de Planes - ${prestacion.codigo}`"
            main
        >
            <BaseButton
                :route-name="route('prestaciones.index')"
                :icon="mdiArrowLeft"
                label="Volver"
                color="contrast"
                rounded-full
                small
            />
        </SectionTitleLineWithButton>

        <!-- Información de la Prestación -->
        <CardBox class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                    <span class="font-mono font-semibold text-blue-600 text-lg">{{ prestacion.codigo }}</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <span class="font-semibold">{{ prestacion.nombre }}</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rubro</label>
                    <BaseTag :label="prestacion.rubro.nombre" color="info" small />
                </div>
            </div>
        </CardBox>

        <!-- Asociar Nuevo Plan -->
        <CardBox v-if="planesDisponibles.length > 0" class="mb-6">
            <div class="flex items-center mb-4">
                <h3 class="text-lg font-semibold flex items-center">
                    <BaseIcon :path="mdiPlus" class="mr-2" />
                    Asociar Nuevo Plan
                </h3>
            </div>

            <form @submit.prevent="asociarPlan" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <FormField label="Plan">
                    <FormControl
                        v-model="formAsociar.plan_id"
                        :options="planOptions"
                        :error="formAsociar.errors.plan_id"
                        required
                    />
                </FormField>

                <FormField label="Estado">
                    <FormControl
                        v-model="formAsociar.estado"
                        :options="estadoOptions"
                        :error="formAsociar.errors.estado"
                        required
                    />
                </FormField>

                <FormField label="Valor Afiliado">
                    <FormControl
                        v-model="formAsociar.valor_afiliado"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        :error="formAsociar.errors.valor_afiliado"
                        required
                    />
                </FormField>

                <FormField label="Valor Particular">
                    <FormControl
                        v-model="formAsociar.valor_particular"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        :error="formAsociar.errors.valor_particular"
                        required
                    />
                </FormField>

                <FormField label="Máx. Individual">
                    <FormControl
                        v-model="formAsociar.cant_max_individual"
                        type="number"
                        min="1"
                        placeholder="Sin límite"
                        :error="formAsociar.errors.cant_max_individual"
                    />
                </FormField>

                <FormField label="Máx. Grupo">
                    <FormControl
                        v-model="formAsociar.cant_max_grupo"
                        type="number"
                        min="1"
                        placeholder="Sin límite"
                        :error="formAsociar.errors.cant_max_grupo"
                    />
                </FormField>

                <FormField label="Vigencia Desde">
                    <FormControl
                        v-model="formAsociar.fecha_vigencia_desde"
                        type="date"
                        :error="formAsociar.errors.fecha_vigencia_desde"
                        required
                    />
                </FormField>

                <FormField label="Vigencia Hasta">
                    <FormControl
                        v-model="formAsociar.fecha_vigencia_hasta"
                        type="date"
                        :error="formAsociar.errors.fecha_vigencia_hasta"
                    />
                </FormField>

                <div class="lg:col-span-2">
                    <FormField label="Observaciones">
                        <FormControl
                            v-model="formAsociar.observaciones"
                            type="textarea"
                            placeholder="Observaciones adicionales..."
                            :error="formAsociar.errors.observaciones"
                        />
                    </FormField>
                </div>

                <div class="lg:col-span-2">
                    <BaseButton
                        type="submit"
                        color="success"
                        label="Asociar Plan"
                        :class="{ 'opacity-25': formAsociar.processing }"
                        :disabled="formAsociar.processing"
                    />
                </div>
            </form>
        </CardBox>

        <!-- Planes Asociados -->
        <CardBox>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Planes Asociados</h3>
                <BaseTag
                    :label="`${prestacion.planes.length} plan${prestacion.planes.length !== 1 ? 'es' : ''}`"
                    color="info"
                    small
                />
            </div>

            <div v-if="prestacion.planes.length === 0" class="text-center py-8 text-gray-500">
                <BaseIcon :path="mdiInformationOutline" size="48" class="mx-auto mb-2" />
                <p>No hay planes asociados a esta prestación</p>
            </div>

            <div v-else class="space-y-4">
                <CardBox
                    v-for="plan in prestacion.planes"
                    :key="plan.id"
                    class="border-l-4"
                    :class="`border-l-${getEstadoColor(plan.pivot.estado)}-500`"
                >
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        <!-- Información del Plan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                            <div class="font-semibold">{{ plan.nombre }}</div>
                            <div class="text-sm text-gray-500 font-mono">{{ plan.nombre_corto }}</div>
                            <BaseTag
                                :label="plan.pivot.estado"
                                :color="getEstadoColor(plan.pivot.estado)"
                                small
                                class="mt-1"
                            />
                        </div>

                        <!-- Valores -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valores</label>
                            <div class="text-sm">
                                <div><span class="text-gray-600">Afiliado:</span> {{ formatCurrency(plan.pivot.valor_afiliado) }}</div>
                                <div><span class="text-gray-600">Particular:</span> {{ formatCurrency(plan.pivot.valor_particular) }}</div>
                            </div>
                        </div>

                        <!-- Límites -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Límites</label>
                            <div class="text-sm">
                                <div>
                                    <span class="text-gray-600">Individual:</span>
                                    {{ plan.pivot.cant_max_individual || 'Sin límite' }}
                                </div>
                                <div>
                                    <span class="text-gray-600">Grupo:</span>
                                    {{ plan.pivot.cant_max_grupo || 'Sin límite' }}
                                </div>
                            </div>
                        </div>

                        <!-- Vigencia y Acciones -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vigencia</label>
                            <div class="text-sm mb-2">
                                <div>{{ formatDate(plan.pivot.fecha_vigencia_desde) }}</div>
                                <div v-if="plan.pivot.fecha_vigencia_hasta">
                                    hasta {{ formatDate(plan.pivot.fecha_vigencia_hasta) }}
                                </div>
                            </div>

                            <BaseButtons no-wrap>
                                <BaseButton
                                    @click="editarAsociacion(plan)"
                                    :icon="mdiPencil"
                                    color="warning"
                                    small
                                    label="Editar"
                                />
                                <BaseButton
                                    @click="confirmDesasociar(plan)"
                                    :icon="mdiTrashCan"
                                    color="danger"
                                    small
                                    label="Quitar"
                                />
                            </BaseButtons>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div v-if="plan.pivot.observaciones" class="mt-3 pt-3 border-t border-gray-100">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                        <p class="text-sm text-gray-600">{{ plan.pivot.observaciones }}</p>
                    </div>
                </CardBox>
            </div>
        </CardBox>

        <!-- Modal de Edición -->
        <CardBoxModal
            v-model="modalEditar"
            title="Editar Asociación Plan"
            button="success"
            button-label="Actualizar"
            @confirm="actualizarAsociacion"
            :loading="formEditar.processing"
        >
            <FormField label="Valor Afiliado">
                <FormControl
                    v-model="formEditar.valor_afiliado"
                    type="number"
                    step="0.01"
                    min="0"
                    :error="formEditar.errors.valor_afiliado"
                    required
                />
            </FormField>

            <FormField label="Valor Particular">
                <FormControl
                    v-model="formEditar.valor_particular"
                    type="number"
                    step="0.01"
                    min="0"
                    :error="formEditar.errors.valor_particular"
                    required
                />
            </FormField>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <FormField label="Máx. Individual">
                    <FormControl
                        v-model="formEditar.cant_max_individual"
                        type="number"
                        min="1"
                        placeholder="Sin límite"
                        :error="formEditar.errors.cant_max_individual"
                    />
                </FormField>

                <FormField label="Máx. Grupo">
                    <FormControl
                        v-model="formEditar.cant_max_grupo"
                        type="number"
                        min="1"
                        placeholder="Sin límite"
                        :error="formEditar.errors.cant_max_grupo"
                    />
                </FormField>
            </div>

            <FormField label="Estado">
                <FormControl
                    v-model="formEditar.estado"
                    :options="estadoOptions.slice(1)"
                    :error="formEditar.errors.estado"
                    required
                />
            </FormField>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <FormField label="Vigencia Desde">
                    <FormControl
                        v-model="formEditar.fecha_vigencia_desde"
                        type="date"
                        :error="formEditar.errors.fecha_vigencia_desde"
                        required
                    />
                </FormField>

                <FormField label="Vigencia Hasta">
                    <FormControl
                        v-model="formEditar.fecha_vigencia_hasta"
                        type="date"
                        :error="formEditar.errors.fecha_vigencia_hasta"
                    />
                </FormField>
            </div>

            <FormField label="Observaciones">
                <FormControl
                    v-model="formEditar.observaciones"
                    type="textarea"
                    :error="formEditar.errors.observaciones"
                />
            </FormField>
        </CardBoxModal>
    </SectionMain>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import {
    mdiCreditCard,
    mdiArrowLeft,
    mdiPlus,
    mdiPencil,
    mdiTrashCan,
    mdiInformationOutline
} from '@mdi/js'

import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseTag from '@/Components/BaseTag.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'

const props = defineProps({
    prestacion: Object,
    planesDisponibles: Array
})

// Modales
const modalEditar = ref(false)
const planEditando = ref(null)

// Formulario para asociar plan
const formAsociar = useForm({
    plan_id: '',
    valor_afiliado: '',
    valor_particular: '',
    cant_max_individual: '',
    cant_max_grupo: '',
    estado: 'activo',
    fecha_vigencia_desde: new Date().toISOString().split('T')[0],
    fecha_vigencia_hasta: '',
    observaciones: ''
})

// Formulario para editar asociación
const formEditar = useForm({
    valor_afiliado: '',
    valor_particular: '',
    cant_max_individual: '',
    cant_max_grupo: '',
    estado: '',
    fecha_vigencia_desde: '',
    fecha_vigencia_hasta: '',
    observaciones: ''
})

const planOptions = computed(() => [
    { value: '', label: 'Seleccione un plan' },
    ...props.planesDisponibles.map(plan => ({
        value: plan.id,
        label: `${plan.nombre} (${plan.nombre_corto})`
    }))
])

const estadoOptions = [
    { value: '', label: 'Seleccione estado' },
    { value: 'activo', label: 'Activo' },
    { value: 'inactivo', label: 'Inactivo' },
    { value: 'suspendido', label: 'Suspendido' }
]

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(amount)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-AR')
}

const getEstadoColor = (estado) => {
    const colors = {
        activo: 'success',
        inactivo: 'danger',
        suspendido: 'warning'
    }
    return colors[estado] || 'contrast'
}

const asociarPlan = () => {
    formAsociar.post(route('prestaciones.asociar-plan', props.prestacion.id), {
        onSuccess: () => {
            formAsociar.reset()
        }
    })
}

const editarAsociacion = (plan) => {
    planEditando.value = plan
    formEditar.reset()

    // Llenar el formulario con los datos actuales
    Object.keys(formEditar.data()).forEach(key => {
        if (plan.pivot[key] !== undefined) {
            formEditar[key] = plan.pivot[key]
        }
    })

    modalEditar.value = true
}

const actualizarAsociacion = () => {
    formEditar.put(route('prestaciones.actualizar-plan', [props.prestacion.id, planEditando.value.id]), {
        onSuccess: () => {
            modalEditar.value = false
            planEditando.value = null
        }
    })
}

const confirmDesasociar = (plan) => {
    if (confirm(`¿Está seguro de quitar el plan "${plan.nombre}" de esta prestación?`)) {
        router.delete(route('prestaciones.desasociar-plan', [props.prestacion.id, plan.id]))
    }
}
</script>
