<template>
    <Head :title="`Gestionar Prestaciones - ${plan.nombre}`" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header mejorado con datos del plan -->
            <div class="mb-8">
                <!-- Navegación y título -->
                <div class="flex items-center space-x-3 mb-4">
                    <Link
                        :href="route('planes.index')"
                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
                        title="Volver a planes"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Prestaciones del Plan</h1>
                        <p class="text-gray-600">Gestiona las prestaciones asociadas a este plan de salud</p>
                    </div>
                </div>

                <!-- Tarjeta con información del plan -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Columna 1: Información del plan -->
                        <div>
                            <!-- Información principal del plan -->
                            <div class="flex items-center space-x-4 mb-6">
                                <div>
                                    <h2 class="text-2xl font-semibold text-gray-900">{{ plan.nombre }}</h2>
                                    <p class="text-lg text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded-md inline-block mt-1">{{ plan.nombre_corto }}</p>
                                </div>
                                <span
                                    :class="[
                                        'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                                        getEstadoClass(plan.estado)
                                    ]"
                                >
                                    {{ capitalizeFirst(plan.estado) }}
                                </span>
                            </div>

                            <!-- Vigencia -->
                            <div class="mb-4">
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Vigencia</h3>
                                <div class="text-sm text-gray-600">
                                    <div class="flex items-center mb-1">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Desde: {{ formatDate(plan.vigencia_desde) }}</span>
                                    </div>
                                    <div v-if="plan.vigencia_hasta" class="flex items-center">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Hasta: {{ formatDate(plan.vigencia_hasta) }}</span>
                                    </div>
                                    <div v-else class="flex items-center text-green-600 font-medium">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Sin vencimiento</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción del plan si existe -->
                            <div v-if="plan.descripcion">
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Descripción</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">{{ plan.descripcion }}</p>
                            </div>
                        </div>

                        <!-- Columna 2: Cards de estadísticas (verticales) -->
                        <div class="flex flex-col space-y-3">
                            <!-- Card Total Prestaciones -->
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-blue-700 mb-1">Total Prestaciones</p>
                                        <p class="text-3xl font-bold text-blue-900">{{ plan.prestaciones?.length || 0 }}</p>
                                    </div>
                                    <div class="bg-blue-200 rounded-full p-3">
                                        <svg class="h-8 w-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Activas -->
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-green-700 mb-1">Activas</p>
                                        <p class="text-3xl font-bold text-green-900">{{ prestacionesActivas }}</p>
                                    </div>
                                    <div class="bg-green-200 rounded-full p-3">
                                        <svg class="h-8 w-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Rubros -->
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-purple-700 mb-1">Rubros Diferentes</p>
                                        <p class="text-3xl font-bold text-purple-900">{{ rubrosUnicos }}</p>
                                    </div>
                                    <div class="bg-purple-200 rounded-full p-3">
                                        <svg class="h-8 w-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna 3: Botones de acción (verticales) -->
                        <div class="flex flex-col space-y-3">
                            <a
                                :href="route('planes.export-pdf', plan.id)"
                                target="_blank"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Exportar PDF
                            </a>
                            <button
                                @click="showAccionesMasivas = true"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a1 1 0 01-1-1V9a1 1 0 011-1h1a2 2 0 100-4H4a1 1 0 01-1-1V4a1 1 0 011-1h3a1 1 0 001-1v1z"/>
                                </svg>
                                Acciones Masivas
                            </button>
                            <button
                                @click="showAgregarModal = true"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Agregar Prestación
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <div v-if="$page.props.flash?.success" class="mb-6">
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ $page.props.flash.success }}
                </div>
            </div>

            <div v-if="$page.props.flash?.error" class="mb-6">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ $page.props.flash.error }}
                </div>
            </div>

            <!-- Filtros para prestaciones -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Prestaciones</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input
                            v-model="filtros.busqueda"
                            type="text"
                            placeholder="Código o nombre..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select
                            v-model="filtros.estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todos</option>
                            <option value="activo">Activas</option>
                            <option value="inactivo">Inactivas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rubro</label>
                        <select
                            v-model="filtros.rubro"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todos los rubros</option>
                            <option
                                v-for="rubro in rubros"
                                :key="rubro.id"
                                :value="rubro.id"
                            >
                                {{ rubro.nombre }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="limpiarFiltros"
                            class="w-full px-3 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de prestaciones -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="prestacionesFiltradas.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Sin prestaciones</h3>
                    <p class="text-gray-500 mb-4">No se encontraron prestaciones con los filtros aplicados</p>
                    <button
                        @click="showAgregarModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                    >
                        Agregar Primera Prestación
                    </button>
                </div>

                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            <input
                                type="checkbox"
                                v-model="seleccionarTodas"
                                @change="toggleSeleccionTodas"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Prestación
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Rubro
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Valor Afiliado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Valor Particular
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Estado en Plan
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-200 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="prestacion in prestacionesPaginadas"
                        :key="prestacion.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input
                                type="checkbox"
                                v-model="prestacionesSeleccionadas"
                                :value="prestacion.id"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-mono font-semibold text-gray-900 bg-gray-100 px-2 py-1 rounded">
                                {{ prestacion.codigo }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ prestacion.nombre }}
                            </div>
                            <div v-if="prestacion.descripcion" class="text-xs text-gray-500 mt-1 line-clamp-2">
                                {{ prestacion.descripcion }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ prestacion.rubro?.nombre }}
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-mono">
                                    ${{ formatCurrency(prestacion.pivot?.valor_afiliado) }}
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-mono">
                                    ${{ formatCurrency(prestacion.pivot?.valor_particular) }}
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                        getEstadoClass(prestacion.pivot?.estado || 'activo')
                                    ]"
                                >
                                    {{ capitalizeFirst(prestacion.pivot?.estado || 'activo') }}
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="editarPrecios(prestacion)"
                                    class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors"
                                    title="Editar precios y estado en este plan"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="toggleEstadoPrestacion(prestacion)"
                                    :class="[
                                        'p-2 rounded-lg transition-opacity hover:opacity-80',
                                        prestacion.pivot?.estado === 'activo'
                                            ? 'text-red-600 hover:text-red-700 hover:bg-red-50'
                                            : 'text-green-600 hover:text-green-700 hover:bg-green-50'
                                    ]"
                                    :title="prestacion.pivot?.estado === 'activo' ? 'Desactivar en este plan' : 'Activar en este plan'"
                                >
                                    <svg v-if="prestacion.pivot?.estado === 'activo'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="confirmarDesasociacion(prestacion)"
                                    class="p-2 text-orange-600 hover:text-orange-800 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Desasociar del plan (quitar del plan)"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="totalPaginas > 1" class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            @click="paginaActual > 1 && paginaActual--"
                            :disabled="paginaActual <= 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                        >
                            Anterior
                        </button>
                        <button
                            @click="paginaActual < totalPaginas && paginaActual++"
                            :disabled="paginaActual >= totalPaginas"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                        >
                            Siguiente
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">{{ (paginaActual - 1) * itemsPorPagina + 1 }}</span>
                                a
                                <span class="font-medium">{{ Math.min(paginaActual * itemsPorPagina, prestacionesFiltradas.length) }}</span>
                                de
                                <span class="font-medium">{{ prestacionesFiltradas.length }}</span>
                                prestaciones
                            </p>
                        </div>
                        <div class="flex space-x-1">
                            <button
                                v-for="pagina in paginasVisibles"
                                :key="pagina"
                                @click="paginaActual = pagina"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    pagina === paginaActual
                                        ? 'bg-blue-600 border-blue-600 text-white'
                                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                {{ pagina }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Agregar Prestación -->
            <div v-if="showAgregarModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showAgregarModal = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6" @click.stop>
                        <form @submit.prevent="agregarPrestacion">
                            <div class="mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Agregar Prestación al Plan</h3>
                                <p class="mt-1 text-sm text-gray-500">Selecciona una prestación y configura sus valores para este plan</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Prestación -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prestación *</label>
                                    <select
                                        v-model="nuevaAsociacion.prestacion_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">Seleccionar prestación...</option>
                                        <option
                                            v-for="prestacion in prestacionesDisponibles"
                                            :key="prestacion.id"
                                            :value="prestacion.id"
                                        >
                                            {{ prestacion.codigo }} - {{ prestacion.nombre }} ({{ prestacion.rubro?.nombre }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Valores -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Afiliado *</label>
                                    <input
                                        v-model.number="nuevaAsociacion.valor_afiliado"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Particular *</label>
                                    <input
                                        v-model.number="nuevaAsociacion.valor_particular"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <!-- Cantidades máximas -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad Máx. Individual</label>
                                    <input
                                        v-model.number="nuevaAsociacion.cant_max_individual"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad Máx. Grupo</label>
                                    <input
                                        v-model.number="nuevaAsociacion.cant_max_grupo"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                                    <select
                                        v-model="nuevaAsociacion.estado"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>

                                <!-- Fechas de vigencia -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                                    <input
                                        v-model="nuevaAsociacion.fecha_desde"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                                    <input
                                        v-model="nuevaAsociacion.fecha_hasta"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <!-- Observaciones -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                    <textarea
                                        v-model="nuevaAsociacion.observaciones"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Observaciones adicionales..."
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="showAgregarModal = false"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    Agregar Prestación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Asociación -->
            <div v-if="showEditarModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showEditarModal = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6" @click.stop>
                        <form @submit.prevent="actualizarAsociacion">
                            <div class="mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Editar Asociación</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Prestación: <strong>{{ prestacionEditando?.nombre }}</strong>
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Valores -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Afiliado *</label>
                                    <input
                                        v-model.number="asociacionEditando.valor_afiliado"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Particular *</label>
                                    <input
                                        v-model.number="asociacionEditando.valor_particular"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <!-- Cantidades máximas -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad Máx. Individual</label>
                                    <input
                                        v-model.number="asociacionEditando.cant_max_individual"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad Máx. Grupo</label>
                                    <input
                                        v-model.number="asociacionEditando.cant_max_grupo"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                                    <select
                                        v-model="asociacionEditando.estado"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>

                                <!-- Fechas de vigencia -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                                    <input
                                        v-model="asociacionEditando.fecha_desde"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                                    <input
                                        v-model="asociacionEditando.fecha_hasta"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <!-- Observaciones -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                    <textarea
                                        v-model="asociacionEditando.observaciones"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Observaciones adicionales..."
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="showEditarModal = false"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    Actualizar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Precios y Estado -->
            <div v-if="showEditarPreciosModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showEditarPreciosModal = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                        <form @submit.prevent="actualizarPreciosYEstado">
                            <!-- Header -->
                            <div class="flex items-center mb-6">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="text-center mb-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Editar Precios y Estado</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        <span class="font-mono font-semibold text-gray-900">{{ prestacionEditandoPrecios?.codigo }}</span> - {{ prestacionEditandoPrecios?.nombre }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Solo se modificarán los precios y estado para el plan "{{ plan.nombre }}"
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Precios -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Valor Afiliado *</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input
                                                v-model.number="preciosEditando.valor_afiliado"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                                placeholder="0.00"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Valor Particular *</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input
                                                v-model.number="preciosEditando.valor_particular"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                                placeholder="0.00"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado en este Plan *</label>
                                    <select
                                        v-model="preciosEditando.estado"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    >
                                        <option value="activo">✅ Activo</option>
                                        <option value="inactivo">❌ Inactivo</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Este estado solo afecta a esta prestación en el plan actual
                                    </p>
                                </div>

                                <!-- Límites opcionales -->
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Límites (Opcionales)</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Máx. Individual</label>
                                            <input
                                                v-model.number="preciosEditando.cant_max_individual"
                                                type="number"
                                                min="0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                                                placeholder="Sin límite"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Máx. Grupo</label>
                                            <input
                                                v-model.number="preciosEditando.cant_max_grupo"
                                                type="number"
                                                min="0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                                                placeholder="Sin límite"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                                <button
                                    type="button"
                                    @click="showEditarPreciosModal = false"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors flex items-center"
                                >
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Acciones Masivas -->
            <div v-if="showAccionesMasivas" class="fixed inset-0 z-50 overflow-y-auto" @click="showAccionesMasivas = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6" @click.stop>
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Acciones Masivas</h3>
                            <p class="mt-1 text-sm text-gray-500">Realiza operaciones en lote sobre las prestaciones del plan</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Copiar desde otro plan -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Copiar Prestaciones</h4>
                                <form @submit.prevent="copiarPrestaciones">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Plan Origen</label>
                                            <select
                                                v-model="copiaDatos.plan_origen_id"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                            >
                                                <option value="">Seleccionar plan...</option>
                                                <option
                                                    v-for="planItem in planesParaCopiar"
                                                    :key="planItem.id"
                                                    :value="planItem.id"
                                                >
                                                    {{ planItem.nombre }} ({{ planItem.prestaciones_count }} prestaciones)
                                                </option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="checkbox" v-model="copiaDatos.incluir_precios" class="mr-2" />
                                                <span class="text-sm text-gray-700">Incluir precios</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" v-model="copiaDatos.solo_activas" class="mr-2" />
                                                <span class="text-sm text-gray-700">Solo prestaciones activas</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" v-model="copiaDatos.sobrescribir_existentes" class="mr-2" />
                                                <span class="text-sm text-gray-700">Sobrescribir existentes</span>
                                            </label>
                                        </div>
                                        <button
                                            type="submit"
                                            class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 text-sm"
                                        >
                                            Copiar Prestaciones
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Ajuste masivo de precios -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Ajuste Masivo de Precios</h4>
                                <form @submit.prevent="ajustarPrecios">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ajuste</label>
                                            <select
                                                v-model="ajusteDatos.tipo_ajuste"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                            >
                                                <option value="porcentaje">Porcentaje</option>
                                                <option value="monto_fijo">Monto Fijo</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                                            <input
                                                v-model.number="ajusteDatos.valor_ajuste"
                                                type="number"
                                                step="0.01"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                                :placeholder="ajusteDatos.tipo_ajuste === 'porcentaje' ? '10 (para 10%)' : '100 (para $100)'"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Aplicar a</label>
                                            <select
                                                v-model="ajusteDatos.aplicar_a"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                            >
                                                <option value="ambos">Ambos valores</option>
                                                <option value="afiliado">Solo afiliado</option>
                                                <option value="particular">Solo particular</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Rubro (opcional)</label>
                                            <select
                                                v-model="ajusteDatos.rubro_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                            >
                                                <option value="">Todos los rubros</option>
                                                <option
                                                    v-for="rubro in rubros"
                                                    :key="rubro.id"
                                                    :value="rubro.id"
                                                >
                                                    {{ rubro.nombre }}
                                                </option>
                                            </select>
                                        </div>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="ajusteDatos.solo_activas" class="mr-2" />
                                            <span class="text-sm text-gray-700">Solo prestaciones activas</span>
                                        </label>
                                        <button
                                            type="submit"
                                            class="w-full bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700 text-sm"
                                        >
                                            Ajustar Precios
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Exportar/Importar -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Exportar/Importar</h4>
                                <div class="space-y-3">
                                    <a
                                        :href="route('planes.export-excel', plan.id)"
                                        class="block w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 text-sm text-center"
                                    >
                                        Exportar a Excel
                                    </a>
                                    <div>
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept=".xlsx,.xls"
                                            class="hidden"
                                            @change="importarExcel"
                                        />
                                        <button
                                            @click="$refs.fileInput.click()"
                                            type="button"
                                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm"
                                        >
                                            Importar desde Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Acciones seleccionadas -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">
                                    Acciones en Seleccionadas
                                    <span v-if="prestacionesSeleccionadas.length > 0" class="text-sm text-gray-500">
                                        ({{ prestacionesSeleccionadas.length }} seleccionadas)
                                    </span>
                                </h4>
                                <div class="space-y-2">
                                    <button
                                        @click="activarSeleccionadas"
                                        :disabled="prestacionesSeleccionadas.length === 0"
                                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Activar Seleccionadas
                                    </button>
                                    <button
                                        @click="desactivarSeleccionadas"
                                        :disabled="prestacionesSeleccionadas.length === 0"
                                        class="w-full bg-yellow-600 text-white py-2 px-4 rounded-md hover:bg-yellow-700 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Desactivar Seleccionadas
                                    </button>
                                    <button
                                        @click="desasociarSeleccionadas"
                                        :disabled="prestacionesSeleccionadas.length === 0"
                                        class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Desasociar Seleccionadas
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button
                                @click="showAccionesMasivas = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de confirmación de desasociación -->
            <div v-if="showDesasociarModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showDesasociarModal = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" @click.stop>
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Desasociar Prestación</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que quieres desasociar la prestación
                                        <strong>{{ prestacionToDesasociar?.nombre }}</strong>
                                        del plan <strong>{{ plan.nombre }}</strong>?
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="desasociarPrestacion"
                                type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Desasociar
                            </button>
                            <button
                                @click="showDesasociarModal = false"
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, reactive } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    plan: Object,
    prestacionesDisponibles: Array,
    rubros: Array,
    planesParaCopiar: Array,
})

