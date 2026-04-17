<template>
    <Head title="Excepciones de Precios por Sucursal" />

    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiStore"
                title="Excepciones de Precios por Sucursal"
                main
            >
                <BaseButton
                    :route-name="route('precios-sucursales.create')"
                    :icon="mdiPlus"
                    label="Nueva Excepción"
                    color="success"
                    rounded-full
                    small
                />
            </SectionTitleLineWithButton>

            <!-- Filtros -->
            <CardBox class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sucursal
                        </label>
                        <select
                            v-model="form.sucursal_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @change="filtrar"
                        >
                            <option value="">Todas las sucursales</option>
                            <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                                {{ sucursal.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Buscar Prestación
                        </label>
                        <input
                            v-model="form.buscar"
                            type="text"
                            placeholder="Código o nombre..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @keyup.enter="filtrar"
                        />
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <BaseButton
                        label="Filtrar"
                        color="info"
                        :icon="mdiMagnify"
                        @click="filtrar"
                    />
                    <BaseButton
                        label="Limpiar"
                        color="whiteDark"
                        class="ml-2"
                        @click="limpiarFiltros"
                    />
                </div>
            </CardBox>

            <!-- Tabla -->
            <CardBox class="mb-6" has-table>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-800">
                                <th class="p-3 font-semibold text-gray-700 dark:text-gray-200 border-b">Sucursal</th>
                                <th class="p-3 font-semibold text-gray-700 dark:text-gray-200 border-b">Prestación</th>
                                <th class="p-3 font-semibold text-center text-gray-700 dark:text-gray-200 border-b">Precio 1</th>
                                <th class="p-3 font-semibold text-center text-gray-700 dark:text-gray-200 border-b">Precio 2</th>
                                <th class="p-3 font-semibold text-center text-gray-700 dark:text-gray-200 border-b">Precio 3</th>
                                <th class="p-3 font-semibold text-center text-gray-700 dark:text-gray-200 border-b">Precio 4</th>
                                <th class="p-3 font-semibold text-center text-gray-700 dark:text-gray-200 border-b">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="excepcion in excepciones.data" :key="excepcion.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="p-3">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ excepcion.sucursal.nombre }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-sm">{{ excepcion.prestacion.codigo }}</div>
                                    <div class="text-xs text-gray-500">{{ excepcion.prestacion.nombre }}</div>
                                </td>
                                <td class="p-3 text-center">${{ excepcion.precio_1 }}</td>
                                <td class="p-3 text-center">${{ excepcion.precio_2 }}</td>
                                <td class="p-3 text-center">${{ excepcion.precio_3 }}</td>
                                <td class="p-3 text-center">${{ excepcion.precio_4 }}</td>
                                <td class="p-3 text-center">
                                    <BaseButtons type="justify-center" no-wrap>
                                        <BaseButton
                                            color="info"
                                            :icon="mdiPencil"
                                            small
                                            :route-name="route('precios-sucursales.edit', excepcion.id)"
                                        />
                                        <BaseButton
                                            color="danger"
                                            :icon="mdiTrashCan"
                                            small
                                            @click="confirmarEliminacion(excepcion.id)"
                                        />
                                    </BaseButtons>
                                </td>
                            </tr>
                            <tr v-if="excepciones.data.length === 0">
                                <td colspan="7" class="p-6 text-center text-gray-500">
                                    No se encontraron excepciones de precios.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 flex justify-between items-center border-t dark:border-gray-700">
                    <div>
                        <span class="text-sm text-gray-500">
                            Mostrando {{ excepciones.from || 0 }} - {{ excepciones.to || 0 }} de {{ excepciones.total }}
                        </span>
                    </div>
                </div>
            </CardBox>
        </SectionMain>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import {
    mdiStore,
    mdiPlus,
    mdiPencil,
    mdiTrashCan,
    mdiMagnify
} from '@mdi/js'
import Swal from 'sweetalert2'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseButtons from '@/components/BaseButtons.vue'

const props = defineProps({
    excepciones: Object,
    sucursales: Array,
    filters: Object,
})

const form = reactive({
    sucursal_id: props.filters.sucursal_id || '',
    buscar: props.filters.buscar || '',
})

const filtrar = () => {
    router.get(route('precios-sucursales.index'), form, {
        preserveState: true,
        replace: true
    })
}

const limpiarFiltros = () => {
    form.sucursal_id = ''
    form.buscar = ''
    filtrar()
}

const confirmarEliminacion = (id) => {
    Swal.fire({
        title: '¿Eliminar excepción?',
        text: 'Se usarán los precios generales de la prestación para esta sucursal.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('precios-sucursales.destroy', id))
        }
    })
}
</script>
