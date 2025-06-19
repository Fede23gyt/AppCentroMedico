<template>
    <Head title="Crear Usuario" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-4">
                    <Link
                        :href="route('users.index')"
                        class="text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        ← Volver a usuarios
                    </Link>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Crear Nuevo Usuario</h1>
                <p class="text-gray-600">Completa la información para crear un nuevo usuario del sistema</p>
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
            <UserForm
                :form="form"
                :roles="roles"
                :sucursales="sucursales"
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
import UserForm from '@/components/UserForm.vue'

const props = defineProps({
    roles: Array,
    sucursales: Array,
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    sucursal_id: '',
    phone: '',
    address: '',
})

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0
})

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            console.log('Éxito!') // Debug
            // La redirección se maneja desde el controlador
        },
        onError: () => {
            // Los errores se muestran automáticamente
            console.log('Errores:', errors) // Debug
        }
    })
}
</script>
