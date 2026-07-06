<template>
    <div v-if="rating && count !== 0" class="star-rating">
        <span
            v-for="i in max"
            :key="i"
            class="star-rating__star"
            :class="{ 'star-rating__star--filled': i <= rating }"
        >★</span>
        <button
            v-if="count !== undefined"
            type="button"
            class="star-rating__count"
            @click="emit('click-count')"
        >({{ count }} reviews)</button>
    </div>
</template>

<script setup lang="ts">
interface Props {
    rating?: number | null
    count?: number
    max?: number
}

withDefaults(defineProps<Props>(), {
    max: 5,
})

const emit = defineEmits<{
    'click-count': []
}>()
</script>

<style lang="scss" scoped>
.star-rating {
    display: flex;
    align-items: center;
    gap: 2px;

    &__star {
        font-size: 18px;
        color: $color-light;

        &--filled {
            color: #f5a623;
        }
    }

    &__count {
        font-size: 13px;
        color: $color-gray;
        margin-left: 6px;
        background: none;
        border: none;
        padding: 0;
        font-family: $font-body;
        cursor: pointer;
        transition: color 0.2s;

        &:hover {
            color: $color-primary;
        }
    }
}
</style>
