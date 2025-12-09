<template>
    <Head title="Nueva Orden" />

    <AuthenticatedLayout>
        <div class="max-w-full mx-auto px-3 py-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h1 class="text-base font-semibold text-gray-900">Nueva Orden</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Crea una nueva orden de prestaciones</p>
                </div>
                <Link
                    :href="route('ordenes.index')"
                    class="text-gray-600 hover:text-gray-900 text-sm"
                >
                    Volver
                </Link>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="space-y-3">
                <!-- Sección 1: Búsqueda y Selección en 2 Columnas -->
                <div v-if="!form.beneficiario_id" class="bg-white rounded-md shadow-sm border border-gray-100 p-3">
                    <!-- Búsqueda -->
                    <div class="mb-2">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Certificado:</label>
                            <input
                                ref="certificadoInput"
                                v-model="certificadoBusqueda"
                                type="text"
                                class="flex-1 px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                placeholder="Ingrese certificado"
                                @keydown.enter.prevent="buscarAfiliados"
                                @keydown.down.prevent="() => {}"
                            />
                            <button
                                type="button"
                                @click="buscarAfiliados"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md transition-colors text-sm"
                            >
                                Buscar
                            </button>
                        </div>
                        <div v-if="errorBusqueda" class="mt-1 text-xs text-red-600">{{ errorBusqueda }}</div>
                    </div>

                    <!-- Layout en 2 columnas: Titular | Beneficiario -->
                    <div v-if="titulares.length > 0" class="grid grid-cols-2 gap-2">
                        <!-- Columna 1: Titular -->
                        <div class="border-r pr-2">
                            <h3 class="text-xs font-semibold text-gray-700 mb-1">1. Titular</h3>
                            <div
                                v-for="(titular, index) in titulares"
                                :key="titular.id"
                                class="p-1.5 border rounded cursor-pointer mb-1 text-xs transition-colors"
                                :class="{
                                    'bg-green-100 border-green-400': titularSeleccionado?.id === titular.id,
                                    'hover:bg-gray-50': titularSeleccionado?.id !== titular.id
                                }"
                                @click="seleccionarTitular(titular)"
                            >
                                <div class="font-medium text-gray-900">{{ titular.apellido }}, {{ titular.nombre }}</div>
                                <div class="text-[11px] text-gray-600">
                                    DNI: {{ titular.dni }} | Plan: {{ titular.plan?.nombre }}
                                </div>
                            </div>
                        </div>

                        <!-- Columna 2: Beneficiario -->
                        <div class="pl-2">
                            <h3 class="text-xs font-semibold text-gray-700 mb-1">2. Beneficiario (Persona a Atender)</h3>
                            <div v-if="titularSeleccionado" class="space-y-1">
                                <div
                                    v-for="(afiliado, index) in afiliados"
                                    :key="afiliado.id"
                                    :ref="el => { if (el) afiliadoRefs[index] = el }"
                                    class="p-1.5 border rounded cursor-pointer text-xs transition-colors"
                                    :class="{
                                        'bg-green-200 border-green-400': selectedBeneficiarioIndex === index,
                                        'hover:bg-gray-50': selectedBeneficiarioIndex !== index
                                    }"
                                    @click="seleccionarBeneficiarioPorClick(afiliado)"
                                    @keydown.down.prevent="navegarBeneficiarios(index + 1)"
                                    @keydown.up.prevent="navegarBeneficiarios(index - 1)"
                                    @keydown.enter.prevent="confirmarBeneficiario(afiliado)"
                                    tabindex="0"
                                >
                                    <div class="font-medium text-gray-900">{{ afiliado.apellido }}, {{ afiliado.nombre }}</div>
                                    <div class="text-[11px] text-gray-600">
                                        DNI: {{ afiliado.dni }} | {{ afiliado.vinculo_texto }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-xs text-gray-500 italic">
                                Seleccione un titular primero
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Titular y Beneficiario seleccionados (compacto) -->
                <div v-else class="bg-white rounded-md shadow-sm border border-gray-100 p-3">
                    <div class="space-y-2">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-xs font-semibold text-gray-700 mb-0.5">Titular:</h3>
                                <p class="font-medium text-gray-900 text-sm">{{ titularSeleccionado?.apellido }}, {{ titularSeleccionado?.nombre }}</p>
                                <p class="text-xs text-gray-600">
                                    Certificado: {{ titularSeleccionado?.certificado }} | Plan: {{ titularSeleccionado?.plan?.nombre }}
                                </p>
                            </div>
                        </div>
                        <div class="border-t pt-2 flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-xs font-semibold text-gray-700 mb-0.5">Beneficiario (a Atender):</h3>
                                <p class="font-medium text-gray-900 text-sm">{{ beneficiarioSeleccionado?.apellido }}, {{ beneficiarioSeleccionado?.nombre }}</p>
                                <p class="text-xs text-gray-600">
                                    DNI: {{ beneficiarioSeleccionado?.dni }} | {{ beneficiarioSeleccionado?.vinculo_texto }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="cambiarBeneficiario"
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                            >
                                Cambiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Datos de la Orden (Compacto) -->
                <div v-if="form.beneficiario_id" class="bg-white rounded-md shadow-sm border border-gray-100 p-2">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Prestador:</label>
                        <select
                            ref="prestadorInput"
                            v-model="form.prestador_id"
                            class="flex-1 px-2.5 py-1.5 border-2 border-custom-teal-500 rounded-md focus:outline-none focus:ring-2 focus:ring-custom-teal-600 text-sm"
                            @keydown.enter.prevent="focusPrestacion"
                        >
                            <option value="">Sin asignar</option>
                            <option v-for="prestador in prestadores" :key="prestador.id" :value="prestador.id">
                                {{ prestador.apellido }}, {{ prestador.nombre }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Sección 3: Prestaciones -->
                <div v-if="form.beneficiario_id" class="bg-white rounded-md shadow-sm border border-gray-100 p-3">
                    <h2 class="text-sm font-semibold text-gray-900 mb-2">3. Prestaciones</h2>

                    <!-- Búsqueda de prestación -->
                    <div class="mb-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Buscar Prestación</label>
                        <div class="flex gap-2">
                            <input
                                ref="prestacionInput"
                                v-model="prestacionBusqueda"
                                type="text"
                                class="flex-1 px-2.5 py-1.5 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                placeholder="Código o descripción de prestación..."
                                @input="buscarPrestaciones"
                                @keydown.down.prevent="navegarPrestaciones(0)"
                                @keydown.enter.prevent="agregarPrimeraPrestacion"
                            />
                        </div>

                        <!-- Resultados búsqueda prestaciones -->
                        <div v-if="prestacionesDisponibles.length > 0" class="mt-1 border border-gray-200 rounded max-h-60 overflow-y-auto">
                            <button
                                v-for="(prestacion, index) in prestacionesDisponibles"
                                :key="prestacion.id"
                                :ref="el => { if (el) prestacionRefs[index] = el }"
                                type="button"
                                class="w-full text-left px-2 py-1.5 border-b last:border-b-0 transition-colors"
                                :class="{
                                    'bg-blue-500': selectedPrestacionIndex === index,
                                    'hover:bg-blue-50 bg-white': selectedPrestacionIndex !== index
                                }"
                                @click="agregarPrestacion(prestacion)"
                                @keydown.down.prevent="navegarPrestaciones(index + 1)"
                                @keydown.up.prevent="navegarPrestaciones(index - 1)"
                                @keydown.enter.prevent="agregarPrestacion(prestacion)"
                                tabindex="0"
                            >
                                <div class="font-medium text-xs" :class="selectedPrestacionIndex === index ? 'text-white' : 'text-gray-900'">
                                    {{ prestacion.codigo }} - {{ prestacion.descripcion }}
                                </div>
                                <div class="text-[11px]" :class="selectedPrestacionIndex === index ? 'text-white' : 'text-gray-600'">
                                    Precio: ${{ prestacion.precio_plan }}
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Lista de prestaciones agregadas -->
                    <div v-if="form.detalles.length > 0" class="mt-2">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1.5 text-left text-[11px] font-semibold text-gray-600 uppercase">Prestación</th>
                                    <th class="px-2 py-1.5 text-center text-[11px] font-semibold text-gray-600 uppercase" style="width: 100px;">Cantidad</th>
                                    <th class="px-2 py-1.5 text-right text-[11px] font-semibold text-gray-600 uppercase" style="width: 120px;">Precio Unit.</th>
                                    <th class="px-2 py-1.5 text-right text-[11px] font-semibold text-gray-600 uppercase" style="width: 120px;">Total</th>
                                    <th class="px-2 py-1.5" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(detalle, index) in form.detalles" :key="index">
                                    <td class="px-2 py-1.5">
                                        <div class="text-xs font-medium text-gray-900">{{ detalle.codigo }} - {{ detalle.descripcion }}</div>
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        <input
                                            :ref="el => { if (el) cantidadInputs[index] = el }"
                                            v-model.number="detalle.cantidad"
                                            type="number"
                                            min="1"
                                            class="w-16 px-1.5 py-1 border border-gray-200 rounded text-xs text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            @input="calcularTotalesDetalle(index)"
                                            @keydown.enter.prevent="focusPrestacion"
                                        />
                                    </td>
                                    <td class="px-2 py-1.5 text-xs text-right text-gray-700">
                                        ${{ parseFloat(detalle.importe_uni).toFixed(2) }}
                                    </td>
                                    <td class="px-2 py-1.5 text-xs font-bold text-right text-gray-900">
                                        ${{ parseFloat(detalle.importe_total).toFixed(2) }}
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        <button
                                            type="button"
                                            @click="eliminarPrestacion(index)"
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                            title="Eliminar prestación"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Total -->
                        <div class="mt-3 flex justify-end">
                            <div class="bg-gray-50 rounded-md px-3 py-2 border-2 border-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-gray-700">TOTAL:</span>
                                    <span class="text-base font-bold text-blue-600">${{ calcularTotal() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-4 text-gray-500 text-xs">
                        No se han agregado prestaciones
                    </div>

                    <span v-if="form.errors.detalles" class="text-xs text-red-600">{{ form.errors.detalles }}</span>
                </div>

                <!-- Botones de acción -->
                <div v-if="form.beneficiario_id" class="flex justify-end gap-2">
                    <Link
                        :href="route('ordenes.index')"
                        class="px-3 py-1.5 border border-red-300 rounded-md text-red-600 hover:bg-red-50 hover:border-red-400 text-sm"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || form.detalles.length === 0"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar Orden' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';

const props = defineProps({
    tiposComprobante: Array,
    prestadores: Array,
    sucursalId: Number,
});

const certificadoInput = ref(null);
const prestadorInput = ref(null);
const prestacionInput = ref(null);
const cantidadInputs = ref([]);
const afiliadoRefs = ref([]);
const prestacionRefs = ref([]);

const certificadoBusqueda = ref('');
const prestacionBusqueda = ref('');
const afiliados = ref([]);
const titulares = ref([]);
const prestacionesDisponibles = ref([]);
const errorBusqueda = ref('');
const titularSeleccionado = ref(null);
const beneficiarioSeleccionado = ref(null);
const selectedBeneficiarioIndex = ref(-1);
const selectedPrestacionIndex = ref(-1);

const form = useForm({
    tipo_comprobante_id: props.tiposComprobante?.[0]?.id || 1, // Primer tipo o ID 1 por defecto
    titular_id: '',
    beneficiario_id: '',
    prestador_id: '',
    observacion: '',
    detalles: [],
});

// Manejador de tecla F2 para guardar
const handleKeyDown = (event) => {
    // Si presiona F2
    if (event.key === 'F2') {
        event.preventDefault(); // Evitar comportamiento por defecto del navegador

        // Validar que el formulario esté listo para guardar
        if (form.beneficiario_id && form.detalles.length > 0) {
            submit(); // Llamar a la misma función que el botón "Guardar Orden"
        } else {
            // Mostrar mensaje si falta información
            Swal.fire({
                icon: 'warning',
                title: 'Formulario Incompleto',
                text: 'Debe seleccionar un beneficiario y agregar al menos una prestación',
                confirmButtonColor: '#2563eb',
            });
        }
    }
};

onMounted(() => {
    certificadoInput.value?.focus();
    // Agregar listener para la tecla F2
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    // Limpiar listener cuando se desmonte el componente
    window.removeEventListener('keydown', handleKeyDown);
});

const buscarAfiliados = async () => {
    if (!certificadoBusqueda.value) {
        errorBusqueda.value = 'Ingrese un certificado';
        return;
    }

    try {
        const response = await axios.post('/api/ordenes/buscar-afiliados', {
            certificado: certificadoBusqueda.value,
        });

        console.log('Respuesta completa:', response.data);

        afiliados.value = response.data;

        console.log('Afiliados cargados:', afiliados.value);

        // Mostrar todos los vínculos para debuggear
        afiliados.value.forEach((a, index) => {
            console.log(`Afiliado ${index}: vinculo =`, a.vinculo, '(tipo:', typeof a.vinculo, ')');
        });

        // Filtrar solo titulares (vinculo puede ser número o string)
        titulares.value = afiliados.value.filter(a => {
            console.log('Evaluando afiliado:', a.nombre_completo, 'vinculo:', a.vinculo, 'es titular?', a.vinculo == 1);
            return a.vinculo == 1 || a.vinculo === '1';
        });

        console.log('Titulares filtrados:', titulares.value);

        errorBusqueda.value = '';

        if (titulares.value.length === 0) {
            errorBusqueda.value = 'No se encontró titular con ese certificado';
        } else if (titulares.value.length === 1) {
            // Auto-seleccionar si hay un solo titular
            seleccionarTitular(titulares.value[0]);
        }
    } catch (error) {
        errorBusqueda.value = 'Error al buscar afiliados';
        console.error('Error completo:', error);
        if (error.response) {
            console.error('Respuesta del servidor:', error.response.data);
        }
    }
};

const seleccionarTitular = (titular) => {
    titularSeleccionado.value = titular;
    selectedBeneficiarioIndex.value = 0;

    // Enfocar el primer beneficiario después de un tick
    nextTick(() => {
        if (afiliadoRefs.value[0]) {
            afiliadoRefs.value[0].focus();
        }
    });
};

const seleccionarBeneficiarioPorClick = (afiliado) => {
    const index = afiliados.value.findIndex(a => a.id === afiliado.id);
    selectedBeneficiarioIndex.value = index;
    confirmarBeneficiario(afiliado);
};

const navegarBeneficiarios = (newIndex) => {
    if (newIndex >= 0 && newIndex < afiliados.value.length) {
        selectedBeneficiarioIndex.value = newIndex;
        nextTick(() => {
            afiliadoRefs.value[newIndex]?.focus();
        });
    }
};

const confirmarBeneficiario = (afiliado) => {
    beneficiarioSeleccionado.value = afiliado;
    form.beneficiario_id = afiliado.id;

    // Asegurarnos de que el titular_id esté guardado en el form
    if (titularSeleccionado.value) {
        form.titular_id = titularSeleccionado.value.id;
    }

    nextTick(() => {
        prestadorInput.value?.focus();
    });
};

const cambiarTitular = () => {
    form.titular_id = '';
    form.beneficiario_id = '';
    titularSeleccionado.value = null;
    beneficiarioSeleccionado.value = null;
    titulares.value = [];
    form.detalles = [];

    nextTick(() => {
        certificadoInput.value?.focus();
    });
};

const cambiarBeneficiario = () => {
    form.beneficiario_id = '';
    beneficiarioSeleccionado.value = null;
    form.detalles = [];
};

const focusPrestacion = () => {
    prestacionInput.value?.focus();
};

const navegarPrestaciones = (newIndex) => {
    if (newIndex >= 0 && newIndex < prestacionesDisponibles.value.length) {
        selectedPrestacionIndex.value = newIndex;
        nextTick(() => {
            prestacionRefs.value[newIndex]?.focus();
        });
    }
};

const buscarPrestaciones = debounce(async () => {
    if (!prestacionBusqueda.value || prestacionBusqueda.value.length < 2) {
        prestacionesDisponibles.value = [];
        selectedPrestacionIndex.value = -1;
        return;
    }

    try {
        const response = await axios.get('/api/prestaciones/buscar', {
            params: {
                search: prestacionBusqueda.value,
                plan_id: beneficiarioSeleccionado.value?.plan_id,
                activas: 1,
            },
        });

        prestacionesDisponibles.value = response.data;
        // Auto-seleccionar la primera prestación si hay resultados
        selectedPrestacionIndex.value = prestacionesDisponibles.value.length > 0 ? 0 : -1;

        // Enfocar el primer elemento después de renderizar
        if (prestacionesDisponibles.value.length > 0) {
            nextTick(() => {
                prestacionRefs.value[0]?.focus();
            });
        }
    } catch (error) {
        console.error('Error al buscar prestaciones:', error);
    }
}, 300);

const agregarPrimeraPrestacion = () => {
    if (prestacionesDisponibles.value.length > 0) {
        agregarPrestacion(prestacionesDisponibles.value[0]);
    }
};

const agregarPrestacion = async (prestacion) => {
    try {
        const response = await axios.post('/api/ordenes/obtener-precio-prestacion', {
            prestacion_id: prestacion.id,
            afiliado_id: form.beneficiario_id,
        });

        const precio = response.data;

        const nuevoIndex = form.detalles.length;

        form.detalles.push({
            prestacion_id: prestacion.id,
            codigo: prestacion.codigo,
            descripcion: prestacion.descripcion,
            cantidad: 1,
            importe_uni: precio.importe_uni,
            importe_total: precio.importe_uni,
            neto_gravado: precio.neto_gravado,
            iva: precio.iva,
            total_afiliado: precio.total_afiliado,
            total_prestador: precio.total_prestador,
        });

        prestacionBusqueda.value = '';
        prestacionesDisponibles.value = [];
        selectedPrestacionIndex.value = -1; // Reset selection

        // Enfocar el campo de cantidad de la prestación recién agregada
        nextTick(() => {
            const cantidadInput = cantidadInputs.value[nuevoIndex];
            if (cantidadInput) {
                cantidadInput.focus();
                cantidadInput.select();
            }
        });

    } catch (error) {
        alert(error.response?.data?.error || 'Error al agregar prestación');
    }
};

const eliminarPrestacion = (index) => {
    form.detalles.splice(index, 1);
};

const calcularTotalesDetalle = (index) => {
    const detalle = form.detalles[index];

    // Guardamos los valores unitarios originales si no están guardados
    if (!detalle.importe_uni_original) {
        detalle.importe_uni_original = detalle.importe_uni;
        detalle.total_afiliado_original = detalle.total_afiliado;
        detalle.total_prestador_original = detalle.total_prestador;
    }

    // Recalculamos los totales basados en la cantidad
    detalle.importe_total = detalle.cantidad * detalle.importe_uni_original;
    detalle.total_afiliado = detalle.cantidad * detalle.total_afiliado_original;
    detalle.total_prestador = detalle.cantidad * detalle.total_prestador_original;
};

const calcularTotal = () => {
    return form.detalles.reduce((sum, d) => sum + parseFloat(d.importe_total), 0).toFixed(2);
};

const calcularTotalAfiliado = () => {
    return form.detalles.reduce((sum, d) => sum + parseFloat(d.total_afiliado), 0).toFixed(2);
};

const calcularTotalPrestador = () => {
    return form.detalles.reduce((sum, d) => sum + parseFloat(d.total_prestador), 0).toFixed(2);
};

const submit = () => {
    // Limpiar detalles para enviar solo los campos requeridos
    const detallesLimpios = form.detalles.map(detalle => ({
        prestacion_id: detalle.prestacion_id,
        cantidad: detalle.cantidad,
        importe_uni: detalle.importe_uni_original || detalle.importe_uni,
        importe_total: detalle.importe_total,
        neto_gravado: detalle.neto_gravado || 0,
        iva: detalle.iva || 0,
        total_afiliado: detalle.total_afiliado,
        total_prestador: detalle.total_prestador || 0,
    }));

    // Crear objeto de datos a enviar
    const data = {
        tipo_comprobante_id: form.tipo_comprobante_id,
        titular_id: form.titular_id,
        beneficiario_id: form.beneficiario_id,
        prestador_id: form.prestador_id || null,
        observacion: form.observacion || null,
        detalles: detallesLimpios,
    };

    form.transform(() => data).post(route('ordenes.store'), {
        onSuccess: (response) => {
            // Obtener el número de orden de la respuesta
            const ordenNumero = response.props.flash?.orden_numero || response.props.orden?.numero || 'N/A';

            // Mostrar SweetAlert
            Swal.fire({
                icon: 'success',
                title: '¡Orden Creada!',
                html: `Se ha generado la orden N° <strong>${ordenNumero}</strong>`,
                confirmButtonText: 'Ver Detalle',
                confirmButtonColor: '#2563eb',
                timer: 3000,
                timerProgressBar: true,
            }).then(() => {
                // Redirigir al detalle de la orden
                if (response.props.orden?.id) {
                    router.visit(route('ordenes.show', response.props.orden.id));
                }
            });
        },
        onError: (errors) => {
            Swal.fire({
                icon: 'error',
                title: 'Error al crear orden',
                text: errors.message || 'Hubo un problema al crear la orden',
                confirmButtonColor: '#dc2626',
            });
        }
    });
};
</script>
