<template>
    <nav
        class="top-0 inset-x-0 fixed bg-white h-14 z-30 transition-position w-screen lg:w-auto dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700"
        :class="styleStore.navBarItemLabelActiveColorStyle"
    >
        <div class="flex lg:items-stretch" :class="containerMaxW">
            <div class="flex flex-1 items-stretch h-14">
                <div class="hidden lg:flex lg:items-center lg:mr-6 xl:mr-12">
                    <div class="inline-flex items-center">
                        <BaseIcon
                            :path="mdiMonitor"
                            class="text-blue-500 mr-2"
                            size="32"
                        />
                        <b class="font-black text-xl text-blue-500">Sistema</b>
                    </div>
                </div>
                <div class="flex-1 flex items-center h-14">
                    <slot />
                </div>
                <div class="flex items-center h-14">
                    <NavBarMenu
                        v-for="(menu, index) in menu"
                        :key="index"
                        :menu="menu"
                        @menu-click="$emit('menu-click', $event)"
                    />
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { mdiMonitor } from '@mdi/js'
import { useStyleStore } from '@/stores/style.js'
import { containerMaxW } from '@/config.js'
import BaseIcon from '@/components/BaseIcon.vue'
import NavBarMenu from '@/components/NavBarMenu.vue'

defineProps({
    menu: {
        type: Array,
        default: () => []
    }
})

defineEmits(['menu-click'])

const styleStore = useStyleStore()
</script>
