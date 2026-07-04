import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { RouteLocationRaw } from 'vue-router'

export interface CartItem {
    id: string
    title: string
    author: string
    price: number
    image: string
    href: RouteLocationRaw
    quantity: number
}

export const useShopStore = defineStore('shop', () => {
    const cart_items = ref<CartItem[]>([])
    const popup_open = ref(false)

    const cart_count = computed(() =>
        cart_items.value.reduce((sum, item) => sum + item.quantity, 0)
    )

    const cart_total = computed(() =>
        cart_items.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
    )

    function in_cart(id: string) {
        return cart_items.value.some(item => item.id === id)
    }

    function add_to_cart(product: Omit<CartItem, 'quantity'>, quantity = 1) {
        const existing = cart_items.value.find(item => item.id === product.id)
        if (existing) {
            existing.quantity += quantity
        } else {
            cart_items.value.push({ ...product, quantity })
        }
        popup_open.value = true
    }

    function remove_from_cart(id: string) {
        cart_items.value = cart_items.value.filter(item => item.id !== id)
        if (cart_items.value.length === 0) {
            popup_open.value = false
        }
    }

    function update_qty(id: string, delta: number) {
        const item = cart_items.value.find(i => i.id === id)
        if (!item) return
        const next = item.quantity + delta
        if (next <= 0) {
            remove_from_cart(id)
        } else {
            item.quantity = next
        }
    }

    function open_popup() {
        popup_open.value = true
    }

    function close_popup() {
        popup_open.value = false
    }

    return {
        cart_items,
        cart_count,
        cart_total,
        popup_open,
        in_cart,
        add_to_cart,
        remove_from_cart,
        update_qty,
        open_popup,
        close_popup,
    }
})
