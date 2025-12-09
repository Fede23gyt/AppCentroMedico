<!-- resources/js/components/AsideMenuList.vue -->
<template>
    <ul>
        <AsideMenuItem
            v-for="(item, index) in menu"
            :key="index"
            :item="item"
            :is-collapsed="isCollapsed"
            @menu-click="$emit('menu-click', $event)"
        />
    </ul>
</template>

<script setup>
import AsideMenuItem from './AsideMenuItem.vue'

defineProps({
    menu: {
        type: Array,
        default: () => []
    },
    isCollapsed: {
        type: Boolean,
        default: false
    }
})

defineEmits(['menu-click'])
</script>
