<template>
    <Head title="Crear Nuevo Plan" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <Link
                            :href="route('planes.index')"
                            class="text-gray-600 hover:text-gray-800"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900">Crear Nuevo Plan</h1>
                    </div>
                    <p class="text-gray-600">Completa los datos del nuevo plan de salud</p>
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

            <!-- Formulario -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Plan <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                required
                                autofocus
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.nombre }"
                                placeholder="Ej: Plan Básico Familiar"
                            />
                            <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">
                                {{ form.errors.nombre }}
                            </div>
                        </div>

                        <!-- Nombre Corto -->
                        <div>
                            <label for="nombre_corto" class="block text-sm font-medium text-gray-700 mb-2">
                                Código Corto <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="nombre_corto"
                                v-model="form.nombre_corto"
                                type="text"
                                required
                                maxlength="20"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                                :class="{ 'border-red-500': form.errors.nombre_corto }"
                                placeholder="Ej: BASICO-FAM"
                            />
                            <div v-if="form.errors.nombre_corto" class="text-red-500 text-sm mt-1">
                                {{ form.errors.nombre_corto }}
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Máximo 20 caracteres, debe ser único</p>
                        </div>

                        <!-- Vigencia Desde -->
                        <div>
                            <label for="vigencia_desde" class="block text-sm font-medium text-gray-700 mb-2">
                                Vigencia Desde <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="vigencia_desde"
                                v-model="form.vigencia_desde"
                                type="date"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.vigencia_desde }"
                            />
                            <div v-if="form.errors.vigencia_desde" class="text-red-500 text-sm mt-1">
                                {{ form.errors.vigencia_desde }}
                            </div>
                        </div>

                        <!-- Vigencia Hasta -->
                        <div>
                            <label for="vigencia_hasta" class="block text-sm font-medium text-gray-700 mb-2">
                                Vigencia Hasta
                            </label>
                            <input
                                id="vigencia_hasta"
                                v-model="form.vigencia_hasta"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.vigencia_hasta }"
                            />
                            <div v-if="form.errors.vigencia_hasta" class="text-red-500 text-sm mt-1">
                                {{ form.errors.vigencia_hasta }}
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Dejar vacío para vigencia indefinida</p>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="estado"
                                v-model="form.estado"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.estado }"
                            >
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="suspendido">Suspendido</option>
                            </select>
                            <div v-if="form.errors.estado" class="text-red-500 text-sm mt-1">
                                {{ form.errors.estado }}
                            </div>
                        </div>

                        <!-- Porcentaje Salud -->
                        <div>
                            <label for="porc_salud" class="block text-sm font-medium text-gray-700 mb-2">
                                % Salud
                            </label>
                            <input
                                id="porc_salud"
                                v-model="form.porc_salud"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.porc_salud }"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.porc_salud" class="text-red-500 text-sm mt-1">
                                {{ form.errors.porc_salud }}
                            </div>
                        </div>

                        <!-- Porcentaje Odontología -->
                        <div>
                            <label for="porc_odo" class="block text-sm font-medium text-gray-700 mb-2">
                                % Odontología
                            </label>
                            <input
                                id="porc_odo"
                                v-model="form.porc_odo"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.porc_odo }"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.porc_odo" class="text-red-500 text-sm mt-1">
                                {{ form.errors.porc_odo }}
                            </div>
                        </div>

                        <!-- Porcentaje Orden -->
                        <div>
                            <label for="porc_ord" class="block text-sm font-medium text-gray-700 mb-2">
                                % Orden
                            </label>
                            <input
                                id="porc_ord"
                                v-model="form.porc_ord"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.porc_ord }"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.porc_ord" class="text-red-500 text-sm mt-1">
                                {{ form.errors.porc_ord }}
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mt-6">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea
                            id="descripcion"
                            v-model="form.descripcion"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.descripcion }"
                            placeholder="Descripción opcional del plan, cobertura, beneficios, etc."
                        ></textarea>
                        <div v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">
                            {{ form.errors.descripcion }}
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <Link
                            :href="route('planes.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
                        >
                            <span v-if="form.processing">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creando...
                            </span>
                            <span v-else>
                                Crear Plan
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
    nombre: '',
    nombre_corto: '',
    vigencia_desde: '',
    vigencia_hasta: '',
    estado: 'activo',
    descripcion: '',
    porc_salud: null,
    porc_odo: null,
    porc_ord: null,
})

const submit = () => {
    form.post(route('planes.store'), {
        onSuccess: () => {
            // Redirigir será manejado por el controlador
        }
    })
}
</script>
