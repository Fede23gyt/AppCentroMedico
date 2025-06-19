<template>
    <div v-if="message" class="text-red-600 dark:text-red-400 text-sm mt-2">
        {{ message }}
    </div>
</template>

<script setup>
defineProps({
    message: String,
})
</script>
