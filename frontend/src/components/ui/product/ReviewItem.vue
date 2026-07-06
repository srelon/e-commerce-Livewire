<template>
    <div :id="`review-${review.id}`" class="review-item" :class="{ 'review-item--reply': is_reply }">
        <div class="review-item__head">
            <div class="review-item__avatar">
                <img v-if="review.user.avatar" :src="to_storage_url(review.user.avatar)" :alt="review.user.name ?? ''">
                <span v-else>{{ (review.user.name ?? '?').charAt(0).toUpperCase() }}</span>
            </div>
            <div class="review-item__meta">
                <p class="review-item__author">{{ review.user.name }}</p>
                <StarRating v-if="!is_reply && review.rating !== null" :rating="review.rating" />
            </div>
            <span class="review-item__date">{{ format_date(review.created_at) }}</span>
        </div>

        <BaseButton
            v-if="is_reply && review.replied_to && !is_editing_reply"
            variant="text"
            class="review-item__replied-to"
            @click="scroll_to_review(review.replied_to.id)"
        >
            ↳ Replying to {{ review.replied_to.user.name }}
        </BaseButton>

        <ReviewForm
            v-if="is_reply && is_editing_reply"
            :initial_body="review.body"
            :is_editing="true"
            :is_submitting="is_submitting_edit"
            :show_rating="false"
            title="Edit Reply"
            submit_label="Update Reply"
            @submit="on_edit_submit"
            @cancel="is_editing_reply = false"
        />
        <div v-else class="review-item__body" v-html="review.body"></div>

        <div v-if="!(is_reply && is_editing_reply)" class="review-item__footer">
            <BaseButton variant="text" class="review-item__reply" @click="on_reply_click">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>
                Reply
            </BaseButton>

            <div class="review-item__right">
                <BaseButton
                    variant="chip"
                    class="review-item__reaction"
                    :active="review.my_reaction === 'like'"
                    @click="on_react('like')"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 20h3V9H2v11zm19-9.5c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L11.17 1 5.59 6.59C5.22 6.95 5 7.45 5 8v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                    <span>{{ review.likes_count }}</span>
                </BaseButton>
                <BaseButton
                    variant="chip"
                    danger
                    class="review-item__reaction"
                    :active="review.my_reaction === 'dislike'"
                    @click="on_react('dislike')"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 4h-3v11h3V4zM3 15.5c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L11.83 24l5.58-5.59c.37-.36.59-.86.59-1.41V7c0-1.1-.9-2-2-2h-9c-.83 0-1.54.5-1.84 1.22L1.14 15.27c-.09.23-.14.47-.14.73v1.5z"/></svg>
                    <span>{{ review.dislikes_count }}</span>
                </BaseButton>

                <BaseActionMenu :items="menu_items" />
                <span v-if="reported" class="review-item__reported-badge">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path :d="REPORT_ICON"/></svg>
                    Reported
                </span>
            </div>
        </div>

        <div v-if="show_report" class="review-item__report">
            <div class="review-item__report-head">
                <span>Report this review</span>
                <button type="button" class="review-item__report-close" aria-label="Close" @click="show_report = false">×</button>
            </div>
            <PlainSelect v-model="report_reason" :options="report_reason_options" />
            <BaseButton
                variant="outline"
                :disabled="!report_reason"
                :loading="is_submitting_report"
                @click="on_report_submit"
            >
                Submit Report
            </BaseButton>
        </div>

        <template v-if="!is_reply">
            <BaseButton
                v-if="show_replies || review.replies_count > 0"
                variant="text"
                class="review-item__toggle-replies"
                @click="show_replies = !show_replies"
            >
                {{ show_replies
                    ? 'Hide replies'
                    : `View ${review.replies_count} ${review.replies_count === 1 ? 'reply' : 'replies'}` }}
            </BaseButton>

            <div v-if="show_replies" class="review-item__replies">
                <ReviewItem
                    v-for="reply in review.replies"
                    :key="reply.id"
                    :review="reply"
                    :product_slug="product_slug"
                    is_reply
                    @reply-to-me="on_reply_to_me(reply)"
                />

                <div v-if="auth_store.is_authenticated" ref="compose_wrap">
                    <ReviewForm
                        :key="reply_form_key"
                        :is_submitting="is_submitting_reply"
                        :show_rating="false"
                        title="Add a Reply"
                        submit_label="Post Reply"
                        :reply_to_name="replying_to?.user.name ?? undefined"
                        @submit="on_reply_submit"
                        @clear-reply-to="replying_to = null"
                    />
                </div>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { useToast } from 'vue-toastification'