// Estado reactivo
const showAgregarModal = ref(false)
const showEditarModal = ref(false)
const showEditarPreciosModal = ref(false)
const showAccionesMasivas = ref(false)
const showDesasociarModal = ref(false)
const prestacionToDesasociar = ref(null)
const prestacionEditando = ref(null)
const prestacionEditandoPrecios = ref(null)
const seleccionarTodas = ref(false)
const prestacionesSeleccionadas = ref([])
const paginaActual = ref(1)
const itemsPorPagina = 15

// Filtros
const filtros = reactive({
    busqueda: '',
    estado: '',
    rubro: ''
})

// Formularios
const nuevaAsociacion = reactive({
    prestacion_id: '',
    valor_afiliado: 0,
    valor_particular: 0,
    cant_max_individual: null,
    cant_max_grupo: null,
    estado: 'activo',
    fecha_desde: null,
    fecha_hasta: null,
    observaciones: ''
})

const asociacionEditando = reactive({
    valor_afiliado: 0,
    valor_particular: 0,
    cant_max_individual: null,
    cant_max_grupo: null,
    estado: 'activo',
    fecha_desde: null,
    fecha_hasta: null,
    observaciones: ''
})

const copiaDatos = reactive({
    plan_origen_id: '',
    incluir_precios: true,
    solo_activas: true,
    sobrescribir_existentes: false
})

