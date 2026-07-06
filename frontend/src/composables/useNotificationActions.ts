import type { RouteLocationRaw } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import type { AppNotification } from '@/types/notification'

export function useNotificationActions() {
    const notification_store = useNotificationStore()

    function notification_label(notification: AppNotification): string {
        const from_name = notification.data.from_name ?? 'Someone'

        switch (notification.type) {
            case 'reply':
                return `${from_name} replied to your review`
            case 'like':
                return `${from_name} liked your review`
            case 'dislike':
                return `${from_name} disliked your review`
            case 'system':
                return notification.data.message ?? 'System notification'
            default:
                return ''
        }
    }

    function build_link(notification: AppNotification): RouteLocationRaw | null {
        if (!notification.product) return null

        const root_id = notification.parent_id ?? notification.review_id
        const query: Record<string, string> = { tab: 'reviews' }
        if (root_id) query.pin = String(root_id)

        return {
            name: 'product',
            params: { slug: notification.product.slug },
            query,
            hash: notification.review_id ? `#review-${notification.review_id}` : undefined,
        }
    }

    function on_notification_click(notification: AppNotification) {
        if (!notification.read_at) notification_store.mark_read(notification.id)
    }

    return {
        notification_label,
        build_link,
        on_notification_click,
    }
}
