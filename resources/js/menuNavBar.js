import {
    mdiAccount,
    mdiCogOutline,
    mdiEmail,
    mdiLogout,
    mdiThemeLightDark
} from '@mdi/js'

export default [
    {
        isCurrentUser: true,
        menu: [
            {
                icon: mdiAccount,
                label: 'Mi Perfil',
                to: '/profile'
            },
            {
                icon: mdiCogOutline,
                label: 'Configuración',
                to: '/settings'
            },
            {
                icon: mdiEmail,
                label: 'Mensajes',
                to: '/messages'
            },
            {
                isDivider: true
            },
            {
                icon: mdiLogout,
                label: 'Cerrar Sesión',
                isLogout: true
            }
        ]
    },
    {
        icon: mdiThemeLightDark,
        label: 'Modo Claro/Oscuro',
        isDesktopNoLabel: true,
        isToggleLightDark: true
    }
]
