<template>
    <Head title="Rendiciones" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h1 class="text-base font-semibold text-gray-900">Rendiciones</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Gestiona las rendiciones de prestadores</p>
                </div>
                <Link
                    :href="route('rendiciones.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md font-medium transition-colors text-sm"
                >
                    Nueva Rendición
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 mb-3">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    <!-- Búsqueda -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Buscar</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="N° Rendición, prestador..."
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            @input="debouncedFilter"
                        />
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
                            <option value="1">Abierta</option>
                            <option value="2">Cerrada</option>
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
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">N° Rendición</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Fecha Inicio</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Prestador</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Sucursal</th>
                            <th class="px-2 py-1.5 text-right text-[11px] font-semibold text-gray-600 uppercase">Total</th>
                            <th class="px-2 py-1.5 text-center text-[11px] font-semibold text-gray-600 uppercase">Estado</th>
                            <th class="px-2 py-1.5 text-center text-[11px] font-semibold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="rendicion in rendiciones.data" :key="rendicion.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-semibold text-gray-900">
                                {{ rendicion.numero_rendicion_completo }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-600">
                                {{ formatDate(rendicion.fecha_inicio) }}
                            </td>
                            <td class="px-2 py-1.5 text-xs font-medium text-gray-900">
                                {{ rendicion.prestador ? `${rendicion.prestador.apellido}, ${rendicion.prestador.nombre}` : '-' }}
                            </td>
                            <td class="px-2 py-1.5 text-xs text-gray-500">
                                {{ rendicion.sucursal?.nombre }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-bold text-gray-900 text-right">
                                ${{ parseFloat(rendicion.total).toFixed(2) }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <span :class="getEstadoBadgeClass(rendicion.estado)" class="px-1.5 py-0.5 inline-flex text-[11px] font-semibold rounded-full">
                                    {{ getEstadoTexto(rendicion.estado) }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500 text-center">
                                <Link
                                    :href="route('rendiciones.show', rendicion.id)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Ver
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="rendiciones.data.length === 0">
                            <td colspan="7" class="px-2 py-2 text-center text-gray-500 text-xs">
                                No se encontraron rendiciones
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="rendiciones.links.length > 3" class="bg-gray-50 px-3 py-2 border-t border-gray-100">
                    <div class="flex justify-between items-center">
                        <div class="text-xs text-gray-700">
                            Mostrando {{ rendiciones.from || 0 }} a {{ rendiciones.to || 0 }} de {{ rendiciones.total }} resultados
                        </div>
                        <div class="flex space-x-1">
                            <template v-for="(link, index) in rendiciones.links" :key="index">
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
