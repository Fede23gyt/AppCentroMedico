<template>
    <div
        :class="[
      'bg-white rounded-2xl shadow-sm border border-gray-100',
      'dark:bg-gray-800 dark:border-gray-700',
      { 'p-6': !hasTable },
      { 'hover:shadow-md transition-shadow': hover }
    ]"
    >
        <!-- Header de la tarjeta -->
        <header v-if="title || icon || headerIcon" class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <BaseIcon
                    v-if="icon"
                    :path="icon"
                    class="mr-3 text-gray-600 dark:text-gray-400"
                    :size="24"
                />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ title }}
                </h3>
            </div>
            <button
                v-if="headerIcon"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                @click="$emit('header-icon-click')"
            >
                <BaseIcon
                    :path="headerIcon"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    :size="20"
                />
            </button>
        </header>

        <!-- Contenido -->
        <div :class="{ 'p-6': hasTable }">
            <slot />
        </div>
    </div>
</template>

<script setup>
import BaseIcon from '@/components/BaseIcon.vue'

defineProps({
    title: String,
    icon: String,
    headerIcon: String,
    hasTable: Boolean,
    hover: {
        type: Boolean,
        default: false
    }
})

defineEmits(['header-icon-click'])
</script>
