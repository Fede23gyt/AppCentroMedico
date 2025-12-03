<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard - {{ sucursal }}</h1>
                <p class="text-gray-600">Resumen general del sistema</p>
            </div>

            <!-- Filtros (Solo Admin/Supervisor) -->
            <div v-if="canFilter" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros</h3>
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sucursal</label>
                        <select v-model="filtrosActivos.sucursal_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Todas las sucursales</option>
                            <option v-for="sucursal in sucursalesDisponibles" :key="sucursal.id" :value="sucursal.id">
                                {{ sucursal.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Plan</label>
                        <select v-model="filtrosActivos.plan_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Todos los planes</option>
                            <option v-for="plan in planesDisponibles" :key="plan.id" :value="plan.id">
                                {{ plan.nombre }} ({{ plan.nombre_corto }})
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                            Aplicar Filtros
                        </button>
                        <button type="button" @click="limpiarFiltros" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Stats Cards - Primera Fila -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Órdenes Hoy</p>
                            <p class="text-3xl font-bold text-blue-600">{{ stats.ordenes_hoy }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Ventas Hoy</p>
                            <p class="text-3xl font-bold text-green-600">${{ formatNumber(stats.ventas_hoy) }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Órdenes Pendientes</p>
                            <p class="text-3xl font-bold text-orange-600">{{ stats.ordenes_pendientes }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Rendiciones Abiertas</p>
                            <p class="text-3xl font-bold text-purple-600">{{ stats.rendiciones_abiertas }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards - Segunda Fila -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Ventas del Mes</p>
                    <p class="text-3xl font-bold mt-2">${{ formatNumber(stats.ventas_mes) }}</p>
                    <p class="text-xs opacity-75 mt-2">{{ stats.ordenes_mes }} órdenes</p>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Ventas del Año</p>
                    <p class="text-3xl font-bold mt-2">${{ formatNumber(stats.ventas_anio) }}</p>
                    <p class="text-xs opacity-75 mt-2">Total acumulado</p>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Prestadores Activos</p>
                    <p class="text-3xl font-bold mt-2">{{ stats.total_prestadores }}</p>
                    <p class="text-xs opacity-75 mt-2">Total registrados</p>
                </div>
            </div>

            <!-- Gráficos y Tablas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Ventas por Sucursal (solo para admin) -->
                <div v-if="ventasPorSucursal.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ventas por Sucursal (Mes Actual)</h3>
                    <div class="space-y-3">
                        <div v-for="(sucursal, index) in ventasPorSucursal" :key="index" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ sucursal.nombre }}</p>
                                <p class="text-sm text-gray-600">{{ sucursal.total_ordenes }} órdenes</p>
                            </div>
                            <p class="text-lg font-bold text-blue-600">${{ formatNumber(sucursal.total_ventas) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Prestaciones Más Vendidas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Prestaciones Más Vendidas</h3>
                    <div v-if="prestacionesMasVendidas && prestacionesMasVendidas.length > 0" class="space-y-2">
                        <div v-for="(prest, index) in prestacionesMasVendidas.slice(0, 5)" :key="index" class="flex items-center justify-between py-2 border-b last:border-b-0">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ prest.codigo }} - {{ (prest.descripcion || '').substring(0, 30) }}{{ prest.descripcion && prest.descripcion.length > 30 ? '...' : '' }}</p>
                                <p class="text-xs text-gray-600">{{ prest.total_cantidad }} unidades</p>
                            </div>
                            <p class="text-sm font-bold text-green-600">${{ formatNumber(prest.total_ventas) }}</p>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 text-sm">No hay datos disponibles</div>
                </div>

                <!-- Top Prestadores -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Prestadores del Mes</h3>
                    <div v-if="topPrestadores && topPrestadores.length > 0" class="space-y-3">
                        <div v-for="(prest, index) in topPrestadores" :key="index" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center flex-1">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ prest.nombre }}</p>
                                    <p class="text-xs text-gray-600">{{ prest.total_ordenes }} órdenes</p>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-purple-600">${{ formatNumber(prest.total_facturado) }}</p>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 text-sm">No hay datos disponibles</div>
                </div>

                <!-- Ventas por Día (Gráfico Simple) -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ventas Últimos 30 Días</h3>
                    <div v-if="ventasPorDia && ventasPorDia.length > 0" class="h-64 flex items-end justify-between gap-1">
                        <div v-for="(dia, index) in ventasPorDia.slice(-15)" :key="index" class="flex-1 flex flex-col items-center">
                            <div
                                class="w-full bg-blue-500 rounded-t transition-all hover:bg-blue-600 cursor-pointer"
                                :style="`height: ${calculateBarHeight(dia.total)}%`"
                                :title="`${dia.fecha}: $${formatNumber(dia.total)}`"
                            ></div>
                            <p class="text-xs text-gray-600 mt-2 transform -rotate-45 origin-top-left">{{ dia.fecha }}</p>
                        </div>
                    </div>
                    <div v-else class="h-64 flex items-center justify-center text-gray-500 text-sm">No hay datos de ventas</div>
                </div>
            </div>

            <!-- Órdenes Recientes -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Órdenes Recientes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">N° Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Beneficiario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Sucursal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="orden in ordenesRecientes" :key="orden.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    <Link :href="route('ordenes.show', orden.id)">{{ orden.numero_orden_completo }}</Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ orden.fecha }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ orden.beneficiario }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ orden.sucursal }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">${{ formatNumber(orden.total) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-800': orden.estado === 1,
                                        'bg-green-100 text-green-800': orden.estado === 2,
                                        'bg-red-100 text-red-800': orden.estado === 3
                                    }" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
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

const calculateBarHeight = (value) => {
    if (!props.ventasPorDia || props.ventasPorDia.length === 0) return 0;
    const maxValue = Math.max(...props.ventasPorDia.map(d => d.total || 0));
    return maxValue > 0 ? (value / maxValue) * 100 : 0;
};
</script>