const ajusteDatos = reactive({
    tipo_ajuste: 'porcentaje',
    valor_ajuste: 0,
    aplicar_a: 'ambos',
    solo_activas: true,
    rubro_id: ''
})

const preciosEditando = reactive({
    valor_afiliado: 0,
    valor_particular: 0,
    cant_max_individual: null,
    cant_max_grupo: null,
    estado: 'activo'
})

// Computadas
const prestacionesActivas = computed(() => {
    return props.plan.prestaciones?.filter(p => (p.pivot?.estado || 'activo') === 'activo').length || 0
})

const rubrosUnicos = computed(() => {
    if (!props.plan.prestaciones) return 0
    const rubrosSet = new Set()
    props.plan.prestaciones.forEach(p => {
        if (p.rubro) rubrosSet.add(p.rubro.id)
    })
    return rubrosSet.size
})

const promedioValorAfiliado = computed(() => {
    if (!props.plan.prestaciones || props.plan.prestaciones.length === 0) return '0.00'
    const suma = props.plan.prestaciones.reduce((acc, p) => acc + (p.pivot?.valor_afiliado || 0), 0)
    return (suma / props.plan.prestaciones.length).toFixed(2)
})

const promedioValorParticular = computed(() => {
    if (!props.plan.prestaciones || props.plan.prestaciones.length === 0) return '0.00'
    const suma = props.plan.prestaciones.reduce((acc, p) => acc + (p.pivot?.valor_particular || 0), 0)
    return (suma / props.plan.prestaciones.length).toFixed(2)
})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('es-AR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const prestacionesFiltradas = computed(() => {
    if (!props.plan.prestaciones) return []

    return props.plan.prestaciones.filter(prestacion => {
        // Filtro por búsqueda
        if (filtros.busqueda) {
            const busqueda = filtros.busqueda.toLowerCase()
            if (!prestacion.codigo.toLowerCase().includes(busqueda) &&
                !prestacion.nombre.toLowerCase().includes(busqueda)) {
                return false
            }
        }

        // Filtro por estado
        if (filtros.estado) {
            const estado = prestacion.pivot?.estado || 'activo'
            if (estado !== filtros.estado) return false
        }

        // Filtro por rubro
        if (filtros.rubro) {
            if (prestacion.rubro?.id !== parseInt(filtros.rubro)) return false
        }

        return true
    })
})

