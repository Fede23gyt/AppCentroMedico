<template>
    <component
        :is="is"
        :class="componentClass"
        :href="href"
        :type="computedType"
        :target="target"
        :disabled="disabled"
        @click="click"
    >
        <BaseIcon
            v-if="icon"
            :path="icon"
            :size="16"
            class="mr-2"
        />
        <span>{{ label }}</span>
        <BaseIcon
            v-if="iconRight"
            :path="iconRight"
            :size="16"
            class="ml-2"
        />
    </component>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import BaseIcon from './BaseIcon.vue'

const props = defineProps({
    label: {
        type: [String, Number],
        default: null
    },
    icon: {
        type: String,
        default: null
    },
    iconRight: {
        type: String,
        default: null
    },
    href: {
        type: String,
        default: null
    },
    type: {
        type: String,
        default: null
    },
    color: {
        type: String,
        default: 'white'
    },
    small: {
        type: Boolean,
        default: false
    },
    outline: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    roundedFull: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['click'])

const is = computed(() => {
    if (props.href) {
        return Link
    }
    return 'button'
})

const computedType = computed(() => {
    if (is.value === 'button') {
        return props.type ?? 'button'
    }
    return null
})

const target = computed(() => {
    if (props.href && props.href.startsWith('http')) {
        return '_blank'
    }
    return null
})

const componentClass = computed(() => {
    const base = [
        'inline-flex',
        'cursor-pointer',
        'justify-center',
        'items-center',
        'whitespace-nowrap',
        'focus:outline-none',
        'transition-colors',
        'focus:ring',
        'duration-150',
        'border',
        props.disabled && 'cursor-not-allowed',
        props.roundedFull ? 'rounded-full' : 'rounded-md',
        props.small ? 'px-2 py-1 text-xs' : 'px-4 py-2 text-sm'
    ]

    // Colores básicos
    if (props.color === 'info') {
        base.push('bg-blue-600 hover:bg-blue-700 text-white border-blue-600')
    } else if (props.color === 'light') {
        base.push('bg-gray-100 hover:bg-gray-200 text-gray-800 border-gray-300')
    } else if (props.color === 'success') {
        base.push('bg-green-600 hover:bg-green-700 text-white border-green-600')
    } else if (props.color === 'danger') {
        base.push('bg-red-600 hover:bg-red-700 text-white border-red-600')
    } else {
        base.push('bg-white hover:bg-gray-50 text-gray-700 border-gray-300')
    }

    if (props.disabled) {
        base.push('opacity-50 cursor-not-allowed')
    }

    return base.filter(Boolean)
})

const click = (event) => {
    if (!props.disabled) {
        emit('click', event)
    }
}
</script>
