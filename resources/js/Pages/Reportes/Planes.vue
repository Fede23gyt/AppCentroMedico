<template>
    <Head title="Reporte de Planes" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Reporte de Planes</h1>
                <p class="text-gray-600">Análisis de ventas por plan de cobertura</p>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input v-model="filtros.fecha_desde" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input v-model="filtros.fecha_hasta" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Aplicar Filtros</button>
                    </div>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Planes Activos</p>
                    <p class="text-3xl font-bold mt-2">{{ stats.total_planes }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Total Ventas</p>
                    <p class="text-3xl font-bold mt-2">${{ formatNumber(stats.total_ventas) }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Total Órdenes</p>
                    <p class="text-3xl font-bold mt-2">{{ stats.total_ordenes }}</p>
                </div>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Beneficiarios</p>
                    <p class="text-3xl font-bold mt-2">{{ stats.total_beneficiarios }}</p>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Ventas por Plan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Código</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Beneficiarios</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Órdenes</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Total Ventas</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Promedio/Orden</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="plan in planes" :key="plan.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ plan.nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ plan.nombre_corto }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ plan.total_beneficiarios }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ plan.total_ordenes }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">${{ formatNumber(plan.total_ventas) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    ${{ formatNumber(plan.total_ordenes > 0 ? plan.total_ventas / plan.total_ordenes : 0) }}
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
    planes: Array,
    stats: Object,
    filtros: Object,
});

const filtros = reactive({
    fecha_desde: props.filtros.fecha_desde,
    fecha_hasta: props.filtros.fecha_hasta,
});

const aplicarFiltros = () => {
    router.get(route('reportes.planes'), filtros, { preserveState: true });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number || 0);
};
</script>
