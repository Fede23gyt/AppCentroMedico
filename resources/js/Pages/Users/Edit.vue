<template>
    <Head title="Editar Usuario" />

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
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Usuario</h1>
                        <p class="text-gray-600">Modifica la información de {{ user.name }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
            <span
                :class="[
                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                user.is_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800'
              ]"
            >
              {{ user.is_active ? 'Activo' : 'Inactivo' }}
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
                            Rol: {{ user.roles[0]?.name || 'Sin rol' }} |
                            Sucursal: {{ user.sucursal?.nombre || 'Sin asignar' }} |
                            Creado: {{ formatDate(user.created_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <UserForm
                :form="form"
                :roles="roles"
                :sucursales="sucursales"
                :is-edit="true"
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
    user: Object,
    roles: Array,
    sucursales: Array,
})

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.roles[0]?.name || '',
    sucursal_id: props.user.sucursal_id || '',
    phone: props.user.phone || '',
    address: props.user.address || '',
    is_active: props.user.is_active,
})

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0
})

const submit = () => {
    form.patch(route('users.update', props.user.id), {
        onSuccess: () => {
            // La redirección se maneja desde el controlador
        },
        onError: () => {
            // Los errores se muestran automáticamente
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