import StarRating from '@/components/ui/base/StarRating.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import BaseActionMenu from '@/components/ui/base/BaseActionMenu.vue'
import PlainSelect from '@/components/ui/base/PlainSelect.vue'
import ReviewForm from '@/components/ui/product/ReviewForm.vue'
import type { ActionMenuItem } from '@/components/ui/base/BaseActionMenu.vue'
import { to_storage_url } from '@/stores/layout'
import { useAuthStore } from '@/stores/auth'
import { useReviewAnchor } from '@/composables/useReviewAnchor'
import { useReviewApi } from '@/composables/useReviewApi'
import type { Review } from '@/types/review'

const REPORT_REASONS = ['Spam', 'Harassment', 'Hate speech', 'Misinformation', 'Inappropriate content', 'Other']
const REPORT_ICON = 'M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z'

interface Props {
    review: Review
    product_slug: string
    is_reply?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    is_reply: false,
})

const emit = defineEmits<{
    react: [type: 'like' | 'dislike']
    edit: []
    delete: []
    'reply-to-me': []
}>()

const auth_store = useAuthStore()
const { scroll_to_review } = useReviewAnchor()
const { create_review, update_review, delete_review, react_to_review, report_review } = useReviewApi()

const show_replies = ref(false)
const compose_wrap = ref<HTMLElement | null>(null)
const is_editing_reply = ref(false)
const is_submitting_edit = ref(false)
const is_submitting_reply = ref(false)
const reply_form_key = ref(0)
const replying_to = ref<Review | null>(null)
const show_report = ref(false)
const report_reason = ref('')
const is_submitting_report = ref(false)
const reported = ref(false)

const report_reason_options = computed(() => [
    { value: '', label: 'Select reason' },
    ...REPORT_REASONS.map((reason) => ({ value: reason, label: reason })),
])

const menu_items = computed<ActionMenuItem[]>(() => {
    const items: ActionMenuItem[] = []

    if (props.review.can_edit) {
        items.push({
            label: 'Edit',
            icon: 'M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z',
            onClick: on_edit,
        })
    }

    if (props.review.can_delete) {
        items.push({
            label: 'Delete',
            icon: 'M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z',
            danger: true,
            onClick: on_delete,
        })
    }

    if (!props.review.can_delete && !reported.value) {
        items.push({
            label: 'Report',
            icon: REPORT_ICON,
            danger: true,
            onClick: on_report_click,
        })
    }

    return items
})

