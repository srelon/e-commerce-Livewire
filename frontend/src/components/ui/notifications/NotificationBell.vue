<template>
    <div ref="wrap" class="notification-bell">
        <button type="button" class="notification-bell__btn" aria-label="Notifications" @click="on_toggle">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
            <span v-if="notification_store.unread_count > 0" class="notification-bell__badge">
                {{ notification_store.unread_count > 99 ? '99+' : notification_store.unread_count }}
            </span>
        </button>

        <div v-if="open" class="notification-bell__dropdown">
            <p v-if="is_loading" class="notification-bell__state">Loading...</p>
            <template v-else>
                <p v-if="notification_store.notifications.length === 0" class="notification-bell__state">No notifications yet.</p>
                <ul v-else class="notification-bell__list">
                    <li v-for="n in notification_store.notifications" :key="n.id">
                        <router-link
                            :to="build_link(n) ?? { name: 'notifications' }"
                            class="notification-bell__item"
                            :class="{ 'notification-bell__item--unread': !n.read_at }"
                            @click="on_item_click(n)"
                        >
                            <span class="notification-bell__item-icon" :class="`notification-bell__item-icon--${n.type}`">
                                <svg v-if="n.type === 'reply'" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>
                                <svg v-else-if="n.type === 'like'" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 20h3V9H2v11zm19-9.5c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L11.17 1 5.59 6.59C5.22 6.95 5 7.45 5 8v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                <svg v-else-if="n.type === 'dislike'" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 4h-3v11h3V4zM3 15.5c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L11.83 24l5.58-5.59c.37-.36.59-.86.59-1.41V7c0-1.1-.9-2-2-2h-9c-.83 0-1.54.5-1.84 1.22L1.14 15.27c-.09.23-.14.47-.14.73v1.5z"/></svg>
                                <svg v-else viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                            </span>
                            <span class="notification-bell__item-text">
                                <span class="notification-bell__item-label">{{ notification_label(n) }}</span>
                                <span class="notification-bell__item-date">{{ format_date(n.created_at) }}</span>
                            </span>
                        </router-link>
                    </li>
                </ul>
            </template>

            <router-link :to="{ name: 'notifications' }" class="notification-bell__view-all" @click="open = false">
                View all
            </router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import { useNotificationActions } from '@/composables/useNotificationActions'
import type { AppNotification } from '@/types/notification'

const notification_store = useNotificationStore()
const { notification_label, build_link, on_notification_click } = useNotificationActions()

const wrap = ref<HTMLElement | null>(null)
const open = ref(false)
const is_loading = ref(false)

function format_date(iso: string): string {
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    })
}

function on_toggle() {
    open.value = !open.value

    if (open.value) {
        is_loading.value = true
        notification_store.fetch_notifications().finally(() => {
            is_loading.value = false
        })
    }
}

function on_item_click(n: AppNotification) {
    on_notification_click(n)
    open.value = false
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
.notification-bell {
    position: relative;

    &__btn {
        color: $color-dark;
        display: flex;
        align-items: center;
        position: relative;
        transition: color 0.2s;
        cursor: pointer;

        svg {
            width: 15px;
            height: 15px;
            fill: currentColor;
        }

        &:hover {
            color: $color-primary;
        }
    }

    &__badge {
        position: absolute;
        top: -8px;
        right: -8px;
        min-width: 17px;
        height: 17px;
        padding: 0 4px;
        background: $color-primary;
        color: $color-white;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    &__dropdown {
        position: absolute;
        right: 0;
        top: calc(100% + 14px);
        width: 340px;
        max-width: calc(100vw - 32px);
        background: $color-white;
        border: 1px solid $color-light;
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        z-index: 50;
        overflow: hidden;
    }

    &__state {
        padding: 24px 16px;
        text-align: center;
        font-size: 13px;
        color: $color-gray;
    }

    &__list {
        max-height: 360px;
        overflow-y: auto;
    }

    &__item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 16px;
        transition: background 0.15s;

        &:hover {
            background: $color-lightest;
        }

        &--unread {
            background: rgba($color-primary, 0.05);
        }
    }

    &__item-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: $color-lightest;
        color: $color-gray;

        svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }

        &--reply,
        &--like {
            background: rgba($color-primary, 0.1);
            color: $color-primary;
        }

        &--dislike {
            background: rgba($color-danger, 0.1);
            color: $color-danger;
        }
    }

    &__item-text {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    &__item-label {
        font-size: 13px;
        color: $color-dark;
        line-height: 1.4;
    }

    &__item-date {
        font-size: 12px;
        color: $color-gray;
    }

    &__view-all {
        display: block;
        text-align: center;
        padding: 12px;
        font-size: 13px;
        font-weight: 600;
        color: $color-primary;
        border-top: 1px solid $color-light;

        &:hover {
            background: $color-lightest;
        }
    }
}
</style>
