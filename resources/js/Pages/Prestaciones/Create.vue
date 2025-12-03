<!-- resources/js/Pages/Prestaciones/Create.vue -->
<template>
    <AuthenticatedLayout>
        <Head title="Nueva Prestación">
            <meta name="csrf-token" :content="$page.props.csrf_token" />
        </Head>

        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiMedicalBag"
                title="Nueva Prestación"
                main
            >
                <BaseButton
                    :route-name="route('prestaciones.index')"
                    :icon="mdiArrowLeft"
                    label="Volver"
                    color="contrast"
                    rounded-full
                    small
                />
            </SectionTitleLineWithButton>

            <!-- Loading State -->
            <div v-if="form.processing" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>

            <CardBox v-else form @submit.prevent="submit">
                <!-- Información Básica -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center p-4 rounded-lg" style="background-color: #2D6660;">
                        <Icon :path="mdiInformation" class="w-5 h-5 mr-2" />
                        Información Básica
                    </h3>

                    <div class="space-y-6">
                        <!-- Código y Estado en la misma fila -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Código de Prestación -->
                            <div class="flex items-center gap-4">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0">
                                    Código <span class="text-red-500">*</span>
                                </label>
                                <div class="flex-1 max-w-xs">
                                    <input
                                        v-model="form.codigo"
                                        type="text"
                                        maxlength="6"
                                        pattern="[0-9]{6}"
                                        placeholder="000001"
                                        required
                                        autofocus
                                        class="w-24 px-3 py-2 text-center font-mono text-lg border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': form.errors.codigo }"
                                        @input="onCodigoChange"
                                        @blur="checkCodigoExists"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">6 dígitos numéricos</p>
                                    <div v-if="form.errors.codigo" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.codigo }}
                                    </div>
                                    <div v-if="codigoStatus === 'checking'" class="text-blue-500 text-sm mt-1">
                                        Verificando disponibilidad...
                                    </div>
                                    <div v-if="codigoStatus === 'exists'" class="text-red-500 text-sm mt-1">
                                        Este código ya está en uso
                                    </div>
                                    <div v-if="codigoStatus === 'available'" class="text-green-500 text-sm mt-1">
                                        Código disponible
                                    </div>
                                </div>
                            </div>

                            <!-- Estado como Checkbox -->
                            <div class="flex items-center gap-4">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0">
                                    Estado
                                </label>
                                <div class="flex-1 max-w-xs">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            v-model="activo"
                                            type="checkbox"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            Prestación Activa
                                        </span>
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ activo ? 'La prestación estará disponible' : 'La prestación estará inactiva' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Nombre de la Prestación -->
                        <div class="flex items-start gap-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1 max-w-md">
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    placeholder="Ej: Consulta médica general"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': form.errors.nombre }"
                                />
                                <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.nombre }}
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="flex items-start gap-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                                Descripción
                            </label>
                            <div class="flex-1 max-w-md">
                                <textarea
                                    v-model="form.descripcion"
                                    rows="3"
                                    placeholder="Describe en detalle qué incluye esta prestación..."
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-vertical"
                                    :class="{ 'border-red-500': form.errors.descripcion }"
                                ></textarea>
                                <div v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.descripcion }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Categorización -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center p-4 rounded-lg" style="background-color: #2D6660;">
                        <Icon :path="mdiTag" class="w-5 h-5 mr-2" />
                        Categorización
                    </h3>

                    <div class="flex items-start gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                            Rubro <span class="text-red-500">*</span>
                        </label>
                        <div class="flex-1 max-w-sm">
                            <select
                                v-model="form.rubro_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': form.errors.rubro_id }"
                            >
                                <option value="">Seleccione un rubro</option>
                                <option
                                    v-for="rubro in rubros"
                                    :key="rubro.id"
                                    :value="rubro.id"
                                >
                                    {{ rubro.nombre }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Selecciona la categoría correspondiente</p>
                            <div v-if="form.errors.rubro_id" class="text-red-500 text-sm mt-1">
                                {{ form.errors.rubro_id }}
                            </div>
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Precios -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center p-4 rounded-lg" style="background-color: #2D6660;">
                        <Icon :path="mdiCurrencyUsd" class="w-5 h-5 mr-2" />
                        Configuración de Precios
                    </h3>

                    <div class="space-y-6">
                      <!-- Valor IPS -->
                      <div class="flex items-start gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                          Valor IPS
                        </label>
                        <div class="flex-1 max-w-xs">
                          <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input
                              v-model="form.valor_ips"
                              type="number"
                              step="0.01"
                              min="0"
                              placeholder="0"
                              class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                              :class="{ 'border-red-500': form.errors.valor_ips }"
                            />
                          </div>
                          <p class="text-xs text-gray-500 mt-1">Valor para Instituto de Previsión Social</p>
                          <div v-if="form.errors.valor_ips" class="text-red-500 text-sm mt-1">
                            {{ form.errors.valor_ips }}
                          </div>
                        </div>
                      </div>

                      <!-- Porcentaje IPS -->
                        <div class="flex items-start gap-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                                Porcentaje IPS <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1 max-w-xs">
                                <div class="relative">
                                    <input
                                        v-model.number="form.porc_ips"
                                        type="number"
                                        step="0.01"
                                        min="-100"
                                        max="100"
                                        placeholder="0"
                                        required
                                        class="w-full pr-8 pl-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': form.errors.porc_ips }"
                                    />
                                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Porcentaje aplicado sobre el valor IPS (puede ser negativo)</p>
                                <div v-if="form.errors.porc_ips" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.porc_ips }}
                                </div>
                            </div>
                        </div>

                      <!-- Valor Referencia (Calculado) -->
                      <div class="flex items-start gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                          Valor Referencia
                        </label>
                        <div class="flex-1 max-w-xs">
                          <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input
                              :value="valorReferenciaCalculado"
                              type="text"
                              readonly
                              class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-not-allowed"
                            />
                          </div>
                          <p class="text-xs text-gray-500 mt-1">
                            Calculado automáticamente: Valor IPS {{ form.porc_ips >= 0 ? '+' : '' }}{{ form.porc_ips || 0 }}%
                          </p>
                        </div>
                      </div>



                        <!-- Preview de precios -->
                        <div v-if="form.valor_ips" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Vista previa de cálculo:</h4>
                            <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                <div>Valor IPS: ${{ formatCurrency(form.valor_ips) }}</div>
                                <div>Porcentaje IPS: {{ form.porc_ips >= 0 ? '+' : '' }}{{ form.porc_ips || 0 }}%</div>
                                <div class="font-bold border-t border-blue-300 pt-1 mt-1">
                                    Valor Referencia: ${{ formatCurrency(valorReferenciaCalculado) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Información Adicional -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center p-4 rounded-lg" style="background-color: #2D6660;">
                        <Icon :path="mdiNoteText" class="w-5 h-5 mr-2" />
                        Información Adicional
                    </h3>

                    <div class="flex items-start gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 flex-shrink-0 pt-2">
                            Observaciones
                        </label>
                        <div class="flex-1 max-w-md">
                            <textarea
                                v-model="form.observaciones"
                                rows="3"
                                placeholder="Ej: Requiere ayuno de 12 horas, incluye material descartable..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-vertical"
                                :class="{ 'border-red-500': form.errors.observaciones }"
                            ></textarea>
                            <p class="text-xs text-gray-500 mt-1">Notas, restricciones o información especial</p>
                            <div v-if="form.errors.observaciones" class="text-red-500 text-sm mt-1">
                                {{ form.errors.observaciones }}
                            </div>
                        </div>
                    </div>
                </div>

                <BaseDivider />

                <!-- Botones de acción -->
                <div class="flex justify-end items-center gap-3 pt-6">
                    <button
                        type="button"
                        @click="$inertia.visit(route('prestaciones.index'))"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <Icon :path="mdiClose" class="w-4 h-4" />
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        @click.prevent="submit"
                        :disabled="form.processing || !isFormValid || codigoStatus === 'exists'"
                        :class="[
                            'inline-flex items-center gap-2 px-6 py-2 text-sm font-medium text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500',
                            form.processing || !isFormValid || codigoStatus === 'exists'
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-green-600 hover:bg-green-700'
                        ]"
                    >
                        <Icon
                            :path="form.processing ? mdiLoading : mdiCheck"
                            :spin="form.processing"
                            class="w-4 h-4"
                        />
                        {{ form.processing ? 'Guardando...' : 'Crear Prestación' }}
                    </button>
                </div>
            </CardBox>
        </SectionMain>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import {
    mdiMedicalBag,
    mdiArrowLeft,
    mdiInformation,
    mdiTag,
    mdiCurrencyUsd,
    mdiNoteText,
    mdiCheck,
    mdiClose,
    mdiLoading
} from '@mdi/js'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseDivider from '@/components/BaseDivider.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
    rubros: Array,
    siguienteCodigo: String
})

