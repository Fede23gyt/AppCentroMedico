import { defineStore } from 'pinia'
import { ref, readonly } from 'vue' // ← Agregar readonly aquí
import * as styles from '@/styles.js'
import { darkModeKey, styleKey } from '@/config.js'

export const useStyleStore = defineStore('style', () => {
    // Estilos del menú lateral
    const asideStyle = ref(styles.white.aside)
    const asideScrollbarsStyle = ref(styles.white.asideScrollbars)
    const asideBrandStyle = ref(styles.white.asideBrand)
    const asideMenuItemStyle = ref(styles.white.asideMenuItem)
    const asideMenuItemActiveStyle = ref(styles.white.asideMenuItemActive)
    const asideMenuDropdownStyle = ref(styles.white.asideMenuDropdown)

    // Estilos de la barra de navegación
    const navBarItemLabelStyle = ref(styles.white.navBarItemLabel)
    const navBarItemLabelHoverStyle = ref(styles.white.navBarItemLabelHover)
    const navBarItemLabelActiveColorStyle = ref(styles.white.navBarItemLabelActiveColor)

    // Estilos de overlay
    const overlayStyle = ref(styles.white.overlay)

    // Modo oscuro
    const isDarkMode = ref(false)

    function setStyle(payload) {
        localStorage.setItem(styleKey, payload)

        const style = styles[payload] || styles.white

        asideStyle.value = style.aside
        asideScrollbarsStyle.value = style.asideScrollbars
        asideBrandStyle.value = style.asideBrand
        asideMenuItemStyle.value = style.asideMenuItem
        asideMenuItemActiveStyle.value = style.asideMenuItemActive
        asideMenuDropdownStyle.value = style.asideMenuDropdown
        navBarItemLabelStyle.value = style.navBarItemLabel
        navBarItemLabelHoverStyle.value = style.navBarItemLabelHover
        navBarItemLabelActiveColorStyle.value = style.navBarItemLabelActiveColor
        overlayStyle.value = style.overlay
    }

    function setDarkMode(payload = null) {
        isDarkMode.value = payload !== null ? payload : !isDarkMode.value

        localStorage.setItem(darkModeKey, isDarkMode.value ? '1' : '0')

        if (typeof document !== 'undefined') {
            if (isDarkMode.value) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        }
    }

    return {
        asideStyle: readonly(asideStyle),
        asideScrollbarsStyle: readonly(asideScrollbarsStyle),
        asideBrandStyle: readonly(asideBrandStyle),
        asideMenuItemStyle: readonly(asideMenuItemStyle),
        asideMenuItemActiveStyle: readonly(asideMenuItemActiveStyle),
        asideMenuDropdownStyle: readonly(asideMenuDropdownStyle),
        navBarItemLabelStyle: readonly(navBarItemLabelStyle),
        navBarItemLabelHoverStyle: readonly(navBarItemLabelHoverStyle),
        navBarItemLabelActiveColorStyle: readonly(navBarItemLabelActiveColorStyle),
        overlayStyle: readonly(overlayStyle),
        isDarkMode: readonly(isDarkMode),
        setStyle,
        setDarkMode
    }
})
