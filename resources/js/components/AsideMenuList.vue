<!-- resources/js/components/AsideMenuList.vue -->
<template>
    <ul>
        <AsideMenuItem
            v-for="(item, index) in menu"
            :key="index"
            :item="item"
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
    }
})

defineEmits(['menu-click'])
</script>
