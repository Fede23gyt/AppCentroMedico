<template>
    <Head title="Nueva Rendición" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h1 class="text-base font-semibold text-gray-900">Nueva Rendición</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Crea una nueva rendición de prestador</p>
                </div>
                <Link
                    :href="route('rendiciones.index')"
                    class="text-gray-600 hover:text-gray-900 font-medium text-sm"
                >
                    ← Volver
                </Link>
            </div>

            <!-- Formulario -->
            <div class="bg-white rounded-md shadow-sm border border-gray-100 p-3">
                <form @submit.prevent="submit">
                    <!-- Prestador -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Prestador <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.prestador_id"
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            :class="{ 'border-red-500': form.errors.prestador_id }"
                            required
                        >
                            <option value="">Seleccione un prestador</option>
                            <option v-for="prestador in prestadores" :key="prestador.id" :value="prestador.id">
                                {{ prestador.apellido }}, {{ prestador.nombre }}
                            </option>
                        </select>
                        <p v-if="form.errors.prestador_id" class="mt-1 text-xs text-red-600">
                            {{ form.errors.prestador_id }}
                        </p>
                    </div>

                    <!-- Observación -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Observación
                        </label>
                        <textarea
                            v-model="form.observacion"
                            rows="3"
                            class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            :class="{ 'border-red-500': form.errors.observacion }"
                            placeholder="Observaciones adicionales (opcional)"
                        ></textarea>
                        <p v-if="form.errors.observacion" class="mt-1 text-xs text-red-600">
                            {{ form.errors.observacion }}
                        </p>
                    </div>

                    <!-- Mensaje de error general -->
                    <div v-if="form.errors.error" class="mb-2 p-2 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-xs text-red-800">{{ form.errors.error }}</p>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-2">
                        <Link
                            :href="route('rendiciones.index')"
                            class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors text-sm"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.prestador_id"
                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm"
                        >
                            <span v-if="form.processing">Creando...</span>
                            <span v-else>Crear Rendición</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Información adicional -->
            <div class="mt-2 bg-blue-50 border border-blue-200 rounded-md p-2">
                <h3 class="text-xs font-medium text-blue-800 mb-1">Información</h3>
                <ul class="text-xs text-blue-700 space-y-0.5">
                    <li>• La rendición se creará en estado "Abierta"</li>
                    <li>• Podrás agregar órdenes una vez creada la rendición</li>
                    <li>• Solo se pueden agregar órdenes pagadas</li>
                    <li>• La rendición se puede cerrar cuando hayas terminado de agregar todas las órdenes</li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    prestadores: Array,
    sucursalId: Number,
});

const form = useForm({
    prestador_id: '',
    observacion: '',
});

const submit = () => {
    form.post(route('rendiciones.store'), {
        preserveScroll: true,
    });
};
</script>
