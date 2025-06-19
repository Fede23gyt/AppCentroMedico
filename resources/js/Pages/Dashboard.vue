<template>
    <Head title="Dashboard" />

    <LayoutAuthenticated>
        <SectionMain>
            <!-- Header principal -->
            <SectionTitleLineWithButton
                :icon="mdiViewDashboard"
                title="Dashboard"
                subtitle="Bienvenido al sistema de gestión"
                main
            >
                <div class="flex space-x-3">
                    <BaseButton
                        :icon="mdiReload"
                        label="Actualizar"
                        color="light"
                        @click="refreshData"
                    />
                    <BaseButton
                        :icon="mdiAccountMultiple"
                        label="Gestionar Usuarios"
                        color="info"
                        :href="'/users'"
                    />
                </div>
            </SectionTitleLineWithButton>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <CardBoxWidget
                    :icon="mdiAccountMultiple"
                    :number="stats.totalUsers"
                    label="Total Usuarios"
                    icon-color="blue"
                    trend="+12%"
                    trend-type="up"
                />
                <CardBoxWidget
                    :icon="mdiBankTransfer"
                    :number="stats.totalSucursales"
                    label="Sucursales"
                    icon-color="green"
                    trend="Estable"
                    trend-type="stable"
                />
                <CardBoxWidget
                    :icon="mdiCurrencyUsd"
                    :number="stats.ventasDelMes"
                    prefix="$"
                    label="Ventas del Mes"
                    icon-color="purple"
                    trend="+8.2%"
                    trend-type="up"
                />
                <CardBoxWidget
                    :icon="mdiChartLine"
                    :number="stats.activeUsers"
                    label="Usuarios Activos"
                    icon-color="orange"
                    trend="+5%"
                    trend-type="up"
                />
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Usuarios por Sucursal -->
                <CardBox
                    title="Usuarios por Sucursal"
                    :icon="mdiAccountGroup"
                    :header-icon="mdiReload"
                    @header-icon-click="refreshCharts"
                >
                    <div class="h-64 flex items-center justify-center">
                        <canvas ref="usersChart" class="max-w-full max-h-full"></canvas>
                    </div>
                </CardBox>

                <!-- Distribución por Roles -->
                <CardBox
                    title="Distribución por Roles"
                    :icon="mdiAccountCog"
                    :header-icon="mdiReload"
                    @header-icon-click="refreshCharts"
                >
                    <div class="space-y-4">
                        <div v-for="role in roleStats" :key="role.name" class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div :class="role.color" class="w-3 h-3 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                  {{ role.name }}
                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ role.count }}
                </span>
                                <div class="w-20 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div :class="role.color" class="h-2 rounded-full" :style="`width: ${role.percentage}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <CardBox hover>
                    <div class="flex items-center p-6">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <BaseIcon :path="mdiAccountPlus" class="text-blue-600" :size="24" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Crear Usuario
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Añadir nuevo usuario al sistema
                            </p>
                        </div>
                        <div class="ml-auto">
                            <BaseButton
                                :icon="mdiArrowRight"
                                color="light"
                                :href="'/users/create'"
                            />
                        </div>
                    </div>
                </CardBox>

                <CardBox hover>
                    <div class="flex items-center p-6">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <BaseIcon :path="mdiBankPlus" class="text-green-600" :size="24" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Nueva Sucursal
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Registrar nueva sucursal
                            </p>
                        </div>
                        <div class="ml-auto">
                            <BaseButton
                                :icon="mdiArrowRight"
                                color="light"
                                :href="'/sucursales/create'"
                            />
                        </div>
                    </div>
                </CardBox>

                <CardBox hover>
                    <div class="flex items-center p-6">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                <BaseIcon :path="mdiChartBox" class="text-purple-600" :size="24" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Ver Reportes
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Análisis y estadísticas
                            </p>
                        </div>
                        <div class="ml-auto">
                            <BaseButton
                                :icon="mdiArrowRight"
                                color="light"
                                href="#"
                            />
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Usuarios Recientes -->
                <CardBox
                    title="Usuarios Recientes"
                    :icon="mdiAccountClock"
                >
                    <div class="space-y-4">
                        <div v-for="user in recentUsers" :key="user.id" class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center">
                  <span class="text-white font-semibold text-sm">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ user.role }} • {{ user.sucursal }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ user.created_at }}
                                </p>
                                <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ user.is_active ? 'Activo' : 'Inactivo' }}
                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <BaseButton
                            label="Ver todos los usuarios"
                            :icon="mdiArrowRight"
                            color="light"
                            :href="'/users'"
                            class="w-full"
                        />
                    </div>
                </CardBox>

                <!-- Activity Feed -->
                <CardBox
                    title="Actividad Reciente"
                    :icon="mdiHistory"
                >
                    <div class="space-y-4">
                        <div v-for="activity in activities" :key="activity.id" class="flex items-start space-x-3 py-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                            <div :class="activity.iconBg" class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
                                <BaseIcon :path="activity.icon" :class="activity.iconColor" :size="16" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">{{ activity.user }}</span>
                                    {{ activity.action }}
                                    <span class="font-medium">{{ activity.target }}</span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ activity.time }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import {
    mdiViewDashboard,
    mdiAccountMultiple,
    mdiBankTransfer,
    mdiCurrencyUsd,
    mdiChartLine,
    mdiReload,
    mdiAccountGroup,
    mdiAccountCog,
    mdiAccountPlus,
    mdiBankPlus,
    mdiChartBox,
    mdiArrowRight,
    mdiAccountClock,
    mdiHistory,
    mdiAccountCheck,
    mdiPlus,
    mdiPencil
} from '@mdi/js'


import LayoutAuthenticated from '@/Layouts/AuthenticatedLayout.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import CardBoxWidget from '@/components/CardBoxWidget.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseIcon from '@/components/BaseIcon.vue'
/*
import LayoutAuthenticated from '../Layouts/LayoutAuthenticated.vue'
import SectionMain from '../components/SectionMain.vue'
import SectionTitleLineWithButton from '../components/SectionTitleLineWithButton.vue'
import CardBox from '../components/CardBox.vue'
import CardBoxWidget from '../components/CardBoxWidget.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseIcon from '../components/BaseIcon.vue'
*/
const props = defineProps({
    totalUsers: {
        type: Number,
        default: 0
    },
    recentUsers: {
        type: Array,
        default: () => []
    }
})

// Stats simuladas (en una app real vendrían del backend)
const stats = ref({
    totalUsers: props.totalUsers || 24,
    totalSucursales: 8,
    ventasDelMes: 156890,
    activeUsers: 18
})

const roleStats = ref([
    { name: 'Administrador', count: 2, percentage: 8, color: 'bg-blue-500' },
    { name: 'Supervisor', count: 5, percentage: 21, color: 'bg-green-500' },
    { name: 'Cajero', count: 8, percentage: 33, color: 'bg-yellow-500' },
    { name: 'Vendedor', count: 7, percentage: 29, color: 'bg-purple-500' },
    { name: 'Administrativo', count: 2, percentage: 9, color: 'bg-red-500' }
])

const activities = ref([
    {
        id: 1,
        user: 'Juan Pérez',
        action: 'creó el usuario',
        target: 'María González',
        time: 'Hace 2 minutos',
        icon: mdiAccountPlus,
        iconBg: 'bg-green-100',
        iconColor: 'text-green-600'
    },
    {
        id: 2,
        user: 'Ana Silva',
        action: 'actualizó la sucursal',
        target: 'Centro Norte',
        time: 'Hace 1 hora',
        icon: mdiPencil,
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600'
    },
    {
        id: 3,
        user: 'Carlos López',
        action: 'activó el usuario',
        target: 'Pedro Martín',
        time: 'Hace 3 horas',
        icon: mdiAccountCheck,
        iconBg: 'bg-green-100',
        iconColor: 'text-green-600'
    }
])

const refreshData = () => {
    // Aquí harías una llamada al backend para refrescar datos
    console.log('Refrescando datos...')
}

const refreshCharts = () => {
    // Aquí refrescarías los gráficos
    console.log('Refrescando gráficos...')
}
</script>
