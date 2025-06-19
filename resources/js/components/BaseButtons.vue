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
import BaseIcon from '@/components/BaseIcon.vue'
import { colorsBgLight, colorsOutline, colorsText } from '@/colors.js'

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
    routeName: {
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
    as: {
        type: String,
        default: null
    },
    small: {
        type: Boolean,
        default: false
    },
    outline: {
        type: Boolean,
        default: false
    },
    active: {
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
    if (props.as) {
        return props.as
    }

    if (props.routeName) {
        return Link
    }

    if (props.href) {
        return 'a'
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
    if (props.href) {
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
        props.roundedFull ? 'rounded-full' : 'rounded',
        props.small ? 'p-1' : 'p-2'
    ]

    if (props.active) {
        base.push('ring ring-black ring-opacity-20')
    }

    if (props.outline) {
        base.push(colorsOutline[props.color])
        base.push(colorsText[props.color])
    } else {
        base.push(colorsBgLight[props.color])
    }

    return base
})

const click = (event) => {
    emit('click', event)
}
</script>
