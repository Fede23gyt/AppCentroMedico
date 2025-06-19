<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Información Personal -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.name }"
                        placeholder="Ingresa el nombre completo"
                    />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.email }"
                        placeholder="usuario@email.com"
                    />
                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono
                    </label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.phone }"
                        placeholder="123-456-7890"
                    />
                    <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">
                        {{ form.errors.phone }}
                    </div>
                </div>

                <!-- Estado (solo en edición) -->
                <div v-if="isEdit">
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                        Estado
                    </label>
                    <select
                        id="is_active"
                        v-model="form.is_active"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="true">Activo</option>
                        <option :value="false">Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- Dirección -->
            <div class="mt-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Dirección
                </label>
                <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.address }"
                    placeholder="Dirección completa..."
                ></textarea>
                <div v-if="form.errors.address" class="text-red-600 text-sm mt-1">
                    {{ form.errors.address }}
                </div>
            </div>
        </div>

        <!-- Configuración del Sistema -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración del Sistema</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="role"
                        v-model="form.role"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.role }"
                    >
                        <option value="">Selecciona un rol</option>
                        <option v-for="role in roles" :key="role.id" :value="role.name">
                            {{ role.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.role" class="text-red-600 text-sm mt-1">
                        {{ form.errors.role }}
                    </div>
                </div>

                <!-- Sucursal -->
                <div>
                    <label for="sucursal_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sucursal
                    </label>
                    <select
                        id="sucursal_id"
                        v-model="form.sucursal_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.sucursal_id }"
                    >
                        <option value="">Sin asignar</option>
                        <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                            {{ sucursal.nombre }}
                        </option>
                    </select>
                    <div v-if="form.errors.sucursal_id" class="text-red-600 text-sm mt-1">
                        {{ form.errors.sucursal_id }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Contraseña -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                {{ isEdit ? 'Cambiar Contraseña (Opcional)' : 'Contraseña' }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ isEdit ? 'Nueva contraseña' : 'Contraseña' }}
                        <span v-if="!isEdit" class="text-red-500">*</span>
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :required="!isEdit"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.password }"
                        placeholder="••••••••"
                    />
                    <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                        {{ form.errors.password }}
                    </div>
                    <div v-if="isEdit" class="text-gray-500 text-sm mt-1">
                        Déjalo vacío si no quieres cambiar la contraseña
                    </div>
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmar contraseña
                        <span v-if="!isEdit" class="text-red-500">*</span>
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        :required="!isEdit && form.password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.password_confirmation }"
                        placeholder="••••••••"
                    />
                    <div v-if="form.errors.password_confirmation" class="text-red-600 text-sm mt-1">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 pt-6">
            <Link
                :href="route('users.index')"
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
          {{ isEdit ? 'Actualizar Usuario' : 'Crear Usuario' }}
        </span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    form: Object,
    roles: Array,
    sucursales: Array,
    isEdit: {
        type: Boolean,
        default: false
    }
})

/*defineEmits(['submit'])*/

/*const submit = () => {
    // El submit se maneja en el componente padre
}
*/
const emit = defineEmits(['submit'])

const handleSubmit = () => {
    console.log('Formulario enviado desde UserForm') // Debug
    emit('submit')
}
</script>