const totalPaginas = computed(() => {
    return Math.ceil(prestacionesFiltradas.value.length / itemsPorPagina)
})

const prestacionesPaginadas = computed(() => {
    const inicio = (paginaActual.value - 1) * itemsPorPagina
    const fin = inicio + itemsPorPagina
    return prestacionesFiltradas.value.slice(inicio, fin)
})

const paginasVisibles = computed(() => {
    const total = totalPaginas.value
    const actual = paginaActual.value
    const paginas = []

    const inicio = Math.max(1, actual - 2)
    const fin = Math.min(total, actual + 2)

    for (let i = inicio; i <= fin; i++) {
        paginas.push(i)
    }

    return paginas
})

// Utilidades
const formatCurrency = (value) => {
    if (!value) return '0.00'
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value)
}

const getEstadoClass = (estado) => {
    const classes = {
        activo: 'bg-green-100 text-green-800',
        inactivo: 'bg-red-100 text-red-800',
        suspendido: 'bg-yellow-100 text-yellow-800'
    }
    return classes[estado] || 'bg-gray-100 text-gray-800'
}

const capitalizeFirst = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1)
}

// Funciones
const limpiarFiltros = () => {
    filtros.busqueda = ''
    filtros.estado = ''
    filtros.rubro = ''
    paginaActual.value = 1
}

