<template>
    <div class="reviews-list">
        <ReviewItem
            v-for="review in reviews"
            :key="review.id"
            :review="review"
            :product_slug="product_slug"
            @react="(type) => emit('react', review, type)"
            @edit="emit('edit', review)"
            @delete="emit('delete', review.id)"
        />
    </div>
</template>

<script setup lang="ts">
import ReviewItem from '@/components/ui/product/ReviewItem.vue'
import type { Review } from '@/types/review'

interface Props {
    reviews: Review[]
    product_slug: string
}

defineProps<Props>()

const emit = defineEmits<{
    react: [review: Review, type: 'like' | 'dislike']
    edit: [review: Review]
    delete: [review_id: number]
}>()
</script>

<style lang="scss" scoped>
.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin-bottom: 24px;
}
</style>
