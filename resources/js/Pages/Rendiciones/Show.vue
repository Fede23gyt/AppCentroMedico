<template>
    <Head title="Detalle de Rendición" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="flex justify-end items-center mb-3">
                <Link
                    :href="route('rendiciones.index')"
                    class="text-gray-600 hover:text-gray-900 font-medium text-sm"
                >
                    ← Volver
                </Link>
            </div>

            <!-- Información de la Rendición -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <!-- Card 1: Información Principal -->
                <div class="rounded-md shadow-sm border border-gray-600 p-3" style="background-color: #2D6660;">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <h2 class="text-sm font-semibold text-white">
                                Rendición: {{ rendicion.numero_rendicion_completo }}
                            </h2>
                            <h2 class="text-sm font-semibold text-white">
                                Fecha: {{ formatDateWithSlashes(rendicion.fecha_inicio) }}
                            </h2>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">
                                Sucursal: {{ rendicion.sucursal?.nombre }}
                            </p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-gray-400">
                            <div class="bg-white rounded-md p-2 border-2 border-gray-300">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center">
                                        <span class="text-sm font-bold text-gray-900">TOTAL:</span>
                                        <span class="text-base font-bold text-green-700 ml-2">${{ parseFloat(rendicion.total).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <span :class="getEstadoBadgeClass(rendicion.estado)" class="px-2 py-0.5 text-[11px] font-semibold rounded-full">
                                            {{ getEstadoTexto(rendicion.estado) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Prestador -->
                <div class="rounded-md shadow-sm border border-gray-600 p-3" style="background-color: #2D6660;">
                    <div class="space-y-2">
                        <div class="border-t border-gray-400 pt-2">
                            <p class="text-sm font-semibold text-white">
                                Prestador: {{ rendicion.prestador?.apellido }}, {{ rendicion.prestador?.nombre }}
                            </p>
                        </div>
                        <div v-if="rendicion.observacion" class="border-t border-gray-400 pt-2">
                            <p class="text-xs text-white">
                                <span class="font-semibold">Observación:</span> {{ rendicion.observacion }}
                            </p>
                        </div>
                        <div v-if="rendicion.estado === 2 && rendicion.fecha_cierre" class="border-t border-gray-400 pt-2">
                            <p class="text-xs text-white">
                                <span class="font-semibold">Fecha Cierre:</span> {{ formatDateWithSlashes(rendicion.fecha_cierre) }}
                            </p>
                            <p class="text-xs text-white">
                                <span class="font-semibold">Usuario:</span> {{ rendicion.usu_cierre }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agregar Orden (solo si está abierta) -->
            <div v-if="rendicion.estado === 1" class="rounded-md shadow-sm border border-gray-600 p-3 mb-3" style="background-color: #2D6660;">
                <h2 class="text-sm font-semibold text-white mb-2">Agregar Orden</h2>

                <div class="flex gap-2">
                    <div class="flex-1">
                        <input
                            v-model="numeroOrden"
                            type="text"
                            placeholder="Ingrese el número de orden o escanee código de barras"
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            @keypress.enter="buscarOrden"
                            ref="inputOrden"
                        />
                    </div>
                    <button
                        @click="buscarOrden"
                        :disabled="!numeroOrden.trim() || buscandoOrden"
                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm"
                    >
                        <span v-if="buscandoOrden">Buscando...</span>
                        <span v-else>Buscar</span>
                    </button>
                </div>

                <!-- Mensaje de error -->
                <div v-if="errorBusqueda" class="mt-2 p-2 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-xs text-red-800">{{ errorBusqueda }}</p>
                </div>

                <!-- Vista previa de orden encontrada -->
                <div v-if="ordenEncontrada" class="mt-2 p-2 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-green-900">Orden encontrada:</p>
                            <p class="text-xs text-green-800">
                                <strong>N°:</strong> {{ ordenEncontrada.numero_orden_completo }} |
                                <strong>Beneficiario:</strong> {{ ordenEncontrada.beneficiario?.apellido }}, {{ ordenEncontrada.beneficiario?.nombre }} |
                                <strong>Total Prestador:</strong> ${{ parseFloat(ordenEncontrada.total_prestador || 0).toFixed(2) }}
                            </p>
                        </div>
                        <div class="flex gap-1.5 ml-2">
                            <button
                                @click="agregarOrden"
                                :disabled="agregandoOrden"
                                class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-md transition-colors disabled:bg-gray-300"
                            >
                                Agregar
                            </button>
                            <button
                                @click="cancelarOrden"
                                class="px-2 py-1 bg-gray-300 hover:bg-gray-400 text-gray-800 text-xs rounded-md transition-colors"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Órdenes -->
            <div class="rounded-md shadow-sm border border-gray-600 p-3 mb-3" style="background-color: #2D6660;">
                <h2 class="text-sm font-semibold text-white mb-2">
                    Órdenes Rendidas ({{ rendicion.detalles?.length || 0 }})
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-gray-200 uppercase">N° Orden</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-gray-200 uppercase">Fecha</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-gray-200 uppercase">Beneficiario</th>
                                <th class="px-2 py-1.5 text-right text-[11px] font-medium text-gray-200 uppercase">Total Prestador</th>
                                <th v-if="rendicion.estado === 1" class="px-2 py-1.5 text-center text-[11px] font-medium text-gray-200 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="detalle in rendicion.detalles" :key="detalle.id">
                                <td class="px-2 py-1.5 text-xs text-gray-900">{{ detalle.orden?.numero_orden_completo }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-500">{{ formatDate(detalle.orden?.fec_ord) }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-900">
                                    {{ detalle.orden?.beneficiario?.apellido }}, {{ detalle.orden?.beneficiario?.nombre }}
                                </td>
                                <td class="px-2 py-1.5 text-xs font-medium text-gray-900 text-right">
                                    ${{ parseFloat(detalle.total_prestador).toFixed(2) }}
                                </td>
                                <td v-if="rendicion.estado === 1" class="px-2 py-1.5 text-center">
                                    <button
                                        @click="quitarOrden(detalle.id)"
                                        class="text-red-600 hover:text-red-900 text-xs"
                                    >
                                        Quitar
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!rendicion.detalles || rendicion.detalles.length === 0">
                                <td :colspan="rendicion.estado === 1 ? 5 : 4" class="px-2 py-2 text-center text-gray-500 text-xs">
                                    No hay órdenes en esta rendición
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Acciones -->
            <div class="rounded-md shadow-sm border border-gray-600 p-3" style="background-color: #2D6660;">
                <h2 class="text-sm font-semibold text-white mb-2">Acciones</h2>

                <div class="flex flex-wrap gap-2">
                    <!-- Ver PDF (solo para rendiciones cerradas) -->
                    <a
                        v-if="rendicion.estado === 2"
                        :href="route('rendiciones.pdf', { rendicion: rendicion.id })"
                        target="_blank"
                        class="flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md transition-colors text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver PDF
                    </a>

                    <!-- Cerrar Rendición -->
                    <button
                        v-if="rendicion.estado === 1"
                        @click="cerrarRendicion"
                        :disabled="!rendicion.detalles || rendicion.detalles.length === 0"
                        class="flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Cerrar Rendición
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    rendicion: Object,
});

const numeroOrden = ref('');
const ordenEncontrada = ref(null);
const errorBusqueda = ref('');
const buscandoOrden = ref(false);
const agregandoOrden = ref(false);
const inputOrden = ref(null);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-AR');
};

const formatDateWithSlashes = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
};

const getEstadoTexto = (estado) => {
    const estados = {
        1: 'Abierta',
        2: 'Cerrada',
    };
    return estados[estado] || 'Desconocido';
};

const getEstadoBadgeClass = (estado) => {
    const classes = {
        1: 'bg-yellow-100 text-yellow-800',
        2: 'bg-green-100 text-green-800',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};

const buscarOrden = async () => {
    if (!numeroOrden.value.trim()) return;

    errorBusqueda.value = '';
    ordenEncontrada.value = null;
    buscandoOrden.value = true;

    try {
        const response = await axios.post(route('api.rendiciones.buscar-orden'), {
            numero_orden: numeroOrden.value.trim(),
            rendicion_id: props.rendicion.id,
        });

        ordenEncontrada.value = response.data;
    } catch (error) {
        if (error.response && error.response.data && error.response.data.error) {
            errorBusqueda.value = error.response.data.error;
        } else {
            errorBusqueda.value = 'Error al buscar la orden';
        }
    } finally {
        buscandoOrden.value = false;
    }
};

const agregarOrden = async () => {
    if (!ordenEncontrada.value) return;

    agregandoOrden.value = true;

    try {
        await axios.post(route('rendiciones.agregar-orden', props.rendicion.id), {
            orden_id: ordenEncontrada.value.id,
        });

        // Recargar la página para actualizar los datos
        router.reload({ preserveScroll: true });

        // Limpiar
        numeroOrden.value = '';
        ordenEncontrada.value = null;
        errorBusqueda.value = '';

        // Enfocar input
        nextTick(() => {
            inputOrden.value?.focus();
        });
    } catch (error) {
        if (error.response && error.response.data && error.response.data.error) {
            errorBusqueda.value = error.response.data.error;
        } else {
            errorBusqueda.value = 'Error al agregar la orden';
        }
    } finally {
        agregandoOrden.value = false;
    }
};

const cancelarOrden = () => {
    numeroOrden.value = '';
    ordenEncontrada.value = null;
    errorBusqueda.value = '';
    inputOrden.value?.focus();
};

const quitarOrden = async (detalleId) => {
    if (!confirm('¿Está seguro de quitar esta orden de la rendición?')) return;

    try {
        await axios.post(route('rendiciones.quitar-orden', props.rendicion.id), {
            detalle_id: detalleId,
        });

        // Recargar la página para actualizar los datos
        router.reload({ preserveScroll: true });
    } catch (error) {
        alert('Error al quitar la orden');
    }
};

const cerrarRendicion = () => {
    if (!confirm('¿Está seguro de cerrar esta rendición? Una vez cerrada no se podrán agregar más órdenes.')) return;

    router.post(route('rendiciones.cerrar', props.rendicion.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Abrir PDF automáticamente
            window.open(route('rendiciones.pdf', { rendicion: props.rendicion.id }), '_blank');
        },
    });
};
</script>
