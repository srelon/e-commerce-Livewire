<template>
    <div class="reviews">
        <template v-if="is_loading">
            <div class="reviews__skeleton">
                <BaseSkeleton height="18px" width="60%" />
                <BaseSkeleton height="14px" width="90%" />
                <BaseSkeleton height="14px" width="80%" />
            </div>
        </template>
        <template v-else>
            <div class="reviews__columns">
                <div class="reviews__list-col">
                    <h3 class="reviews__list-title">
                        <template v-if="product_reviews_count > 0">
                            {{ product_reviews_count }} {{ product_reviews_count === 1 ? 'review' : 'reviews' }} for {{ product_title }}
                        </template>
                        <template v-else>Reviews</template>
                    </h3>

                    <div v-if="product_reviews_count > 0" class="reviews__summary">
                        <div class="reviews__summary-left">
                            <div class="reviews__summary-head">
                                <span class="reviews__summary-score">{{ product_rating!.toFixed(1) }}</span>
                                <div class="reviews__summary-headmeta">
                                    <StarRating :rating="product_rating" />
                                    <span class="reviews__summary-count">
                                        ({{ product_reviews_count }} customer {{ product_reviews_count === 1 ? 'review' : 'reviews' }})
                                    </span>
                                </div>
                            </div>
                            <p class="reviews__summary-recommend">
                                {{ recommend_percent }}% of customers recommend this product.
                            </p>
                        </div>

                        <div class="reviews__breakdown">
                            <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="reviews__breakdown-row">
                                <span class="reviews__breakdown-label">{{ star }} {{ star === 1 ? 'Star' : 'Stars' }}</span>
                                <div class="reviews__breakdown-bar">
                                    <div class="reviews__breakdown-fill" :style="{ width: breakdown_percent(star) + '%' }"></div>
                                </div>
                                <span class="reviews__breakdown-percent">{{ breakdown_percent(star) }}%</span>
                            </div>
                        </div>
                    </div>

                    <p v-if="reviews.length === 0" class="reviews__empty">There are no reviews yet. Be the first to write one.</p>

                    <ReviewsList
                        v-else
                        :reviews="reviews"
                        :product_slug="product_slug"
                        @react="on_react"
                        @edit="on_edit_click"
                        @delete="on_delete"
                    />

                    <div v-if="pagination.current_page < pagination.last_page" class="reviews__load-more">
                        <BaseButton variant="text" :loading="is_loading_more" @click="load_more">
                            Load More Reviews
                        </BaseButton>
                    </div>
                </div>

                <div class="reviews__form-col">
                    <h3 class="reviews__form-col-title">{{ viewer_review ? 'Your review' : 'Add a review' }}</h3>

                    <div v-if="!auth_store.is_authenticated" class="reviews__login">
                        <p>Please log in to write a review.</p>
                        <BaseButton variant="outline" @click="auth_store.open_modal('login')">Log In</BaseButton>
                    </div>
                    <ReviewItem
                        v-else-if="viewer_review && !is_editing"
                        :review="viewer_review!"
                        :product_slug="product_slug"
                        @react="(type) => on_react(viewer_review!, type)"
                        @edit="on_edit_click(viewer_review!)"
                        @delete="on_delete(viewer_review!.id)"
                    />
                    <ReviewForm
                        v-else
                        :key="is_editing && viewer_review ? `edit-${viewer_review.id}` : 'create'"
                        :initial_rating="is_editing ? (viewer_review?.rating ?? 0) : 0"
                        :initial_body="is_editing ? viewer_review?.body : ''"
                        :is_editing="is_editing"
                        :is_submitting="is_submitting"
                        @submit="on_form_submit"
                        @cancel="is_editing = false"
                    />
                </div>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import ReviewsList from '@/components/ui/product/ReviewsList.vue'
import ReviewItem from '@/components/ui/product/ReviewItem.vue'
import ReviewForm from '@/components/ui/product/ReviewForm.vue'
import StarRating from '@/components/ui/base/StarRating.vue'
import api from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'
import { useWebsocketStore } from '@/stores/websocket'
import { useReviewAnchor } from '@/composables/useReviewAnchor'
import { useReviewApi } from '@/composables/useReviewApi'
import type { Review, ViewerReview, RatingBreakdown } from '@/types/review'
import type { Pagination } from '@/types/shop'

interface Props {
    product_slug: string
    product_title: string
    product_rating: number | null
    product_reviews_count: number
}

const props = defineProps<Props>()