const form = useForm({
    codigo: props.siguienteCodigo || '',
    nombre: '',
    descripcion: '',
    estado: 'activo', // Directamente el campo que espera el backend
    rubro_id: '',
    precio_general: '',
    precio_afiliado: '',
    valor_ips: '',
    porc_ips: 0,
    observaciones: ''
})

// Computed para calcular el valor de referencia automáticamente con redondeo estándar
const valorReferenciaCalculado = computed(() => {
    const valorIps = parseFloat(form.valor_ips) || 0
    const porcIps = parseFloat(form.porc_ips) || 0
    return Math.round(valorIps * (1 + (porcIps / 100)))
})

// Campo reactivo para el checkbox basado en estado
const activo = computed({
    get: () => form.estado === 'activo',
    set: (value) => {
        form.estado = value ? 'activo' : 'inactivo'
    }
})

// Estado de verificación del código
const codigoStatus = ref('')

// Validación del formulario
const isFormValid = computed(() => {
    return form.codigo &&
        form.nombre &&
        form.rubro_id &&
        form.valor_ips &&
        form.porc_ips !== '' &&
        form.estado &&
        codigoStatus.value !== 'exists'
})

// Formatear moneda argentina
const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)
}

// Función para verificar si el código existe (TEMPORALMENTE DESHABILITADA)
const checkCodigoExists = async () => {
    // COMENTADO TEMPORALMENTE para poder crear prestaciones
    codigoStatus.value = 'available'
    return

    /*
    if (!form.codigo || form.codigo.length !== 6) {
        codigoStatus.value = ''
        return
    }

    codigoStatus.value = 'checking'

    try {
        console.log('Verificando código:', form.codigo)

        const response = await axios.get('/prestaciones/check-codigo', {
            params: { codigo: form.codigo }
        })

        console.log('Respuesta del servidor:', response.data)
        codigoStatus.value = response.data.exists ? 'exists' : 'available'

    } catch (error) {
        console.error('Error verificando código:', error)
        console.error('Response data:', error.response?.data)
        console.error('Status:', error.response?.status)

        codigoStatus.value = ''

        // Mostrar error específico si está disponible
        if (error.response?.data?.message) {
            alert('Error verificando código: ' + error.response.data.message)
        }
    }
    */
}

