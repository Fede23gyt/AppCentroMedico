<template>
    <li>
        <component
            :is="componentIs"
            :href="item.to"
            :target="item.target ?? null"
            class="flex cursor-pointer text-gray-300 hover:text-white hover:bg-gray-700 px-6 py-3 transition-colors"
            :class="componentClass"
            @click="menuClick"
        >
            <BaseIcon
                v-if="item.icon"
                :path="item.icon"
                class="flex-none mr-3 text-gray-400"
                :size="18"
            />
            <span class="grow text-ellipsis line-clamp-1">
        {{ item.label }}
      </span>
            <BaseIcon
                v-if="hasDropdown"
                :path="isDropdownActive ? mdiChevronUp : mdiChevronDown"
                class="flex-none text-gray-400 transition-transform"
                :size="16"
            />
        </component>

        <!-- Submenú -->
        <div
            v-if="hasDropdown"
            :class="[
        'transition-all duration-200 overflow-hidden',
        isDropdownActive ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'
      ]"
        >
            <ul class="bg-gray-800 border-l-2 border-gray-600 ml-6">
                <AsideMenuItem
                    v-for="(subItem, index) in item.menu"
                    :key="index"
                    :item="subItem"
                    is-dropdown-list
                    @menu-click="$emit('menu-click', $event)"
                />
            </ul>
        </div>
    </li>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { mdiChevronUp, mdiChevronDown } from '@mdi/js'
import BaseIcon from './BaseIcon.vue'

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isDropdownList: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['menu-click'])

const isDropdownActive = ref(false)

const componentIs = computed(() => {
    if (props.item.to) {
        return Link
    }
    return 'a'
})

const hasDropdown = computed(() => {
    return props.item.menu && Array.isArray(props.item.menu) && props.item.menu.length > 0
})

const menuClick = (event) => {
    if (hasDropdown.value) {
        event.preventDefault()
        isDropdownActive.value = !isDropdownActive.value
    }

    emit('menu-click', event, props.item)
}

const componentClass = computed(() => {
    const base = []

    // Estilos para items activos
    if (props.item.to && typeof window !== 'undefined' && window.location.pathname === props.item.to) {
        base.push('bg-gray-700 text-white')
    }

    // Estilos para submenús
    if (props.isDropdownList) {
        base.push('pl-12 text-sm')
    }

    return base
})
</script>
