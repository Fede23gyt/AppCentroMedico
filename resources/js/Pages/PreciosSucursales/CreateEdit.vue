<template>
    <Head :title="excepcion ? 'Editar Excepción de Precio' : 'Nueva Excepción de Precio'" />

    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="excepcion ? mdiPencil : mdiPlus"
                :title="excepcion ? 'Editar Excepción de Precio' : 'Nueva Excepción de Precio'"
                main
            >
                <BaseButton
                    :route-name="route('precios-sucursales.index')"
                    :icon="mdiArrowLeft"
                    label="Volver"
                    color="contrast"
                    rounded-full
                    small
                />
            </SectionTitleLineWithButton>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- FORMULARIO -->
                <div class="lg:col-span-2 space-y-6">
                    <CardBox is-form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Sucursal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Sucursal <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.sucursal_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md"
                                    :disabled="excepcion"
                                    required
                                >
                                    <option value="" disabled>Seleccionar sucursal</option>
                                    <option v-for="suc in sucursales" :key="suc.id" :value="suc.id">
                                        {{ suc.nombre }}
                                    </option>
                                </select>
                                <div v-if="form.errors.sucursal_id" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.sucursal_id }}
                                </div>
                            </div>

                            <!-- Prestación -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Prestación <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.prestacion_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md"
                                    :disabled="excepcion"
                                    @change="onPrestacionElegida"
                                    required
                                >
                                    <option value="" disabled>Seleccionar prestación</option>
                                    <option v-for="pre in prestaciones" :key="pre.id" :value="pre.id">
                                        {{ pre.codigo }} - {{ pre.nombre }}
                                    </option>
                                </select>
                                <div v-if="form.errors.prestacion_id" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.prestacion_id }}
                                </div>
                            </div>
                        </div>

                        <!-- Precios Referencia de la Prestación General -->
                        <div v-if="prestacionSeleccionada" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <h4 class="text-sm font-bold text-blue-800 dark:text-blue-200 mb-2">Valores Generales de Nivel (Referencia)</h4>
                            <div class="grid grid-cols-4 gap-4 text-sm">
                                <div><span class="text-gray-500">P1:</span> ${{ prestacionSeleccionada.precio_1 }}</div>
                                <div><span class="text-gray-500">P2:</span> ${{ prestacionSeleccionada.precio_2 }}</div>
                                <div><span class="text-gray-500">P3:</span> ${{ prestacionSeleccionada.precio_3 }}</div>
                                <div><span class="text-gray-500">P4:</span> ${{ prestacionSeleccionada.precio_4 }}</div>
                            </div>
                        </div>

                        <BaseDivider />

                        <h3 class="text-lg font-bold mb-4">Precios de Excepción (Sucursal)</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Precio 1</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">$</span>
                                    <input v-model="form.precio_1" type="number" step="0.01" class="w-full pl-6 pr-2 py-2 text-sm border-gray-300 rounded-md" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Precio 2</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">$</span>
                                    <input v-model="form.precio_2" type="number" step="0.01" class="w-full pl-6 pr-2 py-2 text-sm border-gray-300 rounded-md" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Precio 3</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">$</span>
                                    <input v-model="form.precio_3" type="number" step="0.01" class="w-full pl-6 pr-2 py-2 text-sm border-gray-300 rounded-md" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Precio 4</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">$</span>
                                    <input v-model="form.precio_4" type="number" step="0.01" class="w-full pl-6 pr-2 py-2 text-sm border-gray-300 rounded-md" />
                                </div>
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Motivo del Cambio <span class="text-xs text-gray-500">(Opcional, se guardará en historial)</span>
                            </label>
                            <textarea
                                v-model="form.motivo"
                                rows="2"
                                placeholder="Ej: Ajuste por costos de alquiler local..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md"
                            ></textarea>
                        </div>

                        <div class="flex justify-end pt-4">
                            <BaseButton
                                type="submit"
                                color="success"
                                :icon="mdiContentSave"
                                :label="excepcion ? 'Actualizar Excepción' : 'Guardar Excepción'"
                                :disabled="form.processing"
                            />
                        </div>
                    </CardBox>
                </div>

                <!-- HISTORIAL (MODAL O PANEL DIRECHO) -->
                <div class="lg:col-span-1" v-if="excepcion">
                    <CardBox class="h-full">
                        <h3 class="font-bold text-lg mb-4 flex items-center">
                            <Icon :path="mdiHistory" size="20" class="mr-2 text-gray-500" />
                            Historial de Cambios
                        </h3>

                        <div v-if="historial.length === 0" class="text-sm text-gray-500 italic p-4 text-center">
                            No hay historial registrado.
                        </div>

                        <div class="space-y-4">
                            <div v-for="item in historial" :key="item.id" class="border-l-4 border-blue-500 pl-3 py-1 text-sm bg-gray-50 dark:bg-gray-800 rounded-r-md">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">
                                        Nivel P{{ item.nivel_precio }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ formatShortDateTime(item.created_at) }}</span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                                    <span class="line-through mr-1 text-red-400">${{ item.valor_anterior }}</span> 
                                    <span class="font-bold text-green-600">→ ${{ item.valor_nuevo }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Usuario: {{ item.user?.name || 'Sistema' }}
                                </div>
                                <div class="text-xs text-gray-500 italic mt-1" v-if="item.motivo">
                                    "{{ item.motivo }}"
                                </div>
                            </div>
                        </div>
                    </CardBox>
                </div>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { mdiPencil, mdiPlus, mdiArrowLeft, mdiContentSave, mdiHistory } from '@mdi/js'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseDivider from '@/components/BaseDivider.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
    excepcion: Object,
    sucursales: Array,
    prestaciones: Array,
    historial: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    sucursal_id: props.excepcion?.sucursal_id || '',
    prestacion_id: props.excepcion?.prestacion_id || '',
    precio_1: props.excepcion?.precio_1 || 0,
    precio_2: props.excepcion?.precio_2 || 0,
    precio_3: props.excepcion?.precio_3 || 0,
    precio_4: props.excepcion?.precio_4 || 0,
    motivo: ''
})

const prestacionSeleccionada = computed(() => {
    if (!form.prestacion_id) return null
    return props.prestaciones.find(p => p.id === form.prestacion_id)
})

const onPrestacionElegida = () => {
    if (!props.excepcion && prestacionSeleccionada.value) {
        // Autocompletar con los valores generales si es nueva
        form.precio_1 = prestacionSeleccionada.value.precio_1
        form.precio_2 = prestacionSeleccionada.value.precio_2
        form.precio_3 = prestacionSeleccionada.value.precio_3
        form.precio_4 = prestacionSeleccionada.value.precio_4
    }
}

const submit = () => {
    if (props.excepcion) {
        form.put(route('precios-sucursales.update', props.excepcion.id))
    } else {
        form.post(route('precios-sucursales.store'))
    }
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
