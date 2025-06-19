<!-- resources/js/Pages/Prestaciones/Show.vue -->
<template>
    <AuthenticatedLayout>
        <Head :title="`Prestación #${prestacion.codigo} - ${prestacion.nombre}`" />

        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiMedicalBag"
                :title="`Prestación #${prestacion.codigo}`"
                main
            >
                <div class="flex gap-2">
                    <BaseButton
                        :route-name="route('prestaciones.edit', prestacion.id)"
                        :icon="mdiPencil"
                        label="Editar"
                        color="success"
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

            <!-- Estado y acciones rápidas -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span :class="getEstadoBadgeClass(prestacion.estado)"
                          class="px-3 py-1 rounded-full text-sm font-medium">
                        {{ getEstadoLabel(prestacion.estado) }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Rubro: {{ prestacion.rubro?.nombre || 'Sin rubro' }}
                    </span>
                </div>

                <!-- Acciones rápidas -->
                <div class="flex gap-2">
                    <BaseButton
                        @click="copyToClipboard"
                        :icon="mdiContentCopy"
                        color="info"
                        outline
                        small
                        label="Copiar"
                    />
                    <BaseButton
                        @click="showDeleteModal = true"
                        :icon="mdiDelete"
                        color="danger"
                        outline
                        small
                        label="Eliminar"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Información Principal -->
                <div class="xl:col-span-2 space-y-6">
                    <!-- Datos básicos -->
                    <CardBox>
                        <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                            <Icon :path="mdiInformation" class="w-6 h-6 text-blue-500" />
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
                                {{ prestacion.nombre }}
                            </h3>
                        </div>

                        <div class="p-6 space-y-4">
                            <div v-if="prestacion.descripcion" class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {{ prestacion.descripcion }}
                                </p>
                            </div>

                            <div v-else class="text-gray-500 dark:text-gray-500 italic">
                                Sin descripción disponible
                            </div>

                            <!-- Detalles técnicos -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-20">Código:</span>
                                    <span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                        {{ prestacion.codigo }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-20">Rubro:</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ prestacion.rubro?.nombre || 'Sin asignar' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardBox>

                    <!-- Observaciones -->
                    <CardBox v-if="prestacion.observaciones">
                        <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                            <Icon :path="mdiNoteText" class="w-6 h-6 text-orange-500" />
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                Observaciones
                            </h3>
                        </div>

                        <div class="p-6">
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ prestacion.observaciones }}
                            </p>
                        </div>
                    </CardBox>
                </div>

                <!-- Panel Lateral -->
                <div class="space-y-6">
                    <!-- Precios -->
                    <CardBox>
                        <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                            <Icon :path="mdiCurrencyUsd" class="w-6 h-6 text-green-500" />
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                Precios
                            </h3>
                        </div>

                        <div class="p-6 space-y-4">
                            <!-- Precio General -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Precio General
                                </span>
                                <span class="text-lg font-bold text-gray-900 dark:text-white">
                                    ${{ formatCurrency(prestacion.precio_general) }}
                                </span>
                            </div>

                            <!-- Valor IPS -->
                            <div v-if="prestacion.valor_ips" class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Valor IPS
                                </span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    ${{ formatCurrency(prestacion.valor_ips) }}
                                </span>
                            </div>

                            <!-- Diferencia de precios -->
                            <div v-if="prestacion.valor_ips && prestacion.precio_general !== prestacion.valor_ips"
                                 class="text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span v-if="prestacion.valor_ips < prestacion.precio_general">
                                    IPS: ${{ formatCurrency(prestacion.precio_general - prestacion.valor_ips) }} menos
                                </span>
                                <span v-else>
                                    IPS: ${{ formatCurrency(prestacion.valor_ips - prestacion.precio_general) }} más
                                </span>
                            </div>
                        </div>
                    </CardBox>

                    <!-- Información del Sistema -->
                    <CardBox>
                        <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                            <Icon :path="mdiClockOutline" class="w-6 h-6 text-gray-500" />
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                Información del Sistema
                            </h3>
                        </div>

                        <div class="p-6 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Creado:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ formatDate(prestacion.created_at) }}
                                </span>
                            </div>

                            <div v-if="prestacion.updated_at !== prestacion.created_at" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Modificado:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ formatDate(prestacion.updated_at) }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">ID:</span>
                                <span class="text-gray-900 dark:text-white font-mono text-xs">
                                    {{ prestacion.id }}
                                </span>
                            </div>
                        </div>
                    </CardBox>

                    <!-- Acciones -->
                    <CardBox>
                        <div class="p-6 space-y-3">
                            <BaseButton
                                :route-name="route('prestaciones.edit', prestacion.id)"
                                :icon="mdiPencil"
                                label="Editar Prestación"
                                color="success"
                                class="w-full justify-center"
                            />

                            <BaseButton
                                @click="showDeleteModal = true"
                                :icon="mdiDelete"
                                label="Eliminar Prestación"
                                color="danger"
                                outline
                                class="w-full justify-center"
                            />
                        </div>
                    </CardBox>
                </div>
            </div>

            <!-- Modal de confirmación para eliminar -->
            <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <div class="flex items-center gap-3 mb-4">
                        <Icon :path="mdiAlertCircle" class="w-6 h-6 text-red-500" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Confirmar Eliminación
                        </h3>
                    </div>

                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        ¿Estás seguro de que deseas eliminar la prestación
                        <strong>"{{ prestacion.nombre }}"</strong>?
                        Esta acción no se puede deshacer.
                    </p>

                    <div class="flex gap-3">
                        <BaseButton
                            @click="showDeleteModal = false"
                            label="Cancelar"
                            color="contrast"
                            outline
                            class="flex-1"
                        />
                        <BaseButton
                            @click="deletePrestacion"
                            :icon="mdiDelete"
                            label="Eliminar"
                            color="danger"
                            class="flex-1"
                            :disabled="deleteForm.processing"
                        />
                    </div>
                </div>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
    mdiMedicalBag,
    mdiPencil,
    mdiArrowLeft,
    mdiInformation,
    mdiCurrencyUsd,
    mdiNoteText,
    mdiClockOutline,
    mdiAlertCircle
} from '@mdi/js'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseButton from '@/components/BaseButton.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
    prestacion: Object
})

const showDeleteModal = ref(false)

const deleteForm = useForm({})

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

const copyToClipboard = async () => {
    const text = `Código: ${props.prestacion.codigo}\nNombre: ${props.prestacion.nombre}\nPrecio: ${formatCurrency(props.prestacion.precio_general)}`

    try {
        await navigator.clipboard.writeText(text)
        // Aquí podrías mostrar un toast de éxito
        alert('Información copiada al portapapeles')
    } catch (err) {
        console.error('Error al copiar al portapapeles:', err)
        alert('Error al copiar la información')
    }
}

const deletePrestacion = () => {
    deleteForm.delete(route('prestaciones.destroy', props.prestacion.id), {
        onSuccess: () => {
            router.visit(route('prestaciones.index'))
        },
        onError: () => {
            showDeleteModal.value = false
            alert('Error al eliminar la prestación')
        }
    })
}
</script>
