<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Información General -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-white mb-4 p-4 rounded-lg" style="background-color: #2D6660;">Información General</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la sucursal <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="nombre"
                        v-model="form.nombre"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.nombre }"
                        placeholder="Ej: Sucursal Centro"
                    />
                    <div v-if="form.errors.nombre" class="text-red-600 text-sm mt-1">
                        {{ form.errors.nombre }}
                    </div>
                </div>

                <!-- Código -->
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="codigo"
                        v-model="form.codigo"
                        type="text"
                        required
                        maxlength="10"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.codigo }"
                        placeholder="Ej: SUC001"
                        style="text-transform: uppercase"
                        @input="form.codigo = $event.target.value.toUpperCase()"
                    />
                    <div v-if="form.errors.codigo" class="text-red-600 text-sm mt-1">
                        {{ form.errors.codigo }}
                    </div>
                    <div class="text-gray-500 text-sm mt-1">
                        Máximo 10 caracteres, se convertirá a mayúsculas
                    </div>
                </div>

                <!-- Estado (solo en edición) -->
                <div v-if="isEdit" class="md:col-span-2">
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                        Estado
                    </label>
                    <select
                        id="is_active"
                        v-model="form.is_active"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="true">Activa</option>
                        <option :value="false">Inactiva</option>
                    </select>
                    <div class="text-gray-500 text-sm mt-1">
                        Las sucursales inactivas no aparecerán en los formularios de asignación
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-white mb-4 p-4 rounded-lg" style="background-color: #2D6660;">Información de Contacto</h3>

            <div class="space-y-6">
                <!-- Dirección -->
                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="direccion"
                        v-model="form.direccion"
                        rows="3"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.direccion }"
                        placeholder="Dirección completa de la sucursal..."
                    ></textarea>
                    <div v-if="form.errors.direccion" class="text-red-600 text-sm mt-1">
                        {{ form.errors.direccion }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                            Teléfono
                        </label>
                        <input
                            id="telefono"
                            v-model="form.telefono"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.telefono }"
                            placeholder="123-456-7890"
                        />
                        <div v-if="form.errors.telefono" class="text-red-600 text-sm mt-1">
                            {{ form.errors.telefono }}
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="sucursal@empresa.com"
                        />
                        <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Planes Asignados (solo en edición) -->
        <div v-if="isEdit && planesDisponibles" class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-white mb-4 p-4 rounded-lg" style="background-color: #2D6660;">Planes de Salud Asignados</h3>

            <div class="border border-gray-300 rounded-md p-4 bg-gray-50 max-h-64 overflow-y-auto">
                <div v-if="planesDisponibles && planesDisponibles.length > 0" class="space-y-2">
                    <label
                        v-for="plan in planesDisponibles"
                        :key="plan.id"
                        class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :value="plan.id"
                            v-model="form.planes"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <span class="text-sm text-gray-700">
                            <span class="font-medium">{{ plan.nombre }}</span>
                            <span class="text-gray-500 ml-2 font-mono">({{ plan.nombre_corto }})</span>
                        </span>
                    </label>
                </div>
                <div v-else class="text-sm text-gray-500 text-center py-4">
                    No hay planes disponibles
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                Selecciona los planes que estarán disponibles en esta sucursal
            </p>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 pt-6">
            <Link
                :href="route('sucursales.index')"
                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
                Cancelar
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
        <span v-if="form.processing">
          {{ isEdit ? 'Actualizando...' : 'Creando...' }}
        </span>
                <span v-else>
          {{ isEdit ? 'Actualizar Sucursal' : 'Crear Sucursal' }}
        </span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    form: Object,
    isEdit: {
        type: Boolean,
        default: false
    },
    planesDisponibles: {
        type: Array,
        default: () => []
    }
})

/*const emit = defineEmits(['submit'])

const handleSubmit = () => {
    emit('submit')
}
*/
const emit = defineEmits(['submit'])

const handleSubmit = () => {
    console.log('Formulario enviado desde SucursalesForm') // Debug
    emit('submit')
}

</script>
