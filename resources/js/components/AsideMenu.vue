<template>
    <aside
        :class="[
      styleStore.asideStyle,
      isAsideMobileExpanded ? 'w-60' : 'w-16'
    ]"
        class="fixed top-0 left-0 z-40 transition-all duration-300 h-screen"
        style="background-color: #2D6660;"
    >
        <div
            :class="styleStore.asideBrandStyle"
            class="flex flex-row h-14 items-center justify-center"
            style="background-color: #245651;"
        >
            <!-- Logo completo cuando está expandido -->
            <div v-if="isAsideMobileExpanded" class="flex items-center justify-center px-4">
                <img
                    src="/images/LogoPieveSalud.jpg"
                    alt="Pieve Salud"
                    class="h-10 w-auto rounded"
                />
            </div>
            <!-- Logo mini cuando está colapsado -->
            <div v-else class="flex items-center justify-center">
                <img
                    src="/images/LogoPieveSalud.jpg"
                    alt="Pieve Salud"
                    class="h-8 w-8 rounded object-cover"
                />
            </div>
        </div>

        <div
            :class="styleStore.asideScrollbarsStyle"
            class="flex-1 overflow-y-auto overflow-x-hidden aside-scrollbars-gray"
        >
            <AsideMenuList
                :menu="menu"
                :is-collapsed="!isAsideMobileExpanded"
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
