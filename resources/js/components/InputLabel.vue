<template>
    <label :for="for" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
        <slot />
    </label>
</template>

<script setup>
defineProps({
    for: String,
})
</script>
