<template>
    <div>
        <template v-for="(item, index) in safeMenu" :key="index">
            <div v-if="item.isDivider" class="border-t border-gray-100"></div>
            
            <a
                v-else-if="item.label"
                href="#"
                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                @click.prevent="handleClick(item, $event)"
            >
                <BaseIcon
                    v-if="item.icon"
                    :path="item.icon"
                    class="w-4 h-4 mr-3"
                />
                {{ item.label }}
            </a>
        </template>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import BaseIcon from '@/components/BaseIcon.vue'

const props = defineProps({
    menu: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['menu-click'])

// Filtrar items válidos
const safeMenu = computed(() => {
    if (!props.menu || !Array.isArray(props.menu)) {
        return []
    }

    return props.menu.filter(item =>
        item && (item.label || item.isDivider)
    )
})

const handleClick = (item, event) => {
    if (item && typeof emit === 'function') {
        emit('menu-click', event, item)
    }
}
</script>
