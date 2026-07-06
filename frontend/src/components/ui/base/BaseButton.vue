<template>
    <button
        class="base-btn"
        :class="[
            `base-btn--${variant}`,
            { 'base-btn--active': active, 'base-btn--danger': danger },
        ]"
        :type="type"
        :disabled="disabled || loading"
    >
        <span v-if="loading" class="base-btn__spinner" aria-hidden="true"></span>
        <slot v-else />
    </button>
</template>

<script setup lang="ts">
interface Props {
    variant?: 'primary' | 'outline' | 'text' | 'dark' | 'primary-outline' | 'chip'
    type?: 'button' | 'submit' | 'reset'
    disabled?: boolean
    loading?: boolean
    active?: boolean
    danger?: boolean
}

withDefaults(defineProps<Props>(), {
    variant: 'primary',
    type: 'button',
    disabled: false,
    loading: false,
    active: false,
    danger: false,
})
</script>

<style lang="scss" scoped>
@use "sass:color";

.base-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 24px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    font-family: $font-body;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;

    &--primary {
        background: $color-primary;
        color: $color-white;
        border: none;

        &:hover {
            background: color.adjust($color-primary, $lightness: -8%);
        }
    }

    &--outline {
        background: transparent;
        color: $color-dark;
        border: 1.5px solid $color-light;

        &:hover {
            background: $color-primary;
            color: $color-white;
            border-color: $color-primary;
        }
    }

    &--text {
        background: none;
        border: none;
        padding: 0;
        color: $color-primary;
        font-size: 13px;

        &:hover {
            color: color.adjust($color-primary, $lightness: -10%);
        }
    }

    &--dark {
        background: $color-dark;
        color: $color-white;
        border: none;
        border-radius: 30px;
        padding: 10px 16px;
        font-size: 14px;

        &:hover {
            background: color.adjust($color-dark, $lightness: 15%);
        }
    }

    &--primary-outline {
        background: transparent;
        color: $color-primary;
        border: 2px solid $color-primary;

        &:hover {
            background: $color-primary;
            color: $color-white;
        }
    }

    &--chip {
        background: transparent;
        border: 1.5px solid $color-light;
        border-radius: 20px;
        color: $color-gray;
        font-size: 13px;
        padding: 6px 10px;
        gap: 5px;

        &:hover {
            border-color: $color-primary;
            color: $color-primary;
        }

        &.base-btn--active {
            color: $color-primary;
            border-color: $color-primary;
            background: rgba($color-primary, 0.08);
        }

        &.base-btn--danger {
            &:hover {
                border-color: $color-danger;
                color: $color-danger;
            }

            &.base-btn--active {
                color: $color-danger;
                border-color: $color-danger;
                background: rgba($color-danger, 0.08);
            }
        }
    }

    &:disabled {
        opacity: 0.5;
        cursor: default;
        pointer-events: none;
    }

    &__spinner {
        width: 16px;
        height: 16px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 50%;
        opacity: 0.8;
        animation: base-btn-spin 0.6s linear infinite;
    }
}

@keyframes base-btn-spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
