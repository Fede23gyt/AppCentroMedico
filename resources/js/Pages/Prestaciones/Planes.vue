<template>
    <Head :title="`Planes - ${prestacion.nombre}`" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <div class="flex items-center mb-2">
                        <Link
                            :href="route('prestaciones.index')"
                            class="text-blue-600 hover:text-blue-800 mr-2"
                        >
                            ← Prestaciones
                        </Link>
                        <span class="text-gray-400">/</span>
                        <span class="ml-2 text-gray-600">Gestionar Planes</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Planes</h1>
                    <p class="text-gray-600">Administra los planes asociados a esta prestación</p>
                </div>
                <button
                    @click="showAddModal = true"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                >
                    + Asociar Plan
                </button>
            </div>

            <!-- Información de la Prestación -->
            <div v-if="prestacionValida" class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                        <span class="text-lg font-mono bg-blue-100 text-blue-800 px-3 py-1 rounded font-semibold">
                            {{ prestacion.codigo }}
                        </span>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <span class="text-lg font-semibold text-gray-900">{{ prestacion.nombre }}</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rubro</label>
                        <span v-if="prestacion.rubro" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            {{ prestacion.rubro.nombre }}
                        </span>
                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
                            Sin rubro
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio General</label>
                        <span class="text-lg font-semibold text-green-600">{{ formatCurrency(prestacion.precio_general) }}</span>
                    </div>
                    <div v-if="prestacion.valor_ips">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor IPS</label>
                        <span class="text-lg font-semibold text-green-600">{{ formatCurrency(prestacion.valor_ips) }}</span>
                    </div>
                </div>
            </div>

            <!-- Loading state si no hay datos -->
            <div v-else class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="animate-pulse">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        <div>
                            <div class="h-4 bg-gray-200 rounded w-16 mb-2"></div>
                            <div class="h-8 bg-gray-200 rounded w-24"></div>
                        </div>
                        <div class="lg:col-span-2">
                            <div class="h-4 bg-gray-200 rounded w-16 mb-2"></div>
                            <div class="h-8 bg-gray-200 rounded w-full"></div>
                        </div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded w-16 mb-2"></div>
                            <div class="h-6 bg-gray-200 rounded w-20"></div>
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

            <!-- Tabla de Planes Asociados -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Planes Asociados</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ planesAsociados.length }} plan{{ planesAsociados.length !== 1 ? 'es' : '' }}
                        </span>
                    </div>
                </div>

                <div v-if="planesAsociados.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay planes asociados</h3>
                    <p class="text-gray-500 mb-4">Esta prestación no está incluida en ningún plan</p>
                    <button
                        @click="showAddModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                    >
                        Asociar Primer Plan
                    </button>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Precios
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Límites
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Vigencia
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="asociacion in planesAsociados"
                                :key="asociacion.plan_id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <!-- Columna Plan -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ asociacion.plan.nombre }}
                                            </div>
                                            <div class="text-sm text-gray-500 font-mono">
                                                {{ asociacion.plan.nombre_corto }}
                                            </div>
                                        </div>
                                    </div>
                                </td>


                                <!-- Columna Precios -->
                                <td class="px-6 py-4 text-center">
                                    <div class="space-y-1 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Afiliado:</span>
                                            <span class="font-semibold text-green-600">{{ formatCurrency(asociacion.valor_afiliado) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Particular:</span>
                                            <span class="font-semibold text-blue-600">{{ formatCurrency(asociacion.valor_particular) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna Límites -->
                                <td class="px-6 py-4 text-center">
                                    <div class="space-y-1 text-sm">
                                        <div>
                                            <span class="text-gray-600">Individual: </span>
                                            <span class="font-medium">{{ asociacion.cant_max_individual || 'Sin límite' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Grupo: </span>
                                            <span class="font-medium">{{ asociacion.cant_max_grupo || 'Sin límite' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna Vigencia -->
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm space-y-1">
                                        <div>{{ formatDate(asociacion.fecha_desde) }}</div>
                                        <div v-if="asociacion.fecha_hasta" class="text-red-600">
                                            hasta {{ formatDate(asociacion.fecha_hasta) }}
                                        </div>
                                        <div v-else class="text-green-600 font-medium">Sin vencimiento</div>
                                    </div>
                                </td>

                                <!-- Columna Estado -->
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getEstadoClass(asociacion.estado)
                                        ]"
                                    >
                                        {{ capitalizeFirst(asociacion.estado) }}
                                    </span>
                                </td>

                                <!-- Columna Acciones -->
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Editar -->
                                        <button
                                            @click="editarAsociacion(asociacion)"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="Editar asociación"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        <!-- Duplicar -->
                                        <button
                                            @click="duplicarAsociacion(asociacion)"
                                            class="text-green-600 hover:text-green-900"
                                            title="Duplicar configuración"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>

                                        <!-- Eliminar -->
                                        <button
                                            @click="confirmarDesasociar(asociacion)"
                                            class="text-red-600 hover:text-red-900"
                                            title="Desasociar plan"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Agregar Plan -->
            <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showAddModal = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6" @click.stop>
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Asociar Plan a Prestación</h3>
                            <p class="mt-1 text-sm text-gray-500">Configure los precios y límites para esta prestación en el plan seleccionado.</p>
                        </div>

                        <form @submit.prevent="asociarPlan" class="space-y-4">
                            <!-- Selección de Plan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                                <select
                                    v-model="formAsociar.plan_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Seleccione un plan</option>
                                    <option v-for="plan in planesDisponibles" :key="plan.id" :value="plan.id">
                                        {{ plan.nombre }} ({{ plan.nombre_corto }})
                                    </option>
                                </select>
                            </div>

                            <!-- Precios -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Afiliado</label>
                                    <input
                                        v-model="formAsociar.valor_afiliado"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="0.00"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Particular</label>
                                    <input
                                        v-model="formAsociar.valor_particular"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <!-- Límites -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Límite Individual</label>
                                    <input
                                        v-model="formAsociar.cant_max_individual"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Sin límite"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Límite Grupo</label>
                                    <input
                                        v-model="formAsociar.cant_max_grupo"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Sin límite"
                                    />
                                </div>
                            </div>

                            <!-- Vigencia -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vigencia Desde</label>
                                    <input
                                        v-model="formAsociar.fecha_desde"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vigencia Hasta</label>
                                    <input
                                        v-model="formAsociar.fecha_hasta"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <!-- Estado y Observaciones -->
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <select
                                        v-model="formAsociar.estado"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                        <option value="suspendido">Suspendido</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                    <textarea
                                        v-model="formAsociar.observaciones"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Observaciones adicionales..."
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end space-x-3 pt-4">
                                <button
                                    @click="showAddModal = false"
                                    type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none"
                                    :disabled="procesando"
                                >
                                    {{ procesando ? 'Procesando...' : 'Asociar Plan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Asociación -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showEditModal = false">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6" @click.stop>
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Editar Asociación</h3>
                            <p class="mt-1 text-sm text-gray-500">Modifique los precios y configuración para el plan "{{ asociacionEditando?.plan?.nombre }}".</p>
                        </div>

                        <form @submit.prevent="actualizarAsociacion" class="space-y-4">
                            <!-- Precios -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Afiliado</label>
                                    <input
                                        v-model="formEditar.valor_afiliado"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Particular</label>
                                    <input
                                        v-model="formEditar.valor_particular"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <!-- Límites -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Límite Individual</label>
                                    <input
                                        v-model="formEditar.cant_max_individual"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Límite Grupo</label>
                                    <input
                                        v-model="formEditar.cant_max_grupo"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <!-- Vigencia -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vigencia Desde</label>
                                    <input
                                        v-model="formEditar.fecha_desde"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vigencia Hasta</label>
                                    <input
                                        v-model="formEditar.fecha_hasta"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <!-- Estado y Observaciones -->
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <select
                                        v-model="formEditar.estado"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                        <option value="suspendido">Suspendido</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                    <textarea
                                        v-model="formEditar.observaciones"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end space-x-3 pt-4">
                                <button
                                    @click="showEditModal = false"
                                    type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none"
                                    :disabled="procesando"
                                >
                                    {{ procesando ? 'Guardando...' : 'Guardar Cambios' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Confirmar Desasociación -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showDeleteModal = false">
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Desasociar Plan</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que quieres desasociar el plan <strong>{{ asociacionAEliminar?.plan?.nombre }}</strong> de esta prestación?
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="desasociarPlan"
                                type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                                :disabled="procesando"
                            >
                                {{ procesando ? 'Eliminando...' : 'Desasociar' }}
                            </button>
                            <button
                                @click="showDeleteModal = false"
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
import { ref, reactive, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    prestacion: Object,
    planesDisponibles: Array,
})

// Estados reactivos
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const procesando = ref(false)
const asociacionEditando = ref(null)
const asociacionAEliminar = ref(null)

// Formularios
const formAsociar = reactive({
    plan_id: '',
    valor_afiliado: '',
    valor_particular: '',
    cant_max_individual: '',
    cant_max_grupo: '',
    fecha_desde: new Date().toISOString().split('T')[0],
    fecha_hasta: '',
    estado: 'activo',
    observaciones: ''
})

const formEditar = reactive({
    valor_afiliado: '',
    valor_particular: '',
    cant_max_individual: '',
    cant_max_grupo: '',
    fecha_desde: '',
    fecha_hasta: '',
    estado: '',
    observaciones: ''
})

// Computed
const planesAsociados = computed(() => {
    if (!props.prestacion || !props.prestacion.planes) {
        return []
    }
    return props.prestacion.planes
})

const prestacionValida = computed(() => {
    return props.prestacion &&
           props.prestacion.codigo &&
           props.prestacion.nombre &&
           props.prestacion.precio_general !== undefined
})

// Utilidades
const formatCurrency = (amount) => {
    if (amount === undefined || amount === null || isNaN(amount)) {
        return '$0,00'
    }
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(amount)
}

const formatDate = (date) => {
    if (!date) return '-'
    try {
        return new Date(date).toLocaleDateString('es-AR')
    } catch (error) {
        return date
    }
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
    if (!str || typeof str !== 'string') {
        return 'Sin estado'
    }
    return str.charAt(0).toUpperCase() + str.slice(1)
}

// Métodos
const asociarPlan = () => {
    procesando.value = true

    // Mapear los nombres de campos a los esperados por el backend
    const datos = {
        plan_id: formAsociar.plan_id,
        valor_afiliado: formAsociar.valor_afiliado,
        valor_particular: formAsociar.valor_particular,
        cant_max_individual: formAsociar.cant_max_individual,
        cant_max_grupo: formAsociar.cant_max_grupo,
        fecha_desde: formAsociar.fecha_desde,
        fecha_hasta: formAsociar.fecha_hasta,
        estado: formAsociar.estado,
        observaciones: formAsociar.observaciones
    }

    router.post(route('prestaciones.asociar-plan', props.prestacion.id), datos, {
        onSuccess: () => {
            showAddModal.value = false
            resetFormAsociar()
        },
        onError: (errors) => {
            console.error('Error al asociar plan:', errors)
        },
        onFinish: () => {
            procesando.value = false
        }
    })
}

const editarAsociacion = (asociacion) => {
    console.log('Editando asociación:', asociacion)

    asociacionEditando.value = asociacion

    // Llenar formulario con datos actuales
    formEditar.valor_afiliado = asociacion.valor_afiliado
    formEditar.valor_particular = asociacion.valor_particular
    formEditar.cant_max_individual = asociacion.cant_max_individual || ''
    formEditar.cant_max_grupo = asociacion.cant_max_grupo || ''
    formEditar.fecha_desde = asociacion.fecha_desde
    formEditar.fecha_hasta = asociacion.fecha_hasta || ''
    formEditar.estado = asociacion.estado
    formEditar.observaciones = asociacion.observaciones || ''

    showEditModal.value = true
}

const actualizarAsociacion = () => {
    procesando.value = true

    // Debug: verificar la estructura de datos
    console.log('asociacionEditando:', asociacionEditando.value)

    // Mapear los nombres de campos a los esperados por el backend
    const datos = {
        valor_afiliado: formEditar.valor_afiliado,
        valor_particular: formEditar.valor_particular,
        cant_max_individual: formEditar.cant_max_individual,
        cant_max_grupo: formEditar.cant_max_grupo,
        fecha_desde: formEditar.fecha_desde,
        fecha_hasta: formEditar.fecha_hasta,
        estado: formEditar.estado,
        observaciones: formEditar.observaciones
    }

    // Obtener el ID del plan de manera más robusta
    let planId = null
    if (asociacionEditando.value.plan_id) {
        planId = asociacionEditando.value.plan_id
    } else if (asociacionEditando.value.plan && asociacionEditando.value.plan.id) {
        planId = asociacionEditando.value.plan.id
    } else if (asociacionEditando.value.id) {
        planId = asociacionEditando.value.id
    }

    console.log('Plan ID obtenido:', planId)

    if (!planId) {
        console.error('No se pudo obtener el ID del plan')
        procesando.value = false
        return
    }

    router.put(route('prestaciones.actualizar-plan', [props.prestacion.id, planId]), datos, {
        onSuccess: () => {
            showEditModal.value = false
            asociacionEditando.value = null
        },
        onError: (errors) => {
            console.error('Error al actualizar asociación:', errors)
        },
        onFinish: () => {
            procesando.value = false
        }
    })
}

const duplicarAsociacion = (asociacion) => {
    // Prellenar formulario con datos de la asociación existente
    formAsociar.plan_id = ''
    formAsociar.valor_afiliado = asociacion.valor_afiliado
    formAsociar.valor_particular = asociacion.valor_particular
    formAsociar.cant_max_individual = asociacion.cant_max_individual || ''
    formAsociar.cant_max_grupo = asociacion.cant_max_grupo || ''
    formAsociar.fecha_desde = asociacion.fecha_desde
    formAsociar.fecha_hasta = asociacion.fecha_hasta || ''
    formAsociar.estado = asociacion.estado
    formAsociar.observaciones = (asociacion.observaciones || '') + ' (Copia)'

    showAddModal.value = true
}

const confirmarDesasociar = (asociacion) => {
    console.log('Confirmando desasociar:', asociacion)
    asociacionAEliminar.value = asociacion
    showDeleteModal.value = true
}

const desasociarPlan = () => {
    procesando.value = true

    // Obtener el ID del plan de manera más robusta
    let planId = null
    if (asociacionAEliminar.value.plan_id) {
        planId = asociacionAEliminar.value.plan_id
    } else if (asociacionAEliminar.value.plan && asociacionAEliminar.value.plan.id) {
        planId = asociacionAEliminar.value.plan.id
    } else if (asociacionAEliminar.value.id) {
        planId = asociacionAEliminar.value.id
    }

    console.log('Plan ID para desasociar:', planId)

    if (!planId) {
        console.error('No se pudo obtener el ID del plan para desasociar')
        procesando.value = false
        return
    }

    router.delete(route('prestaciones.desasociar-plan', [props.prestacion.id, planId]), {
        onSuccess: () => {
            showDeleteModal.value = false
            asociacionAEliminar.value = null
        },
        onError: (errors) => {
            console.error('Error al desasociar plan:', errors)
        },
        onFinish: () => {
            procesando.value = false
        }
    })
}

const resetFormAsociar = () => {
    formAsociar.plan_id = ''
    formAsociar.valor_afiliado = ''
    formAsociar.valor_particular = ''
    formAsociar.cant_max_individual = ''
    formAsociar.cant_max_grupo = ''
    formAsociar.fecha_desde = new Date().toISOString().split('T')[0]
    formAsociar.fecha_hasta = ''
    formAsociar.estado = 'activo'
    formAsociar.observaciones = ''
}
</script>

<style scoped>
/* Estilos para mejorar la apariencia de los modales */
.modal-overlay {
    backdrop-filter: blur(2px);
}

/* Transiciones suaves para los modales */
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
    opacity: 0;
}

/* Mejorar el aspecto de las tablas en dispositivos móviles */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .table-responsive td {
        padding: 0.5rem;
    }
}
</style>