const route = useRoute()
const auth_store = useAuthStore()
const websocket_store = useWebsocketStore()
const { scroll_to_review, open_replies_and_scroll } = useReviewAnchor()
const { create_review, update_review, delete_review, react_to_review } = useReviewApi()

const is_loading = ref(true)
const is_loading_more = ref(false)
const is_submitting = ref(false)
const is_editing = ref(false)
const reviews = ref<Review[]>([])
const viewer_review = ref<ViewerReview | null>(null)
const pagination = ref<Pagination>({
    current_page: 1,
    last_page: 1,
    total: 0,
})
const rating_breakdown = ref<RatingBreakdown>({
    1: 0,
    2: 0,
    3: 0,
    4: 0,
    5: 0,
})

const channel = computed(() => `reviews.products.${props.product_slug}`)

const pin_id = computed<number | null>(() => {
    const pin = route.query.pin
    return pin ? parseInt(String(pin), 10) : null
})

const breakdown_total = computed(() => Object.values(rating_breakdown.value).reduce((sum, count) => sum + count, 0))

const recommend_percent = computed(() => {
    if (!breakdown_total.value) return 0

    return Math.round((rating_breakdown.value[4] + rating_breakdown.value[5]) / breakdown_total.value * 100)
})

function breakdown_percent(star: number): number {
    if (!breakdown_total.value) return 0

    return Math.round((rating_breakdown.value[star as 1 | 2 | 3 | 4 | 5] ?? 0) / breakdown_total.value * 100)
}

function fetch_reviews(page = 1, append = false) {
    const params: Record<string, number> = { page }
    if (pin_id.value) params.pin = pin_id.value

    return api.get(`products/${props.product_slug}/reviews`, { params }).then((res) => {
        const items = res.data.data.items
        reviews.value = append ? [...reviews.value, ...items.data] : items.data
        pagination.value = items.pagination
        viewer_review.value = res.data.data.viewer_review
        rating_breakdown.value = res.data.data.rating_breakdown
    })
}

function load_more() {
    is_loading_more.value = true
    fetch_reviews(pagination.value.current_page + 1, true).finally(() => {
        is_loading_more.value = false
    })
}

function on_edit_click(review: Review) {
    viewer_review.value = review
    is_editing.value = true
}

function on_form_submit(payload: { rating: number | null, body: string }) {
    is_submitting.value = true
    const request = is_editing.value && viewer_review.value
        ? update_review(props.product_slug, viewer_review.value.id, payload)
        : create_review(props.product_slug, payload)

    request.then(() => {
        is_editing.value = false

        return fetch_reviews(1)
    }).finally(() => {
        is_submitting.value = false
    })
}

function on_delete(review_id: number) {
    if (viewer_review.value?.id === review_id) is_editing.value = false

    delete_review(props.product_slug, review_id).then(() => fetch_reviews(1))
}

function on_react(review: Review, type: 'like' | 'dislike') {
    react_to_review(props.product_slug, review.id, type)
}

function find_review(id: number): Review | ViewerReview | undefined {
    if (viewer_review.value?.id === id) return viewer_review.value

    const reply_in_viewer_review = viewer_review.value?.replies.find((r) => r.id === id)
    if (reply_in_viewer_review) return reply_in_viewer_review

    for (const review of reviews.value) {
        if (review.id === id) return review

        const reply = review.replies.find((r) => r.id === id)
        if (reply) return reply
    }

    return undefined
}

function on_review_created(e: Event) {
    const data = (e as CustomEvent).detail

    if (data.parent_id) {
        const parent = find_review(data.parent_id)
        if (parent) {
            parent.replies.push(data)
            parent.replies_count += 1
        }
        return
    }

    if (data.id === viewer_review.value?.id) return
    reviews.value.unshift(data)
    pagination.value.total += 1
}

function on_review_updated(e: Event) {
    const data = (e as CustomEvent).detail
    const existing = find_review(data.id)
    if (existing) Object.assign(existing, data)
}

function on_review_deleted(e: Event) {
    const data = (e as CustomEvent).detail

    if (data.parent_id) {
        const parent = find_review(data.parent_id)
        if (parent) {
            parent.replies = parent.replies.filter((r) => r.id !== data.id)
            parent.replies_count = Math.max(0, parent.replies_count - 1)
        }
        return
    }

    reviews.value = reviews.value.filter((r) => r.id !== data.id)
    pagination.value.total = Math.max(0, pagination.value.total - 1)
}

