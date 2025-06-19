import {
    mdiAccountCircle,
    mdiMonitor,
    mdiLock,
    mdiTable,
    mdiViewList,
    mdiAccountMultiple,
    mdiCog,
    mdiChartLine,
    mdiBankTransfer,
    mdiCreditCard,
    mdiAccountGroup,
    mdiLogout,
    mdiThemeLightDark,
    mdiCashRegister,
    mdiClipboardList,
    mdiFileDocumentMultiple,
    mdiMedicalBag,
    mdiFolderMultiple,
    mdiCashCheck
} from '@mdi/js'

export default [
    {
        to: '/dashboard',
        icon: mdiMonitor,
        label: 'Dashboard'
    },
    {
        label: 'Cajas',
        icon: mdiCashRegister,
        menu: [
            {
                to: '/cajas',
                label: 'Gestión de Cajas',
                icon: mdiCashRegister
            },
            {
                to: '/cajas/movimientos',
                label: 'Movimientos',
                icon: mdiCashCheck
            }
        ]
    },
    {
        label: 'Órdenes',
        icon: mdiClipboardList,
        menu: [
            {
                to: '/ordenes',
                label: 'Lista de Órdenes',
                icon: mdiClipboardList
            },
            {
                to: '/ordenes/create',
                label: 'Nueva Orden',
                icon: mdiClipboardList
            }
        ]
    },
    {
        label: 'Rendiciones',
        icon: mdiFileDocumentMultiple,
        menu: [
            {
                to: '/rendiciones',
                label: 'Lista de Rendiciones',
                icon: mdiFileDocumentMultiple
            },
            {
                to: '/rendiciones/create',
                label: 'Nueva Rendición',
                icon: mdiFileDocumentMultiple
            }
        ]
    },
    {
        label: 'Reportes',
        icon: mdiChartLine,
        menu: [
            {
                to: '/reportes/usuarios',
                label: 'Usuarios',
                icon: mdiAccountMultiple
            },
            {
                to: '/reportes/sucursales',
                label: 'Sucursales',
                icon: mdiBankTransfer
            },
            {
                to: '/reportes/prestaciones',
                label: 'Prestaciones',
                icon: mdiMedicalBag
            },
            {
                to: '/reportes/planes',
                label: 'Planes',
                icon: mdiCreditCard
            },
            {
                to: '/reportes/cajas',
                label: 'Cajas',
                icon: mdiCashRegister
            },
            {
                to: '/reportes/ordenes',
                label: 'Órdenes',
                icon: mdiClipboardList
            }
        ]
    },
    {
        label: 'Configuración',
        icon: mdiCog,
        menu: [
            // Usuarios
            {
                to: '/users',
                label: 'Lista de Usuarios',
                icon: mdiAccountGroup
            },
            {
                to: '/users/create',
                label: 'Crear Usuario',
                icon: mdiAccountCircle
            },
            // Roles y Permisos
            {
                to: '/roles',
                label: 'Roles y Permisos',
                icon: mdiLock
            },
            // Planes
            {
                to: '/planes',
                label: 'Lista de Planes',
                icon: mdiCreditCard
            },
            {
                to: '/planes/create',
                label: 'Crear Plan',
                icon: mdiCreditCard
            },
            // Rubros
            {
                to: '/rubros',
                label: 'Lista de Rubros',
                icon: mdiFolderMultiple
            },
            {
                to: '/rubros/create',
                label: 'Crear Rubro',
                icon: mdiFolderMultiple
            },
            // Prestaciones
            {
                to: '/prestaciones',
                label: 'Lista de Prestaciones',
                icon: mdiMedicalBag
            },
            {
                to: '/prestaciones/create',
                label: 'Crear Prestación',
                icon: mdiMedicalBag
            },
            // Sucursales
            {
                to: '/sucursales',
                label: 'Lista de Sucursales',
                icon: mdiBankTransfer
            },
            {
                to: '/sucursales/create',
                label: 'Crear Sucursal',
                icon: mdiBankTransfer
            },
            // Configuración General
            {
                to: '/settings',
                label: 'Configuración General',
                icon: mdiCog
            }
        ]
    }
]
