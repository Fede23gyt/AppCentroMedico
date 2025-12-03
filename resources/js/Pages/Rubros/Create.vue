<template>
    <Head title="Nuevo Rubro" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Crear Nuevo Rubro</h1>
                    <p class="dark:text-gray-600">Agrega una nueva categoría de prestaciones médicas</p>
                </div>
                <Link
                    :href="route('rubros.index')"
                    class="text-gray-600 hover:text-gray-900 font-medium"
                >
                    ← Volver al Listado
                </Link>
            </div>

            <!-- Formulario -->
            <div class="bg-white shadow rounded-lg">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre del Rubro <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.nombre"
                            type="text"
                            maxlength="100"
                            required
                            autofocus
                            placeholder="Ej: Consultas, Estudios, Cirugías..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.nombre }"
                        />
                        <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">
                            {{ form.errors.nombre }}
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea
                            v-model="form.descripcion"
                            rows="3"
                            placeholder="Describe brevemente este rubro..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical"
                            :class="{ 'border-red-500': form.errors.descripcion }"
                        ></textarea>
                        <div v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">
                            {{ form.errors.descripcion }}
                        </div>
                    </div>

                    <!-- Porcentaje de Descuento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Porcentaje de Descuento<span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 max-w-xs">
                            <input
                                v-model.number="form.porc"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                required
                                placeholder="0.00"
                                class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                                :class="{ 'border-red-500': form.errors.porc }"
                            />
                            <span class="text-gray-600 font-medium">%</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Porcentaje de descuento aplicado sobre el valor IPS (0-100)</p>
                        <div v-if="form.errors.porc" class="text-red-500 text-sm mt-1">
                            {{ form.errors.porc }}
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.estado"
                                    type="radio"
                                    value="activo"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">Activo</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.estado"
                                    type="radio"
                                    value="inactivo"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">Inactivo</span>
                            </label>
                        </div>
                        <div v-if="form.errors.estado" class="text-red-500 text-sm mt-1">
                            {{ form.errors.estado }}
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <Link
                            :href="route('rubros.index')"
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Creando...</span>
                            <span v-else>Crear Rubro</span>
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
    descripcion: '',
    porc: 0,
    estado: 'activo'
})

const submit = () => {
    form.post(route('rubros.store'), {
        onSuccess: () => {
            form.reset()
        }
    })
}
</script>
