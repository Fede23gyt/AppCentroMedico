<template>
    <div class="relative">
        <input
            :id="id"
            ref="inputElRef"
            :name="name"
            :autocomplete="autocomplete"
            :required="required"
            :placeholder="placeholder"
            :type="inputType"
            :class="inputElClass"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            @focus="$emit('focus', $event)"
            @blur="$emit('blur', $event)"
        >
        <BaseIcon
            v-if="icon"
            :path="icon"
            class="absolute top-3 left-3 text-gray-500"
            w="4"
            h="4"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import BaseIcon from '@/components/BaseIcon.vue'

const props = defineProps({
    name: {
        type: String,
        default: null
    },
    id: {
        type: String,
        default: null
    },
    autocomplete: {
        type: String,
        default: null
    },
    placeholder: {
        type: String,
        default: null
    },
    inputmode: {
        type: String,
        default: null
    },
    icon: {
        type: String,
        default: null
    },
    options: {
        type: Array,
        default: null
    },
    type: {
        type: String,
        default: 'text'
    },
    modelValue: {
        type: [String, Number, Boolean, Array, Object],
        default: ''
    },
    required: Boolean,
    borderless: Boolean,
    transparent: Boolean,
    ctrlKFocus: Boolean
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])

const inputElRef = ref(null)

const computedType = computed(() => props.type)

const inputElClass = computed(() => {
    const base = [
        'px-3 py-2 max-w-full focus-within:z-10 block w-full border-gray-700 rounded transition-colors'
    ]

    if (props.icon) {
        base.push('pl-10')
    }

    if (!props.borderless && !props.transparent) {
        base.push('border')
    }

    if (!props.transparent) {
        base.push('bg-white dark:bg-slate-800')
    }

    if (props.borderless) {
        base.push('border-0')
    }

    base.push('focus:ring focus:ring-blue-600 focus:border-blue-600 focus:ring-opacity-25')

    return base
})

const inputType = computed(() => {
    if (['textarea', 'select'].includes(computedType.value)) {
        return null
    }

    return computedType.value
})

onMounted(() => {
    if (props.ctrlKFocus) {
        const focusHotkey = (e) => {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault()
                inputElRef.value.focus()
            }
        }

        document.addEventListener('keydown', focusHotkey)

        return () => {
            document.removeEventListener('keydown', focusHotkey)
        }
    }
})
</script>
