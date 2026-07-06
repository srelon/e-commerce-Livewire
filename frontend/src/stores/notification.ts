import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'
import type { AppNotification } from '@/types/notification'
import type { Paginated } from '@/types/shop'

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref<AppNotification[]>([])
    const unread_count = ref(0)
    const loaded = ref(false)

    function fetch_notifications() {
        return api.get('notifications').then((res) => {
            notifications.value = res.data.data.items
            unread_count.value = res.data.data.unread_count
            loaded.value = true
        })
    }

    function fetch_unread_count() {
        return api.get('notifications/unread-count').then((res) => {
            unread_count.value = res.data.data.count
        })
    }

    function fetch_all(page = 1) {
        return api.get('notifications/all', { params: { page } }).then((res) => res.data.data.items as Paginated<AppNotification>)
    }

    function mark_read(id: number) {
        const notification = notifications.value.find((n) => n.id === id)
        if (notification && !notification.read_at) {
            notification.read_at = new Date().toISOString()
        }

        return api.patch(`notifications/${id}/read`)
    }

    function upsert_notification(notification: AppNotification) {
        const index = notifications.value.findIndex((n) => n.id === notification.id)
        const was_unread = index !== -1 && !notifications.value[index].read_at

        if (index !== -1) {
            notifications.value[index] = notification
        } else {
            notifications.value.unshift(notification)
        }

        if (!notification.read_at && !was_unread) {
            unread_count.value += 1
        }
    }

    function remove_notification(id: number) {
        const notification = notifications.value.find((n) => n.id === id)
        notifications.value = notifications.value.filter((n) => n.id !== id)

        if (notification && !notification.read_at) {
            unread_count.value = Math.max(0, unread_count.value - 1)
        }
    }

    function set_unread_count(count: number) {
        unread_count.value = count
    }

    function reset() {
        notifications.value = []
        unread_count.value = 0
        loaded.value = false
    }

    function on_notification_event(e: Event) {
        const detail = (e as CustomEvent).detail

        if (detail.action === 'upserted') {
            upsert_notification(detail.notification)
        } else if (detail.action === 'deleted') {
            remove_notification(detail.id)
        } else if (detail.action === 'unread_count') {
            set_unread_count(detail.count)
        }
    }

    window.addEventListener('ws:notification', on_notification_event)

    return {
        notifications,
        unread_count,
        loaded,
        fetch_notifications,
        fetch_unread_count,
        fetch_all,
        mark_read,
        upsert_notification,
        remove_notification,
        set_unread_count,
        reset,
    }
})
