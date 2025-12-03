<template>
    <Head title="Nuevo Prestador" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center space-x-3 mb-6">
                <Link
                    :href="route('prestadores.index')"
                    class="text-gray-600 hover:text-gray-800"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nuevo Prestador</h1>
                    <p class="text-gray-600">Completa los datos del profesional médico</p>
                </div>
            </div>

            <!-- Formulario -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <!-- Datos Personales -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 p-4 rounded-lg" style="background-color: #2D6660;">Datos Personales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Apellido <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.apellido"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.apellido }"
                                />
                                <div v-if="form.errors.apellido" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.apellido }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.nombre }"
                                />
                                <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.nombre }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    DNI <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.dni"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.dni }"
                                />
                                <div v-if="form.errors.dni" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.dni }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    CUIL <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.cuil"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.cuil }"
                                />
                                <div v-if="form.errors.cuil" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.cuil }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input
                                    v-model="form.mail"
                                    type="email"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.mail }"
                                />
                                <div v-if="form.errors.mail" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.mail }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input
                                    v-model="form.telefono"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.telefono }"
                                />
                                <div v-if="form.errors.telefono" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.telefono }}
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Domicilio</label>
                                <input
                                    v-model="form.domicilio"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.domicilio }"
                                />
                                <div v-if="form.errors.domicilio" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.domicilio }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha de Alta <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.fecha_alta"
                                    type="date"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.fecha_alta }"
                                />
                                <div v-if="form.errors.fecha_alta" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.fecha_alta }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <select
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

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                <textarea
                                    v-model="form.observaciones"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.observaciones }"
                                ></textarea>
                                <div v-if="form.errors.observaciones" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.observaciones }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('prestadores.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
                        >
                            {{ form.processing ? 'Guardando...' : 'Crear Prestador' }}
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

const props = defineProps({
    sucursales: Array,
})

const form = useForm({
    apellido: '',
    nombre: '',
    dni: '',
    cuil: '',
    mail: '',
    telefono: '',
    domicilio: '',
    estado: 'activo',
    fecha_alta: new Date().toISOString().split('T')[0],
    observaciones: '',
})

const submit = () => {
    form.post(route('prestadores.store'))
}
</script>
