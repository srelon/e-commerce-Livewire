<template>
    <div v-if="items.length" ref="wrap" class="action-menu">
        <button
            type="button"
            class="action-menu__toggle"
            aria-label="More options"
            @click.stop="open = !open"
        >⋯</button>
        <div v-if="open" class="action-menu__list">
            <button
                v-for="item in items"
                :key="item.label"
                type="button"
                class="action-menu__item"
                :class="{ 'action-menu__item--danger': item.danger }"
                @click="on_item_click(item)"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true"><path :d="item.icon" /></svg>
                {{ item.label }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

export interface ActionMenuItem {
    label: string
    icon: string
    danger?: boolean
    onClick: () => void
}

interface Props {
    items: ActionMenuItem[]
}

defineProps<Props>()

const wrap = ref<HTMLElement | null>(null)
const open = ref(false)

function on_item_click(item: ActionMenuItem) {
    open.value = false
    item.onClick()
}

function on_click_outside(e: MouseEvent) {
    if (open.value && !wrap.value?.contains(e.target as Node)) {
        open.value = false
    }
}

onMounted(() => document.addEventListener('click', on_click_outside))
onUnmounted(() => document.removeEventListener('click', on_click_outside))
</script>

<style lang="scss" scoped>
.action-menu {
    position: relative;

    &__toggle {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid $color-light;
        border-radius: 50%;
        font-size: 16px;
        line-height: 1;
        color: $color-gray;
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s;

        &:hover {
            color: $color-dark;
            border-color: $color-dark;
        }
    }

    &__list {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        background: $color-white;
        border: 1px solid $color-light;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        min-width: 130px;
        z-index: 10;
        overflow: hidden;
    }

    &__item {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 10px 14px;
        font-size: 13px;
        font-family: $font-body;
        color: $color-dark;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s;

        svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }

        &:hover {
            background: $color-lightest;
        }

        &--danger {
            color: $color-danger;
        }
    }
}
</style>
