<!-- resources/js/components/BaseIcon.vue -->
<template>
    <svg
        viewBox="0 0 24 24"
        :class="computedClass"
        :style="computedStyle"
    >
        <path
            fill="currentColor"
            :d="path"
        />
    </svg>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    path: {
        type: String,
        required: true
    },
    size: {
        type: [String, Number],
        default: 16
    }
})

const computedClass = computed(() => {
    return `w-${props.size} h-${props.size}`
})

const computedStyle = computed(() => {
    const size = parseInt(props.size)
    return {
        width: `${size}px`,
        height: `${size}px`
    }
})
</script>
