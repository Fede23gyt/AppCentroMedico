<template>
    <Head title="Reporte de Sucursales" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Reporte de Sucursales</h1>
                <p class="text-gray-600">Análisis de ventas por sucursal</p>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input v-model="filtros.fecha_desde" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input v-model="filtros.fecha_hasta" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rubro</label>
                        <select v-model="filtros.rubro_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option :value="null">Todos los rubros</option>
                            <option v-for="rubro in rubros" :key="rubro.id" :value="rubro.id">{{ rubro.nombre }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Aplicar Filtros</button>
                    </div>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
                    <p class="text-sm opacity-90">Total Sucursales</p>
                    <p class="text-3xl font-bold mt-2">{{ stats.total_sucursales }}</p>
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
                    <p class="text-sm opacity-90">Promedio/Sucursal</p>
                    <p class="text-3xl font-bold mt-2">${{ formatNumber(stats.promedio_por_sucursal) }}</p>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Gráfico de Cantidad de Órdenes -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cantidad de Órdenes por Sucursal</h3>
                    <div :style="{ height: chartHeight }">
                        <canvas ref="chartOrdenes"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Total de Ventas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Total de Ventas por Sucursal</h3>
                    <div :style="{ height: chartHeight }">
                        <canvas ref="chartVentas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Ventas por Sucursal</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Sucursal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Dirección</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Total Órdenes</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Pendientes</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Pagadas</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Promedio</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Total Ventas</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="sucursal in sucursales" :key="sucursal.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ sucursal.nombre }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ sucursal.direccion }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ sucursal.total_ordenes }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 text-right">{{ sucursal.ordenes_pendientes }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">{{ sucursal.ordenes_pagadas }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ formatNumber(sucursal.promedio_venta) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">${{ formatNumber(sucursal.total_ventas) }}</td>
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
import { reactive, ref, onMounted, watch, nextTick, computed } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    sucursales: Array,
    stats: Object,
    filtros: Object,
    rubros: Array,
});

const filtros = reactive({
    fecha_desde: props.filtros.fecha_desde,
    fecha_hasta: props.filtros.fecha_hasta,
    rubro_id: props.filtros.rubro_id,
});

const chartOrdenes = ref(null);
const chartVentas = ref(null);
let chartOrdenesInstance = null;
let chartVentasInstance = null;

// Calcular altura dinámica basada en cantidad de sucursales
const chartHeight = computed(() => {
    const numSucursales = props.sucursales?.length || 0;
    const minHeight = 300;
    const heightPerSucursal = 40;
    return Math.max(minHeight, numSucursales * heightPerSucursal) + 'px';
});

const aplicarFiltros = () => {
    router.get(route('reportes.sucursales'), filtros, { preserveState: true });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number || 0);
};

const initCharts = () => {
    if (!props.sucursales || props.sucursales.length === 0) return;

    // Destruir gráficos existentes
    if (chartOrdenesInstance) chartOrdenesInstance.destroy();
    if (chartVentasInstance) chartVentasInstance.destroy();

    const labels = props.sucursales.map(s => s.nombre);
    const ordenesData = props.sucursales.map(s => s.total_ordenes);
    const ventasData = props.sucursales.map(s => parseFloat(s.total_ventas || 0));

    // Paleta de colores modernos con degradados
    const modernColors = [
        { bg: 'rgba(99, 102, 241, 0.8)', border: 'rgb(99, 102, 241)' },     // Indigo
        { bg: 'rgba(59, 130, 246, 0.8)', border: 'rgb(59, 130, 246)' },     // Blue
        { bg: 'rgba(139, 92, 246, 0.8)', border: 'rgb(139, 92, 246)' },     // Purple
        { bg: 'rgba(236, 72, 153, 0.8)', border: 'rgb(236, 72, 153)' },     // Pink
        { bg: 'rgba(249, 115, 22, 0.8)', border: 'rgb(249, 115, 22)' },     // Orange
        { bg: 'rgba(234, 179, 8, 0.8)', border: 'rgb(234, 179, 8)' },       // Yellow
        { bg: 'rgba(34, 197, 94, 0.8)', border: 'rgb(34, 197, 94)' },       // Green
        { bg: 'rgba(20, 184, 166, 0.8)', border: 'rgb(20, 184, 166)' },     // Teal
        { bg: 'rgba(14, 165, 233, 0.8)', border: 'rgb(14, 165, 233)' },     // Sky
        { bg: 'rgba(168, 85, 247, 0.8)', border: 'rgb(168, 85, 247)' },     // Violet
    ];

    const backgroundColors = props.sucursales.map((_, index) => {
        return modernColors[index % modernColors.length].bg;
    });
    const borderColors = props.sucursales.map((_, index) => {
        return modernColors[index % modernColors.length].border;
    });

    // Gráfico de Órdenes
    if (chartOrdenes.value) {
        chartOrdenesInstance = new Chart(chartOrdenes.value, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de Órdenes',
                    data: ordenesData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        right: 20
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Órdenes: ' + context.parsed.x;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            lineWidth: 1
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Ventas
    if (chartVentas.value) {
        chartVentasInstance = new Chart(chartVentas.value, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total de Ventas',
                    data: ventasData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        right: 20
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Ventas: $' + new Intl.NumberFormat('es-AR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }).format(context.parsed.x);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            lineWidth: 1
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return '$' + new Intl.NumberFormat('es-AR').format(value);
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
    }
};

onMounted(() => {
    nextTick(() => {
        initCharts();
    });
});

watch(() => props.sucursales, () => {
    nextTick(() => {
        initCharts();
    });
}, { deep: true });
</script>