// Función para manejar cambios en el código
const onCodigoChange = (event) => {
    // Solo permitir números
    const value = event.target.value.replace(/\D/g, '')
    form.codigo = value.substring(0, 6)

    // Resetear estado cuando cambia
    codigoStatus.value = ''

    // Verificar automáticamente cuando tiene 6 dígitos
    if (form.codigo.length === 6) {
        setTimeout(() => {
            checkCodigoExists()
        }, 500) // Debounce de 500ms
    }
}

// Validar y limpiar campos numéricos
watch(() => form.valor_ips, (newValue) => {
    if (newValue && newValue < 0) {
        form.valor_ips = 0
    }
})

watch(() => form.porc_ips, (newValue) => {
  if (newValue && newValue < -100) {
    form.porc_ips = -100
  }
})

const submit = () => {
    console.log('=== ENVÍO DE DATOS ===')
    console.log('Datos del formulario:', form.data())

    // Verificar campos requeridos
    if (!form.codigo || !form.nombre || !form.rubro_id || !form.valor_ips || form.porc_ips === '') {
        alert('Por favor complete todos los campos requeridos')
        return
    }

    // Enviar directamente sin transformaciones
    form.post(route('prestaciones.store'), {
        onStart: () => {
            console.log('Iniciando envío...')
        },
        onSuccess: (page) => {
            console.log('¡Prestación creada exitosamente!')
        },
        onError: (errors) => {
            console.log('Errores de validación:', errors)

            // Mostrar cada error específicamente
            let errorMsg = 'Errores encontrados:\n'
            Object.entries(errors).forEach(([field, messages]) => {
                if (Array.isArray(messages)) {
                    errorMsg += `${field}: ${messages[0]}\n`
                } else {
                    errorMsg += `${field}: ${messages}\n`
                }
            })
            alert(errorMsg)
        },
        onFinish: () => {
            console.log('Proceso finalizado')
        }
    })
}
</script>
