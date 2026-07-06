<template>
    <div class="base-tabs">
        <div class="base-tabs__nav">
            <button
                v-for="tab in tabs"
                :key="tab"
                type="button"
                class="base-tabs__btn"
                :class="{ 'base-tabs__btn--active': modelValue === tab }"
                @click="emit('update:modelValue', tab)"
            >
                <slot name="label" :tab="tab">{{ tab }}</slot>
            </button>
        </div>
        <div class="base-tabs__content">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    tabs: string[]
    modelValue: string
}

defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [tab: string]
}>()
</script>

<style lang="scss" scoped>
.base-tabs {
    &__nav {
        display: flex;
        justify-content: center;
        border-bottom: 1px solid $color-light;
        margin-bottom: 32px;
    }

    &__btn {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        font-family: $font-body;
        color: $color-gray;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s;

        &:hover {
            color: $color-dark;
        }

        &--active {
            color: $color-dark;
            border-bottom-color: $color-primary;
        }
    }
}
</style>
