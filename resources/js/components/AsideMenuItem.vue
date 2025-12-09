<template>
    <li>
        <component
            :is="componentIs"
            :href="item.to"
            :target="item.target ?? null"
            class="flex cursor-pointer transition-colors duration-200 relative group"
            :class="[componentClass, isCollapsed && depth === 0 ? 'justify-center px-2' : '']"
            @click="menuClick"
            :title="isCollapsed && depth === 0 ? item.label : ''"
        >
            <BaseIcon
                v-if="item.icon"
                :path="item.icon"
                :class="['flex-none text-gray-400', isCollapsed && depth === 0 ? '' : 'mr-2']"
                :size="20"
            />
            <span v-if="!isCollapsed || depth > 0" class="grow text-ellipsis line-clamp-1" :class="{ 'submenu-indicator': depth > 0 && !hasDropdown }">
                {{ item.label }}
            </span>
            <BaseIcon
                v-if="hasDropdown && !isCollapsed"
                :path="isDropdownActive ? mdiChevronUp : mdiChevronDown"
                class="flex-none text-gray-400 transition-transform"
                :size="16"
            />

            <!-- Tooltip cuando está colapsado -->
            <div v-if="isCollapsed && depth === 0" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                {{ item.label }}
            </div>
        </component>

        <!-- Submenú (oculto cuando está colapsado) -->
        <div
            v-if="hasDropdown && !isCollapsed"
            :class="[
        'transition-all duration-300 overflow-hidden',
        isDropdownActive ? 'max-h-screen opacity-100' : 'max-h-0 opacity-0'
      ]"
        >
            <ul :class="submenuContainerClass">
                <AsideMenuItem
                    v-for="(subItem, index) in item.menu"
                    :key="index"
                    :item="subItem"
                    :is-collapsed="isCollapsed"
                    is-dropdown-list
                    :depth="depth + 1"
                    @menu-click="$emit('menu-click', $event)"
                />
            </ul>
        </div>
    </li>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { mdiChevronUp, mdiChevronDown } from '@mdi/js'
import BaseIcon from './BaseIcon.vue'

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isDropdownList: {
        type: Boolean,
        default: false
    },
    depth: {
        type: Number,
        default: 0
    },
    isCollapsed: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['menu-click'])

const isDropdownActive = ref(false)

const componentIs = computed(() => {
    if (props.item.to) {
        return Link
    }
    return 'a'
})

const hasDropdown = computed(() => {
    return props.item.menu && Array.isArray(props.item.menu) && props.item.menu.length > 0
})

const menuClick = (event) => {
    if (hasDropdown.value) {
        event.preventDefault()
        isDropdownActive.value = !isDropdownActive.value
    }

    emit('menu-click', event, props.item)
}

const componentClass = computed(() => {
    const base = []

    // Estilos base según el nivel
    if (props.depth === 0) {
        // Nivel principal
        base.push('text-gray-300 hover:text-white px-4 py-2 text-sm')
        if (props.item.to && typeof window !== 'undefined' && window.location.pathname === props.item.to) {
            base.push('text-white')
            // Aplicar color hover inline
            base.push('menu-item-active')
        } else {
            base.push('menu-item-hover')
        }
    } else if (props.depth === 1) {
        // Primer nivel de submenú
        base.push('text-gray-300 px-3 py-1.5 text-xs')
        base.push('border-l-2 border-transparent')
        if (hasDropdown.value) {
            base.push('font-medium text-gray-200')
        }
        if (props.item.to && typeof window !== 'undefined' && window.location.pathname === props.item.to) {
            base.push('text-white submenu-item-active')
        } else {
            base.push('submenu-item-hover')
        }
    } else {
        // Segundo nivel de submenú y más profundos
        base.push('text-gray-400 py-1.5 text-xs')
        base.push('pl-6') // Indentación fija para segundo nivel
        base.push('border-l-2 border-transparent')
        if (props.item.to && typeof window !== 'undefined' && window.location.pathname === props.item.to) {
            base.push('text-white submenu-nested-active')
        } else {
            base.push('submenu-nested-hover')
        }
    }

    return base
})

const submenuContainerClass = computed(() => {
    const base = ['py-1']

    if (props.depth === 0) {
        // Contenedor principal - Tono más oscuro del sidebar
        base.push('border-l-4 ml-2 submenu-container-bg')
    } else {
        // Contenedores anidados
        base.push('border-l-2 border-gray-600 ml-4 submenu-nested-bg')
    }

    return base
})
</script>

<style scoped>
/* Colores basados en #2D6660 */
.menu-item-hover:hover {
    background-color: #245651; /* Tono más oscuro */
}

.menu-item-active {
    background-color: #245651;
}

.submenu-container-bg {
    background-color: #245651; /* Más oscuro que el sidebar principal */
    border-color: #3d8a82; /* Tono más claro para el borde */
}

.submenu-item-hover:hover {
    background-color: #1f4a46; /* Aún más oscuro para hover de submenús */
    color: #fff;
    border-color: #3d8a82;
}

.submenu-item-active {
    background-color: #1f4a46;
    border-color: #3d8a82;
}

.submenu-nested-bg {
    background-color: #1a3f3c; /* El más oscuro para submenús anidados */
}

.submenu-nested-hover:hover {
    background-color: #163532;
    color: #e0f2f1;
    border-color: #3d8a82;
}

.submenu-nested-active {
    background-color: #163532;
    border-color: #3d8a82;
}
</style>
