<template>
    <div
        class="flex items-center"
        :class="[display, { 'mx-2': useMargin }]"
    >
        <slot />
    </div>
</template>

<script setup>
defineProps({
    display: {
        type: String,
        default: 'flex'
    },
    useMargin: Boolean
})
</script>
