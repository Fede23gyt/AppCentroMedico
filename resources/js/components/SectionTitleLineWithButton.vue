<template>
    <section class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <BaseIcon
                v-if="icon"
                :path="icon"
                class="mr-3 text-gray-600 dark:text-gray-400"
                :size="32"
            />
            <div>
                <h1 :class="{ 'text-3xl': main, 'text-xl': !main }" class="font-bold text-gray-900 dark:text-white">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ subtitle }}
                </p>
            </div>
        </div>
        <div v-if="$slots.default">
            <slot />
        </div>
    </section>
</template>

<script setup>
import BaseIcon from '@/components/BaseIcon.vue'

defineProps({
    icon: String,
    title: {
        type: String,
        required: true
    },
    subtitle: String,
    main: Boolean
})
</script>