const toggleSeleccionTodas = () => {
    if (seleccionarTodas.value) {
        prestacionesSeleccionadas.value = prestacionesPaginadas.value.map(p => p.id)
    } else {
        prestacionesSeleccionadas.value = []
    }
}

const limpiarFormularioNuevo = () => {
    Object.assign(nuevaAsociacion, {
        prestacion_id: '',
        valor_afiliado: 0,
        valor_particular: 0,
        cant_max_individual: null,
        cant_max_grupo: null,
        estado: 'activo',
        fecha_desde: null,
        fecha_hasta: null,
        observaciones: ''
    })
}

const agregarPrestacion = () => {
    router.post(route('planes.prestaciones.store', props.plan.id), nuevaAsociacion, {
        onSuccess: () => {
            showAgregarModal.value = false
            limpiarFormularioNuevo()
        }
    })
}

const editarAsociacion = (prestacion) => {
    prestacionEditando.value = prestacion
    Object.assign(asociacionEditando, {
        valor_afiliado: prestacion.pivot?.valor_afiliado || 0,
        valor_particular: prestacion.pivot?.valor_particular || 0,
        cant_max_individual: prestacion.pivot?.cant_max_individual,
        cant_max_grupo: prestacion.pivot?.cant_max_grupo,
        estado: prestacion.pivot?.estado || 'activo',
        fecha_desde: prestacion.pivot?.fecha_desde,
        fecha_hasta: prestacion.pivot?.fecha_hasta,
        observaciones: prestacion.pivot?.observaciones || ''
    })
    showEditarModal.value = true
}