function format_date(iso: string): string {
    return new Date(iso).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

function require_auth(): boolean {
    if (auth_store.is_authenticated) return true

    auth_store.open_modal('login')
    return false
}

function on_reply_click() {
    if (props.is_reply) {
        emit('reply-to-me')
        return
    }

    if (!require_auth()) return

    show_replies.value = true
    scroll_to_compose()
}

function on_reply_to_me(reply: Review) {
    if (!require_auth()) return

    replying_to.value = reply
    show_replies.value = true
    scroll_to_compose()
}

function scroll_to_compose() {
    nextTick(() => {
        compose_wrap.value?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    })
}

function on_open_replies(e: Event) {
    const { parent_id, scroll_to } = (e as CustomEvent<{ parent_id: number, scroll_to: number }>).detail
    if (parent_id !== props.review.id) return

    show_replies.value = true
    nextTick(() => scroll_to_review(scroll_to))
}

function on_react(type: 'like' | 'dislike') {
    if (props.is_reply) {
        react_to_review(props.product_slug, props.review.id, type)
    } else {
        emit('react', type)
    }
}

function on_edit() {
    if (props.is_reply) {
        is_editing_reply.value = true
    } else {
        emit('edit')
    }
}

function on_delete() {
    if (props.is_reply) {
        delete_review(props.product_slug, props.review.id)
    } else {
        emit('delete')
    }
}

function on_report_click() {
    if (!require_auth()) return

    report_reason.value = ''
    show_report.value = true
}

function on_report_submit() {
    if (!report_reason.value || is_submitting_report.value) return

    is_submitting_report.value = true
    report_review(props.product_slug, props.review.id, report_reason.value)
        .then(() => {
            reported.value = true
            show_report.value = false
            useToast().success('Report submitted.')
        })
        .finally(() => {
            is_submitting_report.value = false
        })
}

function on_edit_submit(payload: { rating: number | null, body: string }) {
    is_submitting_edit.value = true
    update_review(props.product_slug, props.review.id, { body: payload.body })
        .then(() => {
            is_editing_reply.value = false
        })
        .finally(() => {
            is_submitting_edit.value = false
        })
}

function on_reply_submit(payload: { rating: number | null, body: string }) {
    is_submitting_reply.value = true
    create_review(props.product_slug, {
        parent_id: props.review.id,
        replied_to_comment_id: replying_to.value?.id ?? null,
        body: payload.body,
    })
        .then(() => {
            reply_form_key.value += 1
            replying_to.value = null
        })
        .finally(() => {
            is_submitting_reply.value = false
        })
}

onMounted(() => {
    if (!props.is_reply) window.addEventListener('review:open-replies', on_open_replies)
})

onUnmounted(() => {
    if (!props.is_reply) window.removeEventListener('review:open-replies', on_open_replies)
})
</script>

<style lang="scss" scoped>
.review-item {
    padding: 20px 24px;
    border: 1px solid $color-light;
    border-radius: 12px;
    transition: box-shadow 0.2s, border-color 0.2s;

    &:hover {
        border-color: $color-lighter;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
    }

    &--reply {
        padding: 16px 18px;
        border-color: $color-light;

        &:hover {
            border-color: $color-light;
            box-shadow: none;
        }
    }

    &--flash {
        animation: review-item-flash 1.2s ease;
    }

    &__replied-to {
        margin-bottom: 8px;
    }

    &__toggle-replies {
        display: block;
        width: 100%;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid $color-light;
        text-align: center;
    }

    &__replies {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 14px;
        padding-left: 20px;
        border-left: 2px solid $color-light;
    }

    &__head {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    &__avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        overflow: hidden;
        background: $color-primary;
        color: $color-white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 17px;
        flex-shrink: 0;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    &__meta {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    &__author {
        font-size: 14px;
        font-weight: 600;
        color: $color-dark;
        margin-bottom: 2px;
    }

    &__date {
        font-size: 13px;
        color: $color-gray;
        flex-shrink: 0;
    }

    &__body {
        font-size: 14px;
        color: $color-dark;
        line-height: 1.7;
        margin-bottom: 12px;
        word-break: break-word;
    }

    &__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    &__reply {
        gap: 6px;

        svg {
            width: 15px;
            height: 15px;
            fill: currentColor;
        }
    }

    &__right {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    &__reaction svg {
        width: 14px;
        height: 14px;
        fill: currentColor;
    }

    &__report {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 14px;
        padding: 16px;
        background: $color-lightest;
        border-radius: 8px;
    }

    &__report-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 14px;
        font-weight: 600;
        color: $color-dark;
    }

    &__report-close {
        font-size: 18px;
        line-height: 1;
        color: $color-gray;
        cursor: pointer;

        &:hover {
            color: $color-dark;
        }
    }

    &__reported-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        font-weight: 600;
        color: $color-danger;

        svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }
    }
}

@keyframes review-item-flash {
    0%, 100% {
        background: transparent;
    }
    30% {
        background: rgba($color-primary, 0.12);
    }
}
</style>
