<template>
    <div class="relative" @click="menuClick">
        <div
            class="flex items-center cursor-pointer py-2 px-3 text-gray-700 hover:text-black transition-colors"
            :class="{ 'lg:py-2 lg:px-3': menu.isDesktopNoLabel }"
        >
            <BaseIcon
                v-if="menu.icon"
                :path="menu.icon"
                class="transition-colors"
            />
            <span
                v-if="!menu.isDesktopNoLabel"
                class="px-2 transition-colors"
            >
        {{ menu.label || userName }}
      </span>
            <BaseIcon
                v-if="menu.menu"
                :path="mdiChevronDown"
                class="hidden lg:inline-flex transition-transform"
                :class="{ 'rotate-180': isMenuNavBarActive }"
                w="3"
                h="3"
            />
        </div>
        <div
            v-if="menu.menu"
            :class="[
        styleStore.navBarMenuListStyle,
        { 'lg:hidden': !isMenuNavBarActive }
      ]"
            class="text-small border-b border-gray-200 lg:border lg:bg-white lg:absolute lg:top-full lg:right-0 lg:min-w-full lg:z-20 lg:rounded-lg lg:shadow-lg lg:dark:bg-slate-800"
        >
            <NavBarMenuList
                :menu="menu.menu"
                @menu-click="menuClickDropdown"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { mdiChevronDown } from '@mdi/js'
import { useMainStore } from '@/stores/main.js'
import { useStyleStore } from '@/stores/style.js'
import BaseIcon from '@/components/BaseIcon.vue'
import NavBarMenuList from '@/components/NavBarMenuList.vue'

const props = defineProps({
    menu: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['menu-click'])

const mainStore = useMainStore()
const styleStore = useStyleStore()

const isMenuNavBarActive = ref(false)

const userName = computed(() => {
    if (props.menu.isCurrentUser) {
        return mainStore.userName
    }

    return props.menu.label
})

const menuClick = () => {
    if (props.menu.menu) {
        isMenuNavBarActive.value = !isMenuNavBarActive.value
    }
}

const menuClickDropdown = (event, item) => {
    if (item.isLogout) {
        // Cerrar menú
        isMenuNavBarActive.value = false
    }

    emit('menu-click', event, item)
}
</script>