function on_review_liked(e: Event) {
    const data = (e as CustomEvent).detail
    const review = find_review(data.review_id)
    if (!review) return

    review.likes_count = data.likes_count
    review.dislikes_count = data.dislikes_count

    if (data.public_id === auth_store.user?.public_id) {
        review.my_reaction = data.reaction
    }
}

function scroll_to_anchor() {
    if (!route.hash) return

    const target_id = parseInt(route.hash.replace('#review-', ''), 10)
    if (Number.isNaN(target_id)) return

    if (document.getElementById(`review-${target_id}`)) {
        scroll_to_review(target_id)
    } else if (pin_id.value) {
        open_replies_and_scroll(pin_id.value, target_id)
    }
}

function reset_state() {
    is_editing.value = false
    reviews.value = []
    viewer_review.value = null
    pagination.value = {
        current_page: 1,
        last_page: 1,
        total: 0,
    }
    rating_breakdown.value = {
        1: 0,
        2: 0,
        3: 0,
        4: 0,
        5: 0,
    }
}

function load_reviews() {
    is_loading.value = true
    fetch_reviews().finally(() => {
        is_loading.value = false
        nextTick(() => scroll_to_anchor())
    })
}

watch(() => auth_store.is_authenticated, () => {
    is_editing.value = false
    fetch_reviews(1)
})

watch(
    () => [String(route.query.pin ?? ''), route.hash],
    ([new_pin, new_hash], [old_pin, old_hash]) => {
        if (new_pin !== old_pin) {
            fetch_reviews(1).then(() => nextTick(() => scroll_to_anchor()))
        } else if (new_hash !== old_hash && new_hash) {
            nextTick(() => scroll_to_anchor())
        }
    },
)

watch(() => props.product_slug, (new_slug, old_slug) => {
    if (new_slug === old_slug) return

    websocket_store.unsubscribe(`reviews.products.${old_slug}`)
    websocket_store.subscribe(`reviews.products.${new_slug}`)

    reset_state()
    load_reviews()
})

onMounted(() => {
    load_reviews()
    websocket_store.subscribe(channel.value)
    window.addEventListener('ws:review.created', on_review_created)
    window.addEventListener('ws:review.updated', on_review_updated)
    window.addEventListener('ws:review.deleted', on_review_deleted)
    window.addEventListener('ws:review.liked', on_review_liked)
})

onUnmounted(() => {
    websocket_store.unsubscribe(channel.value)
    window.removeEventListener('ws:review.created', on_review_created)
    window.removeEventListener('ws:review.updated', on_review_updated)
    window.removeEventListener('ws:review.deleted', on_review_deleted)
    window.removeEventListener('ws:review.liked', on_review_liked)
})
</script>

<style lang="scss" scoped>
.reviews {
    &__skeleton {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }

    &__columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: start;

        @media (max-width: 900px) {
            grid-template-columns: 1fr;
        }
    }

    &__list-title {
        font-size: 18px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 20px;
    }

    &__summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 40px;
        padding: 28px 4px;
        margin-bottom: 28px;
        border-top: 1px solid $color-light;
        border-bottom: 1px solid $color-light;

        @media (max-width: 560px) {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }

    &__summary-left {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    &__summary-head {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    &__summary-score {
        font-size: 44px;
        font-weight: 700;
        color: $color-dark;
        line-height: 1;
    }

    &__summary-headmeta {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    &__summary-count {
        font-size: 13px;
        color: $color-gray;
    }

    &__summary-recommend {
        font-size: 15px;
        color: $color-dark;
        line-height: 1.5;
    }

    &__breakdown {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    &__breakdown-row {
        display: grid;
        grid-template-columns: 64px 1fr 36px;
        align-items: center;
        gap: 10px;
    }

    &__breakdown-label {
        font-size: 12px;
        color: $color-gray;
    }

    &__breakdown-bar {
        height: 6px;
        border-radius: 3px;
        background: $color-light;
        overflow: hidden;
    }

    &__breakdown-fill {
        height: 100%;
        background: $color-primary;
        border-radius: 3px;
        transition: width 0.3s;
    }

    &__breakdown-percent {
        font-size: 12px;
        color: $color-gray;
        text-align: right;
    }

    &__empty {
        margin-bottom: 24px;
        color: $color-gray;
        font-size: 14px;
    }

    &__load-more {
        text-align: center;
        margin-bottom: 24px;
    }

    &__form-col-title {
        font-size: 18px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 20px;
    }

    &__login {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 16px;
        padding: 24px;
        background: $color-lightest;
        border-radius: 12px;

        p {
            color: $color-gray;
            font-size: 14px;
        }
    }
}
</style>
