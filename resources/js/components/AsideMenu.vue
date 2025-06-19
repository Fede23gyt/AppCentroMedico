<template>
    <aside
        :class="[
      styleStore.asideStyle,
      { '-translate-x-full': !isAsideMobileExpanded },
      { 'lg:-translate-x-full': !isAsideLgActive }
    ]"
        class="bg-gray-800 -translate-x-full fixed top-0 left-0 z-40 w-60 transition-position lg:translate-x-0 h-screen"
    >
        <div
            :class="styleStore.asideBrandStyle"
            class="flex flex-row h-14 items-center justify-between px-6 bg-gray-900"
        >
            <div class="text-center flex-1 lg:text-left lg:flex-none">
                <div class="inline-flex items-center">
                    <BaseIcon
                        :path="mdiMonitor"
                        class="text-blue-400 mr-2"
                        size="32"
                    />
                    <b class="font-black text-xl text-white">Sistema</b>
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
