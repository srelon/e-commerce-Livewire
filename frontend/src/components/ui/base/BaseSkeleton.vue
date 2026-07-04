<template>
    <span
        class="base-skeleton"
        :style="{
            width,
            height,
            borderRadius: circle ? '50%' : radius,
        }"
    ></span>
</template>

<script setup lang="ts">
interface Props {
    width?: string
    height?: string
    radius?: string
    circle?: boolean
}

withDefaults(defineProps<Props>(), {
    width: '100%',
    height: '100%',
    radius: '6px',
    circle: false,
})
</script>

<style lang="scss" scoped>
.base-skeleton {
    display: block;
    background: linear-gradient(90deg, $color-light 25%, $color-lightest 37%, $color-light 63%);
    background-size: 400% 100%;
    animation: base-skeleton-shimmer 1.4s ease infinite;
}

@keyframes base-skeleton-shimmer {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0 50%;
    }
}
</style>
