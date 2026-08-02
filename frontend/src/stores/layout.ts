import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'
import type { ProductSummary } from '@/types/shop'
import type { LayoutCategory, LayoutMenuItem, LayoutContact } from '@/types/global'

export const useLayoutStore = defineStore('layout', () => {
    const loaded = ref(false)
    const categories = ref<LayoutCategory[]>([])
    const menu = ref<LayoutMenuItem[]>([])
    const contacts = ref<LayoutContact[]>([])
    const best_books = ref<ProductSummary[]>([])

    function fetch_layout() {
        if (loaded.value) return Promise.resolve()

        return api.get('layout').then((res) => {
            const data = res.data.data
            categories.value = data.categories
            menu.value = data.menu
            contacts.value = data.contacts
            best_books.value = data.best_books
            loaded.value = true
        })
    }

    return {
        loaded,
        categories,
        menu,
        contacts,
        best_books,
        fetch_layout,
    }
})

export function to_storage_url(path: string | null): string {
    if (!path) return ''
    const app_url = import.meta.env.VITE_APP_URL ?? ''
    return `${app_url}/storage/${path}`
}

export function menu_route_target(item: LayoutMenuItem) {
    return {
        name: item.route ?? undefined,
        params: item.params ?? undefined,
    }
}