const actualizarAsociacion = () => {
    router.put(route('planes.prestaciones.update', [props.plan.id, prestacionEditando.value.id]), asociacionEditando, {
        onSuccess: () => {
            showEditarModal.value = false
            prestacionEditando.value = null
        }
    })
}

const toggleEstadoPrestacion = (prestacion) => {
    const nuevoEstado = (prestacion.pivot?.estado || 'activo') === 'activo' ? 'inactivo' : 'activo'

    router.put(route('planes.prestaciones.update', [props.plan.id, prestacion.id]), {
        ...prestacion.pivot,
        estado: nuevoEstado
    }, {
        preserveScroll: true
    })
}

const confirmarDesasociacion = (prestacion) => {
    prestacionToDesasociar.value = prestacion
    showDesasociarModal.value = true
}

const desasociarPrestacion = () => {
    router.delete(route('planes.prestaciones.destroy', [props.plan.id, prestacionToDesasociar.value.id]), {
        onSuccess: () => {
            showDesasociarModal.value = false
            prestacionToDesasociar.value = null
        }
    })
}

const copiarPrestaciones = () => {
    router.post(route('planes.copiar-prestaciones', props.plan.id), copiaDatos, {
        onSuccess: () => {
            showAccionesMasivas.value = false
        }
    })
}

