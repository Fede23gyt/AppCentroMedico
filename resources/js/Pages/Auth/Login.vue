<template>
    <Head title="Iniciar Sesión" />

    <div class="min-h-screen flex">
        <!-- Panel Izquierdo - Verde/Turquesa -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-teal-600 to-teal-700 items-center justify-center p-12">
            <div class="text-center text-white">
                <!-- Logo - El usuario debe agregarlo después -->
                <div class="mb-8">
                    <img
                        src="/images/logo.png"
                        alt="Logo"
                        class="w-32 h-32 mx-auto object-contain"
                        @error="$event.target.style.display='none'"
                    />
                </div>

                <div class="space-y-4">
                    <h1 class="text-4xl font-bold">SEO 2.0</h1>
                    <p class="text-xl font-medium">Sistema de Emisión de Órdenes</p>
                </div>
            </div>
        </div>

        <!-- Panel Derecho - Blanco con Formulario -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 bg-gray-50">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
                <!-- Logo Superior (versión pequeña para el formulario) -->
                <div class="text-center mb-6">
                    <img
                        src="/images/logo-small.png"
                        alt="Logo"
                        class="w-16 h-16 mx-auto mb-4 object-contain"
                        @error="$event.target.style.display='none'"
                    />
                    <h2 class="text-2xl font-bold text-gray-800">SEO 2.0</h2>
                    <p class="text-sm text-teal-600 font-semibold mt-1">SISTEMA DE EMISIÓN DE ÓRDENES</p>
                </div>

                <!-- Mostrar errores -->
                <div v-if="hasErrors" class="mb-4">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <div v-for="(error, key) in $page.props.errors" :key="key">
                            {{ error }}
                        </div>
                    </div>
                </div>

                <!-- Mostrar status -->
                <div v-if="status" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Campo Usuario -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Usuario
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="text"
                                required
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="fsuppa"
                            />
                        </div>
                        <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="w-full pl-10 pr-12 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="••••••••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <svg v-if="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Recordar sesión -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Recordar mi sesión
                        </label>
                    </div>

                    <!-- Botón Iniciar Sesión -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-teal-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
                    >
                        <span v-if="form.processing">Ingresando...</span>
                        <span v-else>Iniciar Sesión</span>
                    </button>
                </form>

                <!-- Enlaces inferiores -->
                <div class="mt-6 space-y-2 text-center text-sm">
                    <a href="/" class="text-teal-600 hover:text-teal-700 font-medium flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver al inicio
                    </a>
                    <div class="text-gray-500">
                        <p>¿Necesita ayuda?</p>
                        <a href="#" class="text-teal-600 hover:text-teal-700 font-medium">
                            Contacte a soporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const showPassword = ref(false)

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
