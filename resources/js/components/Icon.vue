<!-- resources/js/Components/Icon.vue -->
<template>
    <svg
        class="inline-block"
        :class="[
            size ? '' : 'w-5 h-5',
            spin ? 'animate-spin' : '',
            $attrs.class
        ]"
        :style="{
            width: size ? (typeof size === 'number' ? size + 'px' : size) : undefined,
            height: size ? (typeof size === 'number' ? size + 'px' : size) : undefined,
            color: color || undefined,
            transform: rotate ? `rotate(${typeof rotate === 'number' ? rotate + 'deg' : rotate})` : undefined
        }"
        viewBox="0 0 24 24"
        fill="currentColor"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path :d="path" />
    </svg>
</template>

<script setup>
defineProps({
    path: {
        type: String,
        required: true
    },
    size: {
        type: [String, Number],
        default: null
    },
    color: {
        type: String,
        default: null
    },
    spin: {
        type: Boolean,
        default: false
    },
    rotate: {
        type: [String, Number],
        default: null
    }
})
</script>

<style scoped>
svg {
    transition: transform 0.2s ease-in-out;
}
</style>
