import { defineStore } from 'pinia'
import { ref, computed, readonly } from 'vue' // ← Agregar readonly aquí
import { usePage } from '@inertiajs/vue3'

export const useMainStore = defineStore('main', () => {
    const user = ref(null)
    const isFieldFocusRegistered = ref(false)
    const clients = ref([])
    const history = ref([])

    // Usuario desde Inertia
    const userName = computed(() => {
        const page = usePage()
        return page.props.auth?.user?.name || user.value?.name || 'Usuario'
    })

    const userEmail = computed(() => {
        const page = usePage()
        return page.props.auth?.user?.email || user.value?.email || 'usuario@email.com'
    })

    const userAvatar = computed(() => {
        const page = usePage()
        return page.props.auth?.user?.avatar || null
    })

    const userRole = computed(() => {
        const page = usePage()
        return page.props.auth?.user?.roles?.[0]?.name || 'Sin rol'
    })

    function setUser(payload) {
        if (payload?.name) {
            user.value = payload
        }
    }

    function fetchSampleClients() {
        // Datos de ejemplo para Admin One
        clients.value = [
            { id: 1, avatar: null, name: 'Rebecca Bauch', company: 'Daugherty-Daniel', city: 'South Cory', progress: 79, created: 'Mar 3, 2021', created_mm_dd_yyyy: '03-03-2021' },
            { id: 2, avatar: null, name: 'Felicita Yundt', company: 'Johns-Weissnat', city: 'East Ariel', progress: 67, created: 'Jan 8, 2021', created_mm_dd_yyyy: '01-08-2021' },
            { id: 3, avatar: null, name: 'Mr. Larry Satterfield V', company: 'Hyatt LLC', city: 'Windlerburgh', progress: 53, created: 'Dec 18, 2020', created_mm_dd_yyyy: '12-18-2020' }
        ]
    }

    function fetchSampleHistory() {
        history.value = [
            { id: 1, amount: 254, account: 'Usuarios', name: 'Sistema', date: 'Mar 3, 2021', type: 'withdraw', business: 'Gestión' },
            { id: 2, amount: 89, account: 'Sucursales', name: 'Configuración', date: 'Feb 3, 2021', type: 'deposit', business: 'Administración' },
            { id: 3, amount: 3596, account: 'Reportes', name: 'Analytics', date: 'Feb 2, 2021', type: 'invoice', business: 'Sistema' }
        ]
    }

    return {
        user: readonly(user),
        userName,
        userEmail,
        userAvatar,
        userRole,
        isFieldFocusRegistered: readonly(isFieldFocusRegistered),
        clients: readonly(clients),
        history: readonly(history),
        setUser,
        fetchSampleClients,
        fetchSampleHistory
    }
})
