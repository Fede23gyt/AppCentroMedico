<template>
    <Head title="Reporte de Órdenes" />
    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="mb-3">
                <h1 class="text-base font-semibold text-gray-900">Reporte de Órdenes</h1>
                <p class="text-gray-500 text-xs mt-0.5">Análisis detallado de órdenes por período</p>
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
                    <div v-if="sucursales.length > 0">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sucursal</label>
                        <select v-model="filtros.sucursal_id" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md text-sm">
                            <option value="">Todas</option>
                            <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">{{ sucursal.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                        <select v-model="filtros.estado" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md text-sm">
                            <option value="">Todos</option>
                            <option value="1">Pendiente</option>
                            <option value="2">Pagada</option>
                            <option value="3">Anulada</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-sm">Aplicar Filtros</button>
                    </div>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-2 mb-3">
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <p class="text-xs text-gray-600">Total Órdenes</p>
                    <p class="text-base font-bold text-blue-600">{{ stats.total_ordenes }}</p>
                </div>
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <p class="text-xs text-gray-600">Total Ventas</p>
                    <p class="text-base font-bold text-green-600">${{ formatNumber(stats.total_ventas) }}</p>
                </div>
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <p class="text-xs text-gray-600">Pendientes</p>
                    <p class="text-base font-bold text-orange-600">{{ stats.ordenes_pendientes }}</p>
                </div>
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <p class="text-xs text-gray-600">Pagadas</p>
                    <p class="text-base font-bold text-green-600">{{ stats.ordenes_pagadas }}</p>
                </div>
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <p class="text-xs text-gray-600">Anuladas</p>
                    <p class="text-base font-bold text-red-600">{{ stats.ordenes_anuladas }}</p>
                </div>
            </div>

            <!-- Tabla de Órdenes -->
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
                                <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Beneficiario</th>
                                <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Sucursal</th>
                                <th class="px-2 py-1.5 text-right text-[11px] font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-2 py-1.5 text-center text-[11px] font-semibold text-gray-600 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="orden in ordenes.data" :key="orden.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-blue-600">
                                    <Link :href="route('ordenes.show', orden.id)">{{ orden.numero_orden_completo }}</Link>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">{{ orden.fecha }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">{{ orden.beneficiario }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-600">{{ orden.sucursal }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-bold text-gray-900 text-right">${{ formatNumber(orden.total) }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-800': orden.estado === 1,
                                        'bg-green-100 text-green-800': orden.estado === 2,
                                        'bg-red-100 text-red-800': orden.estado === 3
                                    }" class="px-1.5 py-0.5 inline-flex text-[11px] font-semibold rounded-full">
                                        {{ orden.estado_texto }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="ordenes.links.length > 3" class="px-3 py-2 border-t border-gray-100">
                    <div class="flex justify-between items-center">
                        <p class="text-xs text-gray-700">
                            Mostrando <span class="font-medium">{{ ordenes.from }}</span> a <span class="font-medium">{{ ordenes.to }}</span> de <span class="font-medium">{{ ordenes.total }}</span> resultados
                        </p>
                        <div class="flex gap-1">
                            <Link v-for="link in ordenes.links" :key="link.label" :href="link.url"
                                  :class="{'bg-blue-600 text-white': link.active, 'bg-white text-gray-700 hover:bg-gray-50': !link.active}"
                                  class="px-2 py-1 border border-gray-300 rounded text-xs" v-html="link.label">
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { reactive } from 'vue';

const props = defineProps({
    ordenes: Object,
    stats: Object,
    sucursales: Array,
    filtros: Object,
});

const filtros = reactive({
    fecha_desde: props.filtros.fecha_desde,
    fecha_hasta: props.filtros.fecha_hasta,
    sucursal_id: props.filtros.sucursal_id || '',
    estado: props.filtros.estado || '',
});

const aplicarFiltros = () => {
    router.get(route('reportes.ordenes'), filtros, { preserveState: true });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number || 0);
};
</script>
