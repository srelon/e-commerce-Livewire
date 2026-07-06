<template>
    <PageBanner title="Notifications" />

    <section class="notifications section">
        <div class="container">
            <template v-if="is_loading">
                <div class="notifications__skeleton">
                    <BaseSkeleton v-for="n in 6" :key="n" height="64px" radius="12px" />
                </div>
            </template>
            <template v-else>
                <p v-if="notifications.length === 0" class="notifications__empty">You have no notifications yet.</p>

                <ul v-else class="notifications__list">
                    <li v-for="n in notifications" :key="n.id">
                        <router-link
                            :to="build_link(n) ?? { name: 'notifications' }"
                            class="notifications__item"
                            :class="{ 'notifications__item--unread': !n.read_at }"
                            @click="on_notification_click(n)"
                        >
                            <span class="notifications__item-label">{{ notification_label(n) }}</span>
                            <span class="notifications__item-date">{{ format_date(n.created_at) }}</span>
                            <span v-if="!n.read_at" class="notifications__item-dot" aria-hidden="true"></span>
                        </router-link>
                    </li>
                </ul>

                <BasePagination
                    v-if="pagination.last_page > 1"
                    :current_page="pagination.current_page"
                    :last_page="pagination.last_page"
                    class="notifications__pagination"
                />
            </template>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import BasePagination from '@/components/ui/base/BasePagination.vue'
import { useNotificationStore } from '@/stores/notification'
import { useNotificationActions } from '@/composables/useNotificationActions'
import type { AppNotification } from '@/types/notification'
import type { Pagination } from '@/types/shop'

const route = useRoute()
const notification_store = useNotificationStore()
const { notification_label, build_link, on_notification_click } = useNotificationActions()

const is_loading = ref(true)
const notifications = ref<AppNotification[]>([])
const pagination = ref<Pagination>({
    current_page: 1,
    last_page: 1,
    total: 0,
})

function format_date(iso: string): string {
    return new Date(iso).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

function fetch(page: number) {
    is_loading.value = true

    return notification_store.fetch_all(page).then((paginated) => {
        notifications.value = paginated.data
        pagination.value = paginated.pagination
    }).finally(() => {
        is_loading.value = false
    })
}

watch(
    () => route.query.page,
    () => fetch(Number(route.query.page) || 1),
    { immediate: true },
)
</script>

<style lang="scss" scoped>
.notifications {
    &__skeleton {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    &__empty {
        color: $color-gray;
        font-size: 14px;
    }

    &__list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 32px;
    }

    &__item {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 16px 20px;
        border: 1px solid $color-light;
        border-radius: 10px;
        transition: border-color 0.2s, box-shadow 0.2s;

        &:hover {
            border-color: $color-lighter;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        }

        &--unread {
            background: rgba($color-primary, 0.05);
        }
    }

    &__item-label {
        font-size: 14px;
        color: $color-dark;
        padding-right: 24px;
    }

    &__item-date {
        font-size: 13px;
        color: $color-gray;
    }

    &__item-dot {
        position: absolute;
        top: 18px;
        right: 18px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: $color-primary;
    }
}
</style>
