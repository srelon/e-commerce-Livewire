import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { to_storage_url } from '@/stores/layout'
import api from '@/plugins/axios'
import type { CartItem, ServerCartItem, LastOrder } from '@/types/shop'

const STORAGE_KEY = 'cart_items'

function load(): CartItem[] | null {
    try {
        const raw = localStorage.getItem(STORAGE_KEY)
        if (!raw) return null

        const items = JSON.parse(raw) as CartItem[]

        return items.map(item => ({
            ...item,
            available: Number.isFinite(item.available) ? item.available : item.quantity,
        }))
    } catch {
        return null
    }
}

function save(items: CartItem[]) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items))
    } catch {}
}

export const useShopStore = defineStore('shop', () => {
    const cart_items = ref<CartItem[]>(load() ?? [])
    const popup_open = ref(false)
    const last_order = ref<LastOrder | null>(null)

    watch(cart_items, () => save(cart_items.value), { deep: true })

    const cart_count = computed(() =>
        cart_items.value.reduce((sum, item) => sum + item.quantity, 0)
    )

    const cart_total = computed(() =>
        cart_items.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
    )

    function in_cart(id: string) {
        return cart_items.value.some(item => item.id === id)
    }

    async function add_to_cart(product: Omit<CartItem, 'quantity'>, quantity = 1) {
        if (useAuthStore().is_authenticated) {
            try {
                await api.post('cart/items', { slug: product.id, quantity })
            } catch {
                return
            }
        }

        const existing = cart_items.value.find(item => item.id === product.id)
        if (existing) {
            existing.quantity = quantity
            existing.available = product.available
        } else {
            cart_items.value.push({ ...product, quantity })
        }
        popup_open.value = true
    }

    function merge_server_cart(server_items: ServerCartItem[]) {
        const to_sync: { slug: string; quantity: number }[] = []

        for (const local_item of cart_items.value) {
            const server_item = server_items.find(item => item.slug === local_item.id)
            if (!server_item || server_item.quantity !== local_item.quantity) {
                to_sync.push({ slug: local_item.id, quantity: local_item.quantity })
            }
            if (server_item) {
                local_item.available = server_item.available
            }
        }

        for (const server_item of server_items) {
            if (cart_items.value.some(item => item.id === server_item.slug)) continue

            cart_items.value.push({
                id: server_item.slug,
                title: server_item.title,
                author: server_item.author ?? '',
                price: parseFloat(server_item.price ?? '0'),
                image: to_storage_url(server_item.image),
                href: { name: 'product', params: { slug: server_item.slug } },
                quantity: server_item.quantity,
                available: server_item.available,
            })
        }

        if (to_sync.length === 0) return

        api.post('cart/sync', { items: to_sync }).then((res) => {
            const conflicts = res.data.data?.conflicts as { slug: string; available: number }[] | undefined
            conflicts?.forEach((conflict) => {
                set_qty(conflict.slug, conflict.available)
                const item = cart_items.value.find(i => i.id === conflict.slug)
                if (item) item.available = conflict.available
            })
        }).catch(() => {})
    }

    function remove_from_cart(id: string) {
        cart_items.value = cart_items.value.filter(item => item.id !== id)
        if (cart_items.value.length === 0) {
            popup_open.value = false
        }

        if (useAuthStore().is_authenticated) {
            api.delete(`cart/items/${id}`).catch(() => {})
        }
    }

    function set_qty(id: string, quantity: number) {
        if (quantity <= 0) {
            remove_from_cart(id)
            return
        }
        const item = cart_items.value.find(i => i.id === id)
        if (item) item.quantity = quantity
    }

    async function sync_qty(id: string, new_quantity: number) {
        if (new_quantity <= 0) {
            remove_from_cart(id)
            return
        }

        if (useAuthStore().is_authenticated) {
            try {
                await api.post('cart/items', { slug: id, quantity: new_quantity })
            } catch (error) {
                if (axios.isAxiosError(error) && error.response?.status === 422) {
                    const conflicts = error.response.data?.items as { slug: string; available: number }[] | undefined
                    const conflict = conflicts?.find(c => c.slug === id)
                    if (conflict) {
                        set_qty(id, conflict.available)
                        const item = cart_items.value.find(i => i.id === id)
                        if (item) item.available = conflict.available
                    }
                }
                return
            }
        }

        const item = cart_items.value.find(i => i.id === id)
        if (item) item.quantity = new_quantity
    }

    async function update_qty(id: string, delta: number) {
        const item = cart_items.value.find(i => i.id === id)
        if (!item) return

        await sync_qty(id, item.quantity + delta)
    }

    async function input_qty(id: string, quantity: number) {
        const item = cart_items.value.find(i => i.id === id)
        if (!item) return

        const clamped = Math.min(Math.max(Math.trunc(quantity) || 1, 1), item.available)

        await sync_qty(id, clamped)
    }

    function clear_cart() {
        cart_items.value = []
        popup_open.value = false
    }

    function open_popup() {
        popup_open.value = true
    }

    function close_popup() {
        popup_open.value = false
    }

    function set_last_order(order: LastOrder) {
        last_order.value = order
    }

    function consume_last_order(): LastOrder | null {
        const order = last_order.value
        last_order.value = null
        return order
    }

    return {
        cart_items,
        cart_count,
        cart_total,
        popup_open,
        last_order,
        in_cart,
        add_to_cart,
        merge_server_cart,
        remove_from_cart,
        set_qty,
        update_qty,
        input_qty,
        clear_cart,
        open_popup,
        close_popup,
        set_last_order,
        consume_last_order,
    }
})
