<template>
    <div class="list-bar">
        <div class="list-bar__left">
            <BaseSkeleton v-if="loading" width="130px" height="14px" />
            <p v-else class="list-bar__title">{{ title }}</p>
            <slot />
        </div>
        <slot name="right" />
    </div>
</template>

<script setup lang="ts">
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'

interface Props {
    title: string
    loading?: boolean
}

withDefaults(defineProps<Props>(), {
    loading: false,
})
</script>

<style lang="scss" scoped>
.list-bar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid $color-light;

    &__left {
        display: flex;
        align-items: flex-start;
        flex-wrap: nowrap;
        gap: 16px;
        min-width: 0;
    }

    &__title {
        width: 130px;
        flex-shrink: 0;
        font-size: 14px;
        color: $color-gray;
        white-space: nowrap;
    }
}
</style>
