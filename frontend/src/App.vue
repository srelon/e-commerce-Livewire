<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { RouterView } from 'vue-router'
import { useLayoutStore } from '@/stores/layout'
import { useAuthStore } from '@/stores/auth'
import { useShopStore } from '@/stores/shop'
import { useWebsocketStore } from '@/stores/websocket'
import { useNotificationStore } from '@/stores/notification'

const layout_store = useLayoutStore()
const auth_store = useAuthStore()
const shop_store = useShopStore()
const websocket_store = useWebsocketStore()
const notification_store = useNotificationStore()

onMounted(() => {
    layout_store.fetch_layout()
    auth_store.ensure_ready()
})

watch(
    () => auth_store.user,
    (user, previous_user) => {
        if (user) {
            websocket_store.subscribe(`notifications.users.${user.public_id}`)
            notification_store.fetch_unread_count()
            if (auth_store.cart) shop_store.merge_server_cart(auth_store.cart)
        } else if (previous_user) {
            websocket_store.unsubscribe(`notifications.users.${previous_user.public_id}`)
            notification_store.reset()
        }
    },
)
</script>

<template>
    <RouterView />
</template>
