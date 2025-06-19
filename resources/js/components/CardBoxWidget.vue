<template>
    <CardBox hover>
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div :class="iconColorClass" class="w-12 h-12 rounded-xl flex items-center justify-center">
                    <BaseIcon
                        :path="icon"
                        :class="iconTextClass"
                        :size="24"
                    />
                </div>
            </div>
            <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ label }}
                        </p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ prefix }}{{ formattedNumber }}{{ suffix }}
                            </p>
                        </div>
                    </div>
                    <div v-if="trend" class="text-right">
                        <div :class="trendColorClass" class="flex items-center text-sm font-medium">
                            <BaseIcon
                                :path="trendIcon"
                                class="mr-1"
                                :size="16"
                            />
                            {{ trend }}
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            vs mes anterior
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </CardBox>
</template>

<script setup>
import { computed } from 'vue'
import { mdiTrendingUp, mdiTrendingDown, mdiMinus } from '@mdi/js'
import BaseIcon from '@/components/BaseIcon.vue'
import CardBox from '@/components/CardBox.vue'

const props = defineProps({
    icon: {
        type: String,
        required: true
    },
    iconColor: {
        type: String,
        default: 'blue'
    },
    number: {
        type: [String, Number],
        required: true
    },
    prefix: {
        type: String,
        default: ''
    },
    suffix: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        required: true
    },
    trend: String,
    trendType: {
        type: String,
        default: 'up' // up, down, stable
    }
})

const formattedNumber = computed(() => {
    const num = Number(props.number)
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M'
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K'
    }
    return num.toLocaleString()
})

const iconColorClass = computed(() => {
    const colors = {
        blue: 'bg-blue-100 dark:bg-blue-900/20',
        green: 'bg-green-100 dark:bg-green-900/20',
        purple: 'bg-purple-100 dark:bg-purple-900/20',
        orange: 'bg-orange-100 dark:bg-orange-900/20',
        red: 'bg-red-100 dark:bg-red-900/20',
        indigo: 'bg-indigo-100 dark:bg-indigo-900/20'
    }
    return colors[props.iconColor] || colors.blue
})

const iconTextClass = computed(() => {
    const colors = {
        blue: 'text-blue-600 dark:text-blue-400',
        green: 'text-green-600 dark:text-green-400',
        purple: 'text-purple-600 dark:text-purple-400',
        orange: 'text-orange-600 dark:text-orange-400',
        red: 'text-red-600 dark:text-red-400',
        indigo: 'text-indigo-600 dark:text-indigo-400'
    }
    return colors[props.iconColor] || colors.blue
})

const trendIcon = computed(() => {
    switch (props.trendType) {
        case 'up': return mdiTrendingUp
        case 'down': return mdiTrendingDown
        default: return mdiMinus
    }
})

const trendColorClass = computed(() => {
    switch (props.trendType) {
        case 'up': return 'text-green-600 dark:text-green-400'
        case 'down': return 'text-red-600 dark:text-red-400'
        default: return 'text-gray-600 dark:text-gray-400'
    }
})
</script>
