<template>
  <span
      :class="[
      'inline-flex items-center rounded-full font-medium',
      colorClass,
      sizeClass
    ]"
  >
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: {
        type: [String, Number],
        required: true
    },
    color: {
        type: String,
        default: 'info'
    },
    small: {
        type: Boolean,
        default: false
    }
})

const colorClass = computed(() => {
    const colors = {
        info: 'bg-blue-500 text-white',
        success: 'bg-emerald-500 text-white',
        warning: 'bg-yellow-500 text-white',
        danger: 'bg-red-500 text-white',
        contrast: 'bg-gray-500 text-white'
    }

    return colors[props.color] || colors.info
})

const sizeClass = computed(() => {
    return props.small ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1'
})
</script>
