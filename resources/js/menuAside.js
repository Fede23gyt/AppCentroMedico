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
    mdiCashCheck,
    mdiDoctor,
    mdiCurrencyUsd
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
            },
            {
                to: '/reportes/ventas',
                label: 'Ventas',
                icon: mdiCurrencyUsd
            }
        ]
    },
    {
        label: 'Configuración',
        icon: mdiCog,
        menu: [
            {
                label: 'Prestaciones',
                icon: mdiMedicalBag,
                menu: [
                    {
                        to: '/prestaciones',
                        label: 'Lista de Prestaciones',
                        icon: mdiMedicalBag
                    },
                    {
                        to: '/prestaciones/create',
                        label: 'Nueva Prestación',
                        icon: mdiMedicalBag
                    }
                ]
            },
            {
                label: 'Planes',
                icon: mdiCreditCard,
                menu: [
                    {
                        to: '/planes',
                        label: 'Lista de Planes',
                        icon: mdiCreditCard
                    },
                    {
                        to: '/planes/create',
                        label: 'Nuevo Plan',
                        icon: mdiCreditCard
                    }
                ]
            },
            {
                label: 'Usuarios y Accesos',
                icon: mdiAccountGroup,
                menu: [
                    {
                        to: '/users',
                        label: 'Usuarios',
                        icon: mdiAccountMultiple
                    },
                    {
                        to: '/roles',
                        label: 'Roles y Permisos',
                        icon: mdiLock
                    }
                ]
            },
            {
                label: 'Datos Maestros',
                icon: mdiTable,
                menu: [
                    {
                        to: '/prestadores',
                        label: 'Prestadores',
                        icon: mdiDoctor
                    },
                    {
                        to: '/rubros',
                        label: 'Rubros',
                        icon: mdiFolderMultiple
                    },
                    {
                        to: '/sucursales',
                        label: 'Sucursales',
                        icon: mdiBankTransfer
                    }
                ]
            },
            {
                to: '/settings',
                label: 'Configuración General',
                icon: mdiCog
            }
        ]
    }
]
