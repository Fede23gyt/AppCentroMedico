<template>
    <Head title="Editar Sucursal" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-4">
                    <Link
                        :href="route('sucursales.index')"
                        class="text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        ← Volver a sucursales
                    </Link>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Sucursal</h1>
                        <p class="text-gray-600">Modifica la información de {{ sucursal.nombre }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
            <span
                :class="[
                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                sucursal.is_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800'
              ]"
            >
              {{ sucursal.is_active ? 'Activa' : 'Inactiva' }}
            </span>
                    </div>
                </div>
            </div>

            <!-- Mensajes de error globales -->
            <div v-if="hasErrors" class="mb-6">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    <div class="font-medium">Por favor corrige los siguientes errores:</div>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        <li v-for="(error, key) in form.errors" :key="key">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Información actual -->
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-md mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Información actual:</strong>
                            Código: {{ sucursal.codigo }} |
                            Creada: {{ formatDate(sucursal.created_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <SucursalForm
                :form="form"
                :is-edit="true"
                :planes-disponibles="planesDisponibles"
                @submit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SucursalForm from '@/components/SucursalForm.vue'

const props = defineProps({
    sucursal: {
        type: Object,
        required: true
    },
    planesDisponibles: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    nombre: props.sucursal.nombre,
    codigo: props.sucursal.codigo,
    direccion: props.sucursal.direccion,
    telefono: props.sucursal.telefono || '',
    email: props.sucursal.email || '',
    is_active: props.sucursal.is_active,
    planes: props.sucursal.planes?.map(p => p.id) || [],
})

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0
})

const submit = () => {
    form.patch(route('sucursales.update', props.sucursal.id), {
        onSuccess: () => {
            // La redirección se maneja desde el controlador
        }
    })
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>
