<template>
    <Head title="Reporte de Cajas" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Reporte de Cajas</h1>
                <p class="text-gray-600">Análisis de movimientos de caja</p>
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-sm text-gray-600">Total Cajas</p>
                    <p class="text-2xl font-bold text-blue-600">{{ stats.total_cajas }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-sm text-gray-600">Cajas Abiertas</p>
                    <p class="text-2xl font-bold text-orange-600">{{ stats.cajas_abiertas }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-sm text-gray-600">Total Ingresos</p>
                    <p class="text-2xl font-bold text-green-600">${{ formatNumber(stats.total_ingresos) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-sm text-gray-600">Total Egresos</p>
                    <p class="text-2xl font-bold text-red-600">${{ formatNumber(stats.total_egresos) }}</p>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Historial de Cajas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">N° Caja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Sucursal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Apertura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Cierre</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Ingresos</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Egresos</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Saldo Final</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="caja in cajas.data" :key="caja.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ caja.numero_caja }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ caja.sucursal }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ caja.usuario }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ caja.fecha_apertura }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ caja.fecha_cierre || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">${{ formatNumber(caja.total_ingresos) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">${{ formatNumber(caja.total_egresos) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">${{ formatNumber(caja.saldo_final) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span :class="{
                                        'bg-green-100 text-green-800': caja.estado === 1,
                                        'bg-gray-100 text-gray-800': caja.estado !== 1
                                    }" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ caja.estado_texto }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="cajas.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-700">
                            Mostrando <span class="font-medium">{{ cajas.from }}</span> a <span class="font-medium">{{ cajas.to }}</span> de <span class="font-medium">{{ cajas.total }}</span> resultados
                        </p>
                        <div class="flex gap-1">
                            <Link v-for="link in cajas.links" :key="link.label" :href="link.url"
                                  :class="{'bg-blue-600 text-white': link.active, 'bg-white text-gray-700 hover:bg-gray-50': !link.active}"
                                  class="px-3 py-2 border border-gray-300 rounded-md text-sm" v-html="link.label">
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
    cajas: Object,
    stats: Object,
    filtros: Object,
});

const filtros = reactive({
    fecha_desde: props.filtros.fecha_desde,
    fecha_hasta: props.filtros.fecha_hasta,
});

const aplicarFiltros = () => {
    router.get(route('reportes.cajas'), filtros, { preserveState: true });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number || 0);
};
</script>
