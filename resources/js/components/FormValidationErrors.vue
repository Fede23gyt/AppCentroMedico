<template>
    <div v-if="errors && Object.keys(errors).length > 0" class="mb-4">
        <div class="font-medium text-red-600 dark:text-red-400">
            Por favor corrige los siguientes errores:
        </div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
            <li v-for="(error, key) in errors" :key="key">
                {{ error }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const errors = computed(() => page.props.errors)
</script>
