import {
    mdiAccount,
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
