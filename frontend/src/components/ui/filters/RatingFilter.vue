<template>
    <div class="rating-filter">
        <button
            v-for="i in 5"
            :key="i"
            class="rating-filter__star"
            :class="{ 'rating-filter__star--filled': i <= (hovered ?? modelValue ?? 0) }"
            @click="on_click(i)"
            @mouseenter="hovered = i"
            @mouseleave="hovered = null"
            aria-label="`${i} stars`"
        >★</button>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Props {
    modelValue?: number | null
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
})

const emit = defineEmits<{
    'update:modelValue': [val: number | null]
}>()

const hovered = ref<number | null>(null)

function on_click(i: number) {
    emit('update:modelValue', props.modelValue === i ? null : i)
}
</script>

<style lang="scss" scoped>
.rating-filter {
    display: flex;
    gap: 4px;

    &__star {
        font-size: 24px;
        color: $color-light;
        cursor: pointer;
        transition: color 0.15s, transform 0.1s;
        line-height: 1;

        &:hover,
        &--filled {
            color: #f5a623;
        }

        &:active {
            transform: scale(0.88);
        }
    }
}
</style>
