<template>
    <div
        :class="layoutAsidePadding"
        class="pt-14 min-h-screen w-screen transition-position lg:w-auto"
        style="background-color: #EBF5F5;"
    >
        <NavBar
            :menu="menuNavBar"
            :class="[
        layoutAsidePadding,
        { 'ml-60 lg:ml-0': isAsideMobileExpanded }
      ]"
            @menu-click="menuClick"
        >
            <NavBarItemPlain use-margin>
                <FormControl
                    placeholder="Buscar (ctrl+k)"
                    ctrl-k-focus
                    transparent
                    borderless
                />
            </NavBarItemPlain>
        </NavBar>
        <AsideMenu
            :is-aside-mobile-expanded="isAsideMobileExpanded"
            :menu="menuAside"
            @menu-click="menuClick"
            @aside-lg-close-click="isAsideLgActive = false"
        />
        <slot/>
        <FooterBar>
            <a
                href="https://github.com/tu-usuario/tu-repo"
                target="_blank"
                class="text-blue-600"
            >
                Sistema de Gestión
            </a>
            © {{ new Date().getFullYear() }}
        </FooterBar>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useMainStore } from '@/stores/main.js'
import { useStyleStore } from '@/stores/style.js'
import BaseIcon from '@/components/BaseIcon.vue'
import FormControl from '@/components/FormControl.vue'
import NavBar from '@/components/NavBar.vue'
import NavBarItemPlain from '@/components/NavBarItemPlain.vue'
import AsideMenu from '@/components/AsideMenu.vue'
import FooterBar from '@/components/FooterBar.vue'
import { mdiForwardburger, mdiMenu, mdiChevronLeft, mdiChevronRight } from '@mdi/js'
import menuAside from '@/menuAside.js'
import menuNavBar from '@/menuNavBar.js'
import { router } from '@inertiajs/vue3'

const mainStore = useMainStore()
const styleStore = useStyleStore()

const isAsideMobileExpanded = ref(true) // Start with sidebar visible
const isAsideLgActive = ref(false)

const layoutAsidePadding = computed(() =>
    isAsideMobileExpanded.value ? 'xl:pl-60' : 'xl:pl-16'
)

// Declarar funciones ANTES de usarlas
const layoutResizeHandler = () => {
    isAsideLgActive.value = window.innerWidth >= 1280
}

const menuToggle = () => {
    isAsideMobileExpanded.value = !isAsideMobileExpanded.value
}

const menuClick = (event, item) => {
    if (!item) return

    if (item.isToggleSidebar) {
        menuToggle()
        return
    }

    if (item.isToggleLightDark) {
        styleStore.setDarkMode()
        return
    }

    if (item.isLogout) {
        router.post('/logout')
        return
    }

    if (item.to) {
        router.visit(item.to)
    }
}


// Lifecycle hooks
onMounted(() => {
    layoutResizeHandler()
    window.addEventListener('resize', layoutResizeHandler)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', layoutResizeHandler)
})
</script>
