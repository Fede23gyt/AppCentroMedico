<template>
    <Head title="Órdenes" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h1 class="text-base font-semibold text-gray-900">Listado de Ordenes</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Gestiona las órdenes de prestaciones</p>
                </div>
                <Link
                    :href="route('ordenes.create')"
                    class="bg-orange-400 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md font-medium transition-colors text-sm"
                >
                    Nueva Orden
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 mb-3">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
                    <!-- Búsqueda -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Buscar</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="N° Orden, certificado, beneficiario..."
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            @input="debouncedFilter"
                        />
                    </div>

                    <!-- Filtro por Sucursal (solo para admin/supervisor) -->
                    <div v-if="canFilterAll">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sucursal</label>
                        <select
                            v-model="filters.sucursal_id"
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Todas las sucursales</option>
                            <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                                {{ sucursal.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Filtro por Estado -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                        <select
                            v-model="filters.estado"
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="1">Pendiente</option>
                            <option value="2">Pagada</option>
                            <option value="3">Anulada</option>
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input
                            v-model="filters.fecha_desde"
                            type="date"
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            @change="applyFilters"
                        />
                    </div>

                    <!-- Fecha Hasta -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input
                            v-model="filters.fecha_hasta"
                            type="date"
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <!-- Limpiar filtros -->
                <div class="mt-2 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="text-xs text-gray-600 hover:text-gray-800 underline"
                    >
                        Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-md shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">N° Orden</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Fecha</th>
                            <th v-if="canFilterAll" class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Sucursal</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Certificado</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Beneficiario</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Prestador</th>
                            <th class="px-2 py-1.5 text-right text-[11px] font-semibold text-gray-600 uppercase">Total</th>
                            <th class="px-2 py-1.5 text-center text-[11px] font-semibold text-gray-600 uppercase">Estado</th>
                            <th class="px-2 py-1.5 text-center text-[11px] font-semibold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="orden in ordenes.data" :key="orden.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-semibold text-gray-900">
                                {{ orden.numero_orden_completo }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-600">
                                {{ formatDate(orden.fec_ord) }}
                            </td>
                            <td v-if="canFilterAll" class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                {{ orden.sucursal?.nombre || '-' }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                {{ orden.beneficiario?.certificado }}
                            </td>
                            <td class="px-2 py-1.5 text-xs font-medium text-gray-900">
                                {{ orden.beneficiario?.apellido }}, {{ orden.beneficiario?.nombre }}
                            </td>
                            <td class="px-2 py-1.5 text-xs text-gray-500">
                                {{ orden.prestador ? `${orden.prestador.apellido}, ${orden.prestador.nombre}` : '-' }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-bold text-gray-900 text-right">
                                ${{ parseFloat(orden.total).toFixed(2) }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <span :class="getEstadoBadgeClass(orden.estado)" class="px-1.5 py-0.5 inline-flex text-[11px] font-semibold rounded-full">
                                    {{ getEstadoTexto(orden.estado) }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <Link
                                        :href="route('ordenes.show', orden.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Ver
                                    </Link>
                                    <button
                                        v-if="orden.estado === 1"
                                        @click="anularOrden(orden)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Anular
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="ordenes.data.length === 0">
                            <td colspan="8" class="px-2 py-2 text-center text-gray-500 text-xs">
                                No se encontraron órdenes
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="ordenes.links.length > 3" class="bg-gray-50 px-3 py-2 border-t border-gray-100">
                    <div class="flex justify-between items-center">
                        <div class="text-xs text-gray-700">
                            Mostrando {{ ordenes.from || 0 }} a {{ ordenes.to || 0 }} de {{ ordenes.total }} resultados
                        </div>
                        <div class="flex space-x-1">
                            <template v-for="(link, index) in ordenes.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-2 py-1 border rounded text-xs',
                                        link.active
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-2 py-1 border border-gray-300 rounded text-xs text-gray-400 bg-gray-100"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    ordenes: Object,
    filters: Object,
    sucursales: Array,
    canFilterAll: Boolean,
    userSucursalId: Number,
});

const filters = reactive({
    search: props.filters?.search || '',
    estado: props.filters?.estado || '',
    sucursal_id: props.filters?.sucursal_id || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const applyFilters = () => {
    router.get(route('ordenes.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedFilter = debounce(() => {
    applyFilters();
}, 300);

const clearFilters = () => {
    filters.search = '';
    filters.estado = '';
    filters.sucursal_id = '';
    filters.fecha_desde = '';
    filters.fecha_hasta = '';
    applyFilters();
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

const getEstadoBadgeClass = (estado) => {
    const classes = {
        1: 'bg-yellow-100 text-yellow-800',
        2: 'bg-green-100 text-green-800',
        3: 'bg-red-100 text-red-800',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};

const anularOrden = (orden) => {
    if (confirm(`¿Estás seguro de anular la orden N° ${orden.numero_orden}?`)) {
        router.delete(route('ordenes.destroy', orden.id), {
            preserveScroll: true,
        });
    }
};
</script>
