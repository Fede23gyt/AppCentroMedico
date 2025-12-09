<template>
    <Head title="Detalle de Orden" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="flex justify-end items-center mb-3">
                <Link
                    :href="route('ordenes.index')"
                    class="text-gray-600 hover:text-gray-900 font-medium text-sm"
                >
                    ← Volver
                </Link>
            </div>

            <!-- Primera Sección: 2 Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <!-- Card 1: Información de la Orden -->
                <div class="rounded-md shadow-sm border border-gray-600 p-3" style="background-color: #2D6660;">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <h2 class="text-sm font-semibold text-white">
                                Orden: {{ orden.numero_orden_completo }}
                            </h2>
                            <h2 class="text-sm font-semibold text-white">
                                Fecha: {{ formatDateWithSlashes(orden.fec_ord) }}
                            </h2>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">
                                Sucursal: {{ orden.sucursal?.nombre }}
                            </p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-gray-400">
                            <div class="bg-white rounded-md p-2 border-2 border-gray-300">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center">
                                        <span class="text-sm font-bold text-gray-900">TOTAL:</span>
                                        <span class="text-base font-bold text-green-700 ml-2">${{ parseFloat(orden.total).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <span :class="getEstadoBadgeClass(orden.estado)" class="px-2 py-0.5 text-[11px] font-semibold rounded-full">
                                            {{ getEstadoTexto(orden.estado) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Datos del Beneficiario -->
                <div class="rounded-md shadow-sm border border-gray-600 p-3" style="background-color: #2D6660;">
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <p class="text-xs text-white">
                                    <span class="font-semibold">Certificado:</span> {{ orden.beneficiario?.certificado }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-white">
                                    <span class="font-semibold">Plan:</span> {{ orden.beneficiario?.plan?.nombre || 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="border-t border-gray-400 pt-2">
                            <p class="text-sm font-semibold text-white">
                                Beneficiario: {{ orden.beneficiario?.apellido }}, {{ orden.beneficiario?.nombre }}
                            </p>
                        </div>
                        <div class="border-t border-gray-400 pt-2">
                            <p class="text-xs text-white">
                                <span class="font-semibold">DNI:</span> {{ orden.beneficiario?.dni }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Prestador -->
            <div class="rounded-md shadow-sm border border-gray-600 p-3 mb-3" style="background-color: #2D6660;">
                <p class="text-xs text-white">
                    <span class="font-semibold">Prestador:</span> {{ orden.prestador ? `${orden.prestador.apellido}, ${orden.prestador.nombre}` : 'Sin asignar' }}
                </p>
            </div>

            <!-- Sección Detalle de Prestaciones -->
            <div class="rounded-md shadow-sm border border-gray-600 p-3 mb-3" style="background-color: #2D6660;">
                <h2 class="text-sm font-semibold text-white mb-2">Detalle de Prestaciones</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-gray-200 uppercase">Código</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-gray-200 uppercase">Descripción</th>
                                <th class="px-2 py-1.5 text-right text-[11px] font-medium text-gray-200 uppercase">Cant.</th>
                                <th class="px-2 py-1.5 text-right text-[11px] font-medium text-gray-200 uppercase">P. Unit.</th>
                                <th class="px-2 py-1.5 text-right text-[11px] font-medium text-gray-200 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="detalle in orden.detalles" :key="detalle.id">
                                <td class="px-2 py-1.5 text-xs text-gray-900">{{ detalle.prestacion?.codigo }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-900">{{ detalle.prestacion?.descripcion }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-900 text-right">{{ detalle.cantidad }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-900 text-right">${{ parseFloat(detalle.importe_uni).toFixed(2) }}</td>
                                <td class="px-2 py-1.5 text-xs font-medium text-gray-900 text-right">${{ parseFloat(detalle.importe_total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sección Acciones -->
            <div class="rounded-md shadow-sm border border-gray-600 p-3" style="background-color: #2D6660;">
                <h2 class="text-sm font-semibold text-white mb-2">Acciones</h2>

                <div class="flex flex-wrap gap-2">
                    <!-- Nueva Orden -->
                    <Link
                        :href="route('ordenes.create')"
                        class="flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md transition-colors text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva Orden
                    </Link>

                    <!-- Ver Orden PDF (solo para órdenes pagadas) -->
                    <a
                        v-if="orden?.id && orden.estado === 2"
                        :href="route('ordenes.pdf', { orden: orden.id })"
                        target="_blank"
                        class="flex items-center justify-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-md transition-colors text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver Orden
                    </a>

                    <!-- Cambiar Estado -->
                    <button
                        v-if="orden.estado === 1"
                        @click="cambiarEstado(2)"
                        class="flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md transition-colors text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Marcar como Pagada
                    </button>

                    <button
                        v-if="orden.estado === 1"
                        @click="anularOrden"
                        class="flex items-center justify-center gap-1.5 bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md transition-colors text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Anular Orden
                    </button>

                    <!-- Botón para crear Nota de Crédito (órdenes pagadas) -->
                    <button
                        v-if="orden.estado === 2"
                        @click="anularOrden"
                        class="flex items-center justify-center gap-1.5 bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded-md transition-colors text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Generar Nota de Crédito
                    </button>
                </div>

                <!-- Información de anulación si está anulada -->
                <div v-if="orden.estado === 3" class="mt-2 bg-red-50 rounded-md p-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <p class="text-xs font-medium text-red-700">Orden Anulada</p>
                            <p class="text-xs text-red-900">
                                Fecha: {{ formatDate(orden.fec_anu) }} | Usuario: {{ orden.usu_anu }}
                            </p>
                        </div>
                        <div v-if="orden.motivo_anulacion">
                            <p class="text-xs font-medium text-red-700">Motivo de Anulación</p>
                            <p class="text-xs text-red-900">{{ orden.motivo_anulacion }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Motivo de Anulación -->
            <div v-if="mostrarModalAnulacion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-xl p-4 max-w-md w-full mx-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">
                        {{ orden.estado === 2 ? 'Generar Nota de Crédito' : 'Anular Orden' }}
                    </h3>
                    <p class="text-xs text-gray-600 mb-3">
                        {{ orden.estado === 2
                            ? 'Esta orden ya ha sido pagada. Se generará una Nota de Crédito. Por favor indique el motivo:'
                            : 'Por favor indique el motivo de la anulación:'
                        }}
                    </p>
                    <textarea
                        v-model="motivoAnulacion"
                        rows="4"
                        class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        placeholder="Escriba el motivo de la anulación..."
                    ></textarea>
                    <div class="flex justify-end gap-2 mt-3">
                        <button
                            @click="cerrarModalAnulacion"
                            class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors text-sm"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="confirmarAnulacion"
                            :disabled="!motivoAnulacion.trim()"
                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm"
                        >
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    orden: Object,
});

const page = usePage();

// Mostrar SweetAlert si viene del crear orden
onMounted(() => {
    const ordenNumero = page.props.flash?.orden_numero;
    if (ordenNumero) {
        Swal.fire({
            icon: 'success',
            title: '¡Orden Creada!',
            html: `Se ha generado la orden N° <strong>${ordenNumero}</strong>`,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#2563eb',
            timer: 4000,
            timerProgressBar: true,
        });
    }
});

const mostrarModalAnulacion = ref(false);
const motivoAnulacion = ref('');

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
        1: 'Pendiente',
        2: 'Pagada',
        3: 'Anulada',
    };
    return estados[estado] || 'Desconocido';
};

const getEstadoBadgeClass = (estado) => {
    const classes = {
        1: 'bg-yellow-100 text-yellow-800',
        2: 'bg-green-100 text-green-800',
        3: 'bg-red-100 text-red-800',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};

const cambiarEstado = (nuevoEstado) => {
    Swal.fire({
        title: '¿Confirma el pago del movimiento?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, confirmar pago',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('ordenes.cambiar-estado', props.orden.id), {
                estado: nuevoEstado,
            }, {
                preserveScroll: false,
                onSuccess: () => {
                    // Si se marcó como pagada (estado 2), abrir PDF y redirigir al index
                    if (nuevoEstado === 2) {
                        // Abrir PDF en nueva pestaña
                        window.open(route('ordenes.pdf', { orden: props.orden.id }), '_blank');

                        // Mostrar mensaje de éxito y redirigir
                        Swal.fire({
                            icon: 'success',
                            title: 'Pago Confirmado',
                            text: 'La orden ha sido marcada como pagada',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            router.visit(route('ordenes.index'));
                        });
                    }
                },
            });
        }
    });
};

const anularOrden = () => {
    mostrarModalAnulacion.value = true;
};

const cerrarModalAnulacion = () => {
    mostrarModalAnulacion.value = false;
    motivoAnulacion.value = '';
};

const confirmarAnulacion = () => {
    if (!motivoAnulacion.value.trim()) {
        return;
    }

    router.post(route('ordenes.destroy', props.orden.id), {
        _method: 'DELETE',
        motivo_anulacion: motivoAnulacion.value,
    }, {
        onSuccess: () => {
            cerrarModalAnulacion();
        },
    });
};
</script>
