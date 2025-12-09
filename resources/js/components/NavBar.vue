<template>
    <nav
        class="top-0 inset-x-0 fixed bg-white h-14 z-30 transition-position w-screen lg:w-auto dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700"
        :class="styleStore.navBarItemLabelActiveColorStyle"
    >
        <div class="flex lg:items-stretch" :class="containerMaxW">
            <div class="flex flex-1 items-stretch h-14">
                <!-- Botón Hamburguesa a la izquierda -->
                <div class="flex items-center h-14">
                    <button
                        @click="$emit('menu-click', $event, { isToggleSidebar: true })"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors mr-2"
                        title="Toggle Sidebar"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-gray-700 dark:text-gray-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                </div>

                <div class="hidden lg:flex lg:items-center lg:mr-6 xl:mr-12">
                    <div class="inline-flex items-center">
                        <img
                            src="/images/LogoPieveSalud.jpg"
                            alt="Pieve Salud"
                            class="h-10 w-auto mr-3 rounded"
                        />
                        <b class="font-black text-xl" style="color: #2D6660;">Emisión</b>
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
