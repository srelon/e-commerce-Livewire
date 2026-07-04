import { useRoute, useRouter } from 'vue-router'
import { useQueryPatch } from '@/composables/useQueryPatch'

export function useShopFilterNav() {
    const route = useRoute()
    const router = useRouter()
    const { patch_query } = useQueryPatch()

    function go_to_filter(route_name: string, key: 'category' | 'author', value: string) {
        if (route.name === route_name) {
            patch_query({ [key]: value })
        } else {
            router.push({ name: route_name, query: { [key]: value } })
        }
    }

    return { go_to_filter }
}
