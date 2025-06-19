<template>
    <input
        :id="id"
        ref="input"
        :value="modelValue"
        :type="type"
        :required="required"
        :autocomplete="autocomplete"
        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block w-full"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>

<script setup>
import { onMounted, ref } from 'vue'

defineEmits(['update:modelValue'])

defineProps({
    id: String,
    modelValue: String,
    type: {
        type: String,
        default: 'text',
    },
    required: Boolean,
    autocomplete: String,
})

const input = ref(null)

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus()
    }
})

defineExpose({ focus: () => input.value.focus() })
</script>
