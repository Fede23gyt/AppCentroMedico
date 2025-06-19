<template>
    <Head title="Iniciar Sesión" />

    <div class="bg-gradient-to-tr from-purple-400 via-blue-500 to-purple-600 min-h-screen">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Iniciar Sesión</h2>
                    <p class="mt-2 text-gray-600">Sistema de Gestión</p>
                </div>

                <!-- Mostrar errores -->
                <div v-if="hasErrors" class="mb-4">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <div v-for="(error, key) in $page.props.errors" :key="key" class="text-sm">
                            {{ error }}
                        </div>
                    </div>
                </div>

                <!-- Mostrar status -->
                <div v-if="status" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded text-sm">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="admin@admin.com"
                        />
                        <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="password"
                        />
                        <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="rounded text-blue-600"
                            />
                            <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 transition duration-200"
                    >
                        <span v-if="form.processing">Ingresando...</span>
                        <span v-else>Ingresar</span>
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>Credenciales de prueba:</p>
                    <p><strong>Email:</strong> admin@admin.com</p>
                    <p><strong>Password:</strong> password</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: 'admin@admin.com', // Prellenado para pruebas
    password: 'password',      // Prellenado para pruebas
    remember: false,
})

// Verificar si hay errores
const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0
})

const submit = () => {
    console.log('Enviando formulario:', form.data()) // Debug

    // Usar la URL directa en lugar de route()
    form.post('/login', {
        onFinish: () => {
            console.log('Formulario enviado') // Debug
            form.reset('password')
        },
        onSuccess: () => {
            console.log('Login exitoso') // Debug
        },
        onError: (errors) => {
            console.log('Errores:', errors) // Debug
        }
    })
}
</script>