const ajustarPrecios = () => {
    router.patch(route('planes.ajuste-masivo-precios', props.plan.id), ajusteDatos, {
        onSuccess: () => {
            showAccionesMasivas.value = false
        }
    })
}

const importarExcel = (event) => {
    const file = event.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('archivo', file)
    formData.append('sobrescribir_existentes', true)

    router.post(route('planes.import-excel', props.plan.id), formData, {
        onSuccess: () => {
            showAccionesMasivas.value = false
            event.target.value = ''
        }
    })
}

const activarSeleccionadas = () => {
    // Implementar lógica para activar prestaciones seleccionadas
    console.log('Activar seleccionadas:', prestacionesSeleccionadas.value)
}

const desactivarSeleccionadas = () => {
    // Implementar lógica para desactivar prestaciones seleccionadas
    console.log('Desactivar seleccionadas:', prestacionesSeleccionadas.value)
}

const desasociarSeleccionadas = () => {
    if (confirm(`¿Estás seguro de desasociar ${prestacionesSeleccionadas.value.length} prestaciones del plan?`)) {
        // Implementar lógica para desasociar prestaciones seleccionadas
        console.log('Desasociar seleccionadas:', prestacionesSeleccionadas.value)
    }
}

const editarPrecios = (prestacion) => {
    prestacionEditandoPrecios.value = prestacion
    Object.assign(preciosEditando, {
        valor_afiliado: prestacion.pivot?.valor_afiliado || 0,
        valor_particular: prestacion.pivot?.valor_particular || 0,
        cant_max_individual: prestacion.pivot?.cant_max_individual,
        cant_max_grupo: prestacion.pivot?.cant_max_grupo,
        estado: prestacion.pivot?.estado || 'activo'
    })
    showEditarPreciosModal.value = true
}

const actualizarPreciosYEstado = () => {
    router.put(route('planes.prestaciones.update', [props.plan.id, prestacionEditandoPrecios.value.id]), {
        valor_afiliado: preciosEditando.valor_afiliado,
        valor_particular: preciosEditando.valor_particular,
        cant_max_individual: preciosEditando.cant_max_individual,
        cant_max_grupo: preciosEditando.cant_max_grupo,
        estado: preciosEditando.estado,
        fecha_desde: prestacionEditandoPrecios.value.pivot?.fecha_desde,
        fecha_hasta: prestacionEditandoPrecios.value.pivot?.fecha_hasta,
        observaciones: prestacionEditandoPrecios.value.pivot?.observaciones
    }, {
        onSuccess: () => {
            showEditarPreciosModal.value = false
            prestacionEditandoPrecios.value = null
        }
    })
}
</script>
