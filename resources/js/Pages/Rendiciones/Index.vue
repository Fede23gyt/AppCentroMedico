<template>
    <Head title="Rendiciones" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Rendiciones</h1>
                    <p class="text-gray-200">Gestiona las rendiciones de prestadores</p>
                </div>
                <Link
                    :href="route('rendiciones.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    Nueva Rendición
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Búsqueda -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="N° Rendición, prestador..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @input="debouncedFilter"
                        />
                    </div>

                    <!-- Filtro por Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select
                            v-model="filters.estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="1">Abierta</option>
                            <option value="2">Cerrada</option>
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input
                            v-model="filters.fecha_desde"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        />
                    </div>

                    <!-- Fecha Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input
                            v-model="filters.fecha_hasta"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <!-- Limpiar filtros -->
                <div class="mt-3 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800 underline"
                    >
                        Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">N° Rendición</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Fecha Inicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Prestador</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Sucursal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="rendicion in rendiciones.data" :key="rendicion.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ rendicion.numero_rendicion_completo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(rendicion.fecha_inicio) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ rendicion.prestador ? `${rendicion.prestador.apellido}, ${rendicion.prestador.nombre}` : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ rendicion.sucursal?.nombre }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${{ parseFloat(rendicion.total).toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getEstadoBadgeClass(rendicion.estado)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ getEstadoTexto(rendicion.estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <Link
                                    :href="route('rendiciones.show', rendicion.id)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Ver
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="rendiciones.data.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron rendiciones
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="rendiciones.links.length > 3" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ rendiciones.from || 0 }} a {{ rendiciones.to || 0 }} de {{ rendiciones.total }} resultados
                        </div>
                        <div class="flex space-x-1">
                            <template v-for="(link, index) in rendiciones.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 border rounded-md text-sm',
                                        link.active
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-400 bg-gray-100"
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
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    rendiciones: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search || '',
    estado: props.filters?.estado || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const applyFilters = () => {
    router.get(route('rendiciones.index'), filters, {
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
</script>
