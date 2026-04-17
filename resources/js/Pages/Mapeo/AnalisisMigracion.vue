<template>
    <Head title="Análisis de Migración PieveSalud" />

    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiTable"
                title="Resultados del Análisis de Migración (Solo Lectura)"
                main
            >
            </SectionTitleLineWithButton>

            <!-- Filtros -->
            <CardBox class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Buscar Afiliado
                        </label>
                        <input
                            v-model="form.buscar"
                            type="text"
                            placeholder="DNI, Apellido/Nombre o Titular CF..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @keyup.enter="filtrar"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Cobertura Inferida
                        </label>
                        <select
                            v-model="form.cobertura"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @change="filtrar"
                        >
                            <option value="">Todas</option>
                            <option value="INI">INI</option>
                            <option value="ODO">ODO</option>
                            <option value="ODO_02">ODO_02</option>
                            <option value="PIE">PIE</option>
                            <option value="SINSAL">SINSAL</option>
                            <option value="PIEVERF">PIEVERF</option>
                            <option value="PIEVEMETAN">PIEVEMETAN</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <BaseButton
                        label="Filtrar"
                        color="info"
                        :icon="mdiMagnify"
                        @click="filtrar"
                    />
                    <BaseButton
                        label="Limpiar"
                        color="whiteDark"
                        class="ml-2"
                        @click="limpiarFiltros"
                    />
                </div>
            </CardBox>

            <!-- Alerta informativa -->
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-md p-4 flex items-start mb-6 w-full">
                <svg class="h-5 w-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm">
                    <strong>Estos datos son el resultado del último script de comando `analizar-migracion-salud`.</strong> 
                    No afectan la base de datos de producción real hasta que no se corra un proceso de actualización final.
                </div>
            </div>

            <!-- Tabla -->
            <CardBox class="mb-6" has-table>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-800 text-sm">
                                <th class="p-3 border-b">IdTitularCF</th>
                                <th class="p-3 border-b">DNI</th>
                                <th class="p-3 border-b">Afiliado</th>
                                <th class="p-3 border-b">Vinculo</th>
                                <th class="p-3 border-b bg-yellow-50 dark:bg-yellow-900/20 text-center">Planes Originales (CS/ODO/PIE)</th>
                                <th class="p-3 border-b bg-green-50 dark:bg-green-900/20 text-center font-bold">Cobertura Inferida Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="res in resultados.data" :key="res.id" class="border-b dark:border-gray-700 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="p-3 text-gray-600">{{ res.id_titular_cf }}</td>
                                <td class="p-3 font-mono">{{ res.dni }}</td>
                                <td class="p-3 font-bold">{{ res.apellido_nombre }}</td>
                                <td class="p-3">{{ res.vinculo }}</td>
                                <td class="p-3 bg-yellow-50/50 dark:bg-yellow-900/10 text-center text-xs">
                                    <div class="flex flex-wrap justify-center gap-1">
                                        <span v-if="res.plan_salud" class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded">S: {{ res.plan_salud }}</span>
                                        <span v-if="res.plan_odo" class="px-2 py-0.5 bg-green-100 text-green-800 rounded">O: {{ res.plan_odo }}</span>
                                        <span v-if="res.plan_pieve" class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded">P: {{ res.plan_pieve }}</span>
                                        <span v-if="!res.plan_salud && !res.plan_odo && !res.plan_pieve" class="text-gray-400">Ninguno</span>
                                    </div>
                                </td>
                                <td class="p-3 bg-green-50/50 dark:bg-green-900/10 text-center">
                                    <div v-if="res.cobertura_inferida" class="flex flex-wrap justify-center gap-1">
                                        <span v-for="(cob, index) in res.cobertura_inferida.split(',')" :key="index" class="px-2 py-1 bg-green-200 text-green-900 font-bold rounded-sm text-xs border border-green-300">
                                            {{ cob.trim() }}
                                        </span>
                                    </div>
                                    <div v-else class="text-xs text-red-500 font-bold">Sin cobertura</div>
                                    <div v-if="res.observaciones" class="text-[10px] text-gray-500 mt-1 italic">{{ res.observaciones }}</div>
                                </td>
                            </tr>
                            <tr v-if="resultados.data.length === 0">
                                <td colspan="6" class="p-6 text-center text-gray-500">
                                    No se encontraron registros de análisis. Ejecuta el comando <code>php artisan app:analizar-migracion-salud</code> primero.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="p-3 flex justify-between items-center border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <span class="text-sm text-gray-500">
                        Mostrando {{ resultados.from || 0 }} - {{ resultados.to || 0 }} de {{ resultados.total }}
                    </span>
                    <div class="flex gap-1" v-if="resultados.total > 0">
                        <Link 
                            v-for="(link, i) in resultados.links" 
                            :key="i"
                            :href="link.url || '#'"
                            class="px-3 py-1 text-sm border rounded"
                            :class="[
                                link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-100',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </CardBox>
        </SectionMain>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import {
    mdiTable,
    mdiMagnify
} from '@mdi/js'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseButton from '@/components/BaseButton.vue'

const props = defineProps({
    resultados: Object,
    filtros: Object,
})

const form = reactive({
    buscar: props.filtros?.buscar || '',
    cobertura: props.filtros?.cobertura || '',
})

const filtrar = () => {
    router.get(route('mapeo.analisis'), form, {
        preserveState: true,
        replace: true
    })
}

const limpiarFiltros = () => {
    form.buscar = ''
    form.cobertura = ''
    filtrar()
}
</script>
