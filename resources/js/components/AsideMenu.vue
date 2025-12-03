<template>
    <aside
        :class="[
      styleStore.asideStyle,
      { '-translate-x-full': !isAsideMobileExpanded },
      { 'lg:-translate-x-full': !isAsideLgActive }
    ]"
        class="-translate-x-full fixed top-0 left-0 z-40 w-60 transition-position lg:translate-x-0 h-screen"
        style="background-color: #2D6660;"
    >
        <div
            :class="styleStore.asideBrandStyle"
            class="flex flex-row h-14 items-center justify-between px-6"
            style="background-color: #245651;"
        >
            <div class="text-center flex-1 lg:text-left lg:flex-none">
                <div class="flex items-center justify-center w-full">
                    <img
                        src="/images/LogoPieveSalud.jpg"
                        alt="Pieve Salud"
                        class="h-12 w-auto rounded"
                    />
                </div>
            </div>
            <button
                class="hidden lg:inline-block xl:hidden p-3"
                @click.prevent="$emit('aside-lg-close-click')"
            >
                <BaseIcon :path="mdiClose" />
            </button>
        </div>
        <div
            :class="styleStore.asideScrollbarsStyle"
            class="flex-1 overflow-y-auto overflow-x-hidden aside-scrollbars-gray"
        >
            <AsideMenuList
                :menu="menu"
                @menu-click="$emit('menu-click', $event)"
            />
        </div>
    </aside>
</template>

<script setup>
import { mdiMonitor, mdiClose } from '@mdi/js'
import { useStyleStore } from '@/stores/style.js'
import BaseIcon from '@/components/BaseIcon.vue'
import AsideMenuList from '@/components/AsideMenuList.vue'

defineProps({
    menu: {
        type: Array,
        default: () => []
    },
    isAsideMobileExpanded: {
        type: Boolean,
        default: false
    },
    isAsideLgActive: {
        type: Boolean,
        default: false
    }
})

defineEmits(['menu-click', 'aside-lg-close-click'])

const styleStore = useStyleStore()
</script>
