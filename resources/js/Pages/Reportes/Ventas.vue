<template>
    <Head title="Reporte de Ventas" />
    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <div class="mb-3">
                <h1 class="text-base font-semibold text-gray-900">Reporte de Ventas</h1>
                <p class="text-gray-500 text-xs mt-0.5">Análisis detallado de ventas por sucursal</p>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 mb-3">
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input v-model="filtros.fecha_desde" type="date" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input v-model="filtros.fecha_hasta" type="date" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md text-sm" />
                    </div>
                    <div v-if="canFilterAll">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sucursal</label>
                        <select v-model="filtros.sucursal_id" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md text-sm">
                            <option :value="null">Todas las sucursales</option>
                            <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">{{ sucursal.nombre }}</option>
                        </select>
                    </div>
                    <div v-else>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sucursal</label>
                        <input type="text" :value="sucursalActual?.nombre || 'Mi Sucursal'" disabled class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md bg-gray-100 text-gray-600 text-sm" />
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-sm">Aplicar Filtros</button>
                    </div>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3">
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-md shadow-sm p-2 text-white">
                    <p class="text-xs opacity-90">Total Vendido</p>
                    <p class="text-lg font-bold mt-1">${{ formatNumber(stats.total_vendido) }}</p>
                    <p class="text-[10px] opacity-75 mt-0.5">{{ stats.ordenes_pagadas }} órdenes pagadas</p>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-md shadow-sm p-2 text-white">
                    <p class="text-xs opacity-90">Total Anulado</p>
                    <p class="text-lg font-bold mt-1">${{ formatNumber(stats.total_anulado) }}</p>
                    <p class="text-[10px] opacity-75 mt-0.5">{{ stats.ordenes_anuladas }} órdenes anuladas</p>
                </div>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-md shadow-sm p-2 text-white">
                    <p class="text-xs opacity-90">Total Pendiente</p>
                    <p class="text-lg font-bold mt-1">${{ formatNumber(stats.total_pendiente) }}</p>
                    <p class="text-[10px] opacity-75 mt-0.5">{{ stats.ordenes_pendientes }} órdenes pendientes</p>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-md shadow-sm p-2 text-white">
                    <p class="text-xs opacity-90">Total Órdenes</p>
                    <p class="text-lg font-bold mt-1">{{ stats.total_ordenes }}</p>
                    <p class="text-[10px] opacity-75 mt-0.5">Todas las órdenes</p>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-md shadow-sm border border-gray-100">
                <div class="p-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Listado de Órdenes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">N° Orden</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Fecha</th>
                                <th v-if="canFilterAll" class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Sucursal</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Beneficiario</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Prestador</th>
                                <th class="px-2 py-1.5 text-right text-[11px] font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-2 py-1.5 text-center text-[11px] font-semibold text-gray-600 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="orden in ordenes" :key="orden.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                    {{ orden.numero_orden_completo }}
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                    {{ formatDate(orden.fec_ord) }}
                                </td>
                                <td v-if="canFilterAll" class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                    {{ orden.sucursal?.nombre || '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-900">
                                    {{ orden.beneficiario?.apellido }}, {{ orden.beneficiario?.nombre }}
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500">
                                    {{ orden.prestador ? `${orden.prestador.apellido}, ${orden.prestador.nombre}` : '-' }}
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-bold text-right" :class="getEstadoColorTotal(orden.estado)">
                                    ${{ formatNumber(orden.total) }}
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                    <span :class="getEstadoClase(orden.estado)" class="px-1.5 py-0.5 text-[11px] font-semibold rounded-full">
                                        {{ getEstadoTexto(orden.estado) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { reactive } from 'vue';

const props = defineProps({
    ordenes: Array,
    stats: Object,
    sucursales: Array,
    sucursalActual: Object,
    canFilterAll: Boolean,
    userSucursalId: Number,
    filtros: Object,
});

const filtros = reactive({
    fecha_desde: props.filtros.fecha_desde,
    fecha_hasta: props.filtros.fecha_hasta,
    sucursal_id: props.filtros.sucursal_id,
});

const aplicarFiltros = () => {
    router.get(route('reportes.ventas'), filtros, { preserveState: true });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number || 0);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-AR');
};

const getEstadoTexto = (estado) => {
    const estados = {
        1: 'Pendiente',
        2: 'Pagada',
        3: 'Anulada',
    };
    return estados[estado] || 'Desconocido';
};

const getEstadoClase = (estado) => {
    const clases = {
        1: 'bg-orange-100 text-orange-800',
        2: 'bg-green-100 text-green-800',
        3: 'bg-red-100 text-red-800',
    };
    return clases[estado] || 'bg-gray-100 text-gray-800';
};

const getEstadoColorTotal = (estado) => {
    const colores = {
        1: 'text-orange-600',
        2: 'text-green-600',
        3: 'text-red-600',
    };
    return colores[estado] || 'text-gray-900';
};
</script>
