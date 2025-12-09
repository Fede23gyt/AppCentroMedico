<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="mb-3">
                <h1 class="text-base font-semibold text-gray-900">Dashboard</h1>
                <p class="text-gray-500 text-xs mt-0.5">{{ sucursal }}</p>
            </div>

            <!-- Filtros (Solo Admin/Supervisor) -->
            <div v-if="canFilter" class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 mb-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Filtros</h3>
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sucursal</label>
                        <select v-model="filtrosActivos.sucursal_id" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:ring-2 focus:ring-primary-300 focus:border-primary-400 text-sm">
                            <option value="">Todas las sucursales</option>
                            <option v-for="sucursal in sucursalesDisponibles" :key="sucursal.id" :value="sucursal.id">
                                {{ sucursal.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Plan</label>
                        <select v-model="filtrosActivos.plan_id" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:ring-2 focus:ring-primary-300 focus:border-primary-400 text-sm">
                            <option value="">Todos los planes</option>
                            <option v-for="plan in planesDisponibles" :key="plan.id" :value="plan.id">
                                {{ plan.nombre }} ({{ plan.nombre_corto }})
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-primary-400 hover:bg-primary-500 text-white font-medium px-3 py-1.5 rounded-md transition-colors text-sm">
                            Aplicar
                        </button>
                        <button type="button" @click="limpiarFiltros" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-1.5 rounded-md transition-colors text-sm">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Stats Cards Grid - Siempre 4 columnas en una línea -->
            <div class="grid grid-cols-4 gap-2 mb-3">
                <!-- Órdenes Hoy -->
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2 hover:shadow transition-shadow">
                    <div class="flex items-center justify-between mb-1">
                        <div class="w-6 h-6 bg-blue-50 rounded flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full">
                            +3.14%
                        </span>
                    </div>
                    <h3 class="text-xs font-medium text-gray-600 mb-0.5">Órdenes Hoy</h3>
                    <p class="text-base font-bold text-gray-900">{{ stats.ordenes_hoy }}</p>
                </div>

                <!-- Ventas Hoy -->
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2 hover:shadow transition-shadow">
                    <div class="flex items-center justify-between mb-1">
                        <div class="w-6 h-6 bg-green-50 rounded flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-green-600 bg-green-50 px-1.5 py-0.5 rounded-full">
                            +5.21%
                        </span>
                    </div>
                    <h3 class="text-xs font-medium text-gray-600 mb-0.5">Ventas Hoy</h3>
                    <p class="text-base font-bold text-gray-900">${{ formatNumber(stats.ventas_hoy) }}</p>
                </div>

                <!-- Órdenes Pendientes -->
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2 hover:shadow transition-shadow">
                    <div class="flex items-center justify-between mb-1">
                        <div class="w-6 h-6 bg-orange-50 rounded flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full">
                            -1.56%
                        </span>
                    </div>
                    <h3 class="text-xs font-medium text-gray-600 mb-0.5">Pendientes</h3>
                    <p class="text-base font-bold text-gray-900">{{ stats.ordenes_pendientes }}</p>
                </div>

                <!-- Rendiciones -->
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2 hover:shadow transition-shadow">
                    <div class="flex items-center justify-between mb-1">
                        <div class="w-6 h-6 bg-purple-50 rounded flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded-full">
                            +1.64%
                        </span>
                    </div>
                    <h3 class="text-xs font-medium text-gray-600 mb-0.5">Rendiciones</h3>
                    <p class="text-xl font-bold text-gray-900">{{ stats.rendiciones_abiertas }}</p>
                </div>
            </div>

            <!-- Gráficos y Tablas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 mb-3">
                <!-- Ventas por Sucursal -->
                <div v-if="ventasPorSucursal.length > 0" class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-900">Ventas por Sucursal</h3>
                        <span class="text-[11px] text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Mes Actual</span>
                    </div>
                    <div class="space-y-1.5">
                        <div v-for="(sucursal, index) in ventasPorSucursal" :key="index" class="flex items-center justify-between p-1.5 bg-gray-50 rounded hover:bg-gray-100 transition-colors">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-xs">{{ sucursal.nombre }}</p>
                                <p class="text-[11px] text-gray-500">{{ sucursal.total_ordenes }} órdenes</p>
                            </div>
                            <p class="text-xs font-bold text-primary-500">${{ formatNumber(sucursal.total_ventas) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Prestaciones Más Vendidas -->
                <div class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-900">Top Prestaciones</h3>
                        <span class="text-[11px] text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Este Mes</span>
                    </div>
                    <div v-if="prestacionesMasVendidas && prestacionesMasVendidas.length > 0" class="space-y-1">
                        <div v-for="(prest, index) in prestacionesMasVendidas.slice(0, 5)" :key="index" class="flex items-center justify-between py-1 border-b border-gray-100 last:border-b-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1 mb-0.5">
                                    <span class="text-[10px] font-semibold text-primary-600 bg-primary-50 px-1 py-0 rounded">{{ prest.codigo }}</span>
                                    <p class="text-[11px] font-medium text-gray-900 truncate">{{ (prest.descripcion || '').substring(0, 18) }}...</p>
                                </div>
                                <p class="text-[10px] text-gray-500">{{ prest.total_cantidad }} unid.</p>
                            </div>
                            <p class="text-xs font-bold text-green-600 ml-2">${{ formatNumber(prest.total_ventas) }}</p>
                        </div>
                    </div>
                    <div v-else class="text-gray-400 text-xs text-center py-4">No hay datos</div>
                </div>

                <!-- Top Prestadores (Ocupa espacio si ventas por sucursal no existe) -->
                <div v-if="!ventasPorSucursal.length && topPrestadores && topPrestadores.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-100 p-3">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Top Prestadores</h3>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Este Mes</span>
                    </div>
                    <div class="space-y-2">
                        <div v-for="(prest, index) in topPrestadores.slice(0,3)" :key="index" class="flex items-center justify-between p-2 bg-gradient-to-r from-purple-50 to-transparent rounded-md">
                            <div class="flex items-center flex-1">
                                <div class="w-7 h-7 bg-gradient-to-br from-purple-400 to-purple-600 rounded-md flex items-center justify-center text-white font-bold text-xs mr-2">
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">{{ prest.nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ prest.total_ordenes }} órdenes</p>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-purple-600">${{ formatNumber(prest.total_facturado) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Ventas por Día -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Tendencia de Ventas</h3>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-primary-400 rounded-full"></div>
                            <span class="text-xs text-gray-600">Últimos 15 días</span>
                        </div>
                    </div>
                    <div v-if="ventasPorDia && ventasPorDia.length > 0" class="h-40 flex items-end justify-between gap-1">
                        <div v-for="(dia, index) in ventasPorDia.slice(-15)" :key="index" class="flex-1 flex flex-col items-center group">
                            <div class="w-full relative">
                                <div
                                    class="w-full bg-gradient-to-t from-primary-400 to-primary-300 rounded-t transition-all group-hover:from-primary-500 group-hover:to-primary-400 cursor-pointer"
                                    :style="`height: ${calculateBarHeight(dia.total) * 1.5}px`"
                                    :title="`${dia.fecha}: $${formatNumber(dia.total)}`"
                                ></div>
                            </div>
                            <p class="text-[8px] text-gray-400 mt-1 transform -rotate-45 origin-top-left whitespace-nowrap">{{ formatDate(dia.fecha) }}</p>
                        </div>
                    </div>
                    <div v-else class="h-40 flex items-center justify-center text-gray-400 text-sm">No hay datos</div>
                </div>
            </div>

            <!-- Órdenes Recientes -->
            <div class="bg-white rounded-md shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-3 py-2 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Órdenes Recientes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
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
                            <tr v-for="orden in ordenesRecientes" :key="orden.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <Link :href="route('ordenes.show', orden.id)" class="text-xs font-semibold text-primary-500 hover:text-primary-600">
                                        {{ orden.numero_orden_completo }}
                                    </Link>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-600">{{ orden.fecha }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">{{ orden.beneficiario }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">{{ orden.sucursal }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-bold text-gray-900 text-right">${{ formatNumber(orden.total) }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-700': orden.estado === 1,
                                        'bg-green-100 text-green-700': orden.estado === 2,
                                        'bg-red-100 text-red-700': orden.estado === 3
                                    }" class="px-1.5 py-0.5 inline-flex text-[11px] font-semibold rounded-full">
                                        {{ orden.estado_texto }}
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
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { reactive } from 'vue';

const props = defineProps({
    stats: Object,
    ventasPorSucursal: Array,
    prestacionesMasVendidas: Array,
    ventasPorDia: Array,
    ordenesRecientes: Array,
    topPrestadores: Array,
    sucursal: String,
    canFilter: Boolean,
    sucursalesDisponibles: Array,
    planesDisponibles: Array,
    filtros: Object,
});

const filtrosActivos = reactive({
    sucursal_id: props.filtros.sucursal_id || '',
    plan_id: props.filtros.plan_id || '',
});

const aplicarFiltros = () => {
    router.get(route('dashboard'), filtrosActivos, { preserveState: true });
};

const limpiarFiltros = () => {
    filtrosActivos.sucursal_id = '';
    filtrosActivos.plan_id = '';
    router.get(route('dashboard'));
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number || 0);
};

const formatDate = (dateString) => {
    const parts = dateString.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}`;
    }
    return dateString;
};

const calculateBarHeight = (value) => {
    if (!props.ventasPorDia || props.ventasPorDia.length === 0) return 0;
    const maxValue = Math.max(...props.ventasPorDia.map(d => d.total || 0));
    return maxValue > 0 ? (value / maxValue) * 100 : 0;
};
</script>
