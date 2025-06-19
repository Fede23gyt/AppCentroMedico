<template>
    <Head title="Crear Sucursal" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-4">
                    <Link
                        :href="route('sucursales.index')"
                        class="text-gray-600 hover:text-gray-800 transition-colors">
                        ← Volver a sucursales
                    </Link>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Crear Nueva Sucursal</h1>
                <p class="text-gray-600">Completa la información para crear una nueva sucursal</p>
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

            <!-- Formulario -->
            <SucursalForm
                :form="form"
                :is-edit="false"
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

const form = useForm({
    nombre: '',
    codigo: '',
    direccion: '',
    telefono: '',
    email: '',
})

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0
})

const submit = () => {
    form.post(route('sucursales.store'), {
        onSuccess: () => {
            // La redirección se maneja desde el controlador
        }
    })
}
</script>
