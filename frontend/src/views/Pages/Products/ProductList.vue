<template>
    <PageBanner title="Products" />

    <section class="products section">
        <div class="container">
            <ListBar :title="`${pagination.total} ${pagination.total === 1 ? 'result' : 'results'} found`" :loading="is_loading">
                <ActiveFilters
                    :filter_groups="filter_groups"
                    :selected="selected"
                    :price_min="price_min"
                    :price_max="price_max"
                    :price_bounds_min="price_bounds_min"
                    :price_bounds_max="price_bounds_max"
                    @remove="on_sidebar_filter"
                    @clear="on_sidebar_filter"
                />
                <template #right>
                    <SortSelect v-model="sort_by" :options="sort_options" />
                </template>
            </ListBar>

            <div class="products__inner">
                <ProductSidebar
                    :filter_groups="filter_groups"
                    :selected="selected"
                    :price_min="price_min"
                    :price_max="price_max"
                    :loading="is_loading"
                    @filter="on_sidebar_filter"
                />

                <div class="products__main">
                    <div class="products__grid">
                        <template v-if="is_loading">
                            <ProductCard v-for="n in 9" :key="n" loading />
                        </template>
                        <template v-else>
                            <ProductCard
                                v-for="product in products"
                                :key="product.slug"
                                :id="product.slug"
                                :title="product.title"
                                :author="product.author ?? ''"
                                :category="product.category ?? ''"
                                :price="product.stock.price ?? ''"
                                :rating="product.rating"
                                :quantity="product.stock.quantity"
                                :image="to_storage_url(product.image)"
                            />
                        </template>
                    </div>

                    <BasePagination
                        :current_page="pagination.current_page"
                        :last_page="pagination.last_page"
                        class="products__pagination"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import ListBar from '@/components/ui/base/ListBar.vue'
import ProductSidebar from '@/components/ui/sidebar/ProductSidebar.vue'
import ActiveFilters from '@/components/ui/filters/ActiveFilters.vue'
import ProductCard from '@/components/ui/product/ProductCard.vue'
import BasePagination from '@/components/ui/base/BasePagination.vue'
import SortSelect from '@/components/ui/base/SortSelect.vue'
import api from '@/plugins/axios'
import { to_storage_url } from '@/stores/layout'
import { useQueryPatch } from '@/composables/useQueryPatch'
import type { FilterGroup, Paginated, Pagination, ProductSummary } from '@/types/shop'
import type { SortKey } from '@/components/ui/base/SortSelect.vue'

const route = useRoute()
const { patch_query } = useQueryPatch()

const sort_options: SortKey[] = ['default', 'popularity', 'rating', 'price_asc', 'price_desc', 'newest', 'oldest']

const is_loading = ref(true)
const products = ref<ProductSummary[]>([])
const filter_groups = ref<FilterGroup[]>([])
const pagination = ref<Pagination>({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const query_key_order = computed(() => {
    const keys: string[] = []
    for (const group of filter_groups.value) {
        if (group.type === 'price') {
            keys.push('price_min', 'price_max')
        } else {
            keys.push(group.query_key)
        }
    }
    keys.push('sort_by', 'page')
    return keys
})

const sort_by = computed({
    get: () => (route.query.sort_by as string) ?? 'default',
    set: (value: string) => {
        patch_query({ sort_by: value === 'default' ? undefined : value }, { order: query_key_order.value })
    },
})

const price_group = computed(() => filter_groups.value.find((group) => group.type === 'price'))
const price_bounds_min = computed(() => price_group.value?.min ?? 0)
const price_bounds_max = computed(() => price_group.value?.max ?? 100)

function clamp_price(value: number): number {
    return Math.min(Math.max(value, price_bounds_min.value), price_bounds_max.value)
}

const price_min = computed(() => {
    const raw = route.query.price_min ? Number(route.query.price_min) : price_bounds_min.value
    return clamp_price(raw)
})
const price_max = computed(() => {
    const raw = route.query.price_max ? Number(route.query.price_max) : price_bounds_max.value
    return clamp_price(raw)
})

function query_to_array(val: string | (string | null)[] | null): string[] {
    if (!val) return []
    const raw = Array.isArray(val) ? val : [val]
    return raw.flatMap((v) => (v ?? '').split(',')).filter(Boolean)
}

const selected = computed<Record<string, string[] | number | null>>(() => {
    const result: Record<string, string[] | number | null> = {}
    for (const group of filter_groups.value) {
        if (group.type === 'checkbox') {
            result[group.query_key] = query_to_array(route.query[group.query_key])
        } else if (group.type === 'rating') {
            result[group.query_key] = route.query.rating ? Number(route.query.rating) : null
        }
    }
    return result
})

function on_sidebar_filter(patch: Record<string, string | undefined>) {
    patch_query(patch, { order: query_key_order.value })
}

function sanitize_query() {
    const patch: Record<string, string | undefined> = {}
    let changed = false

    for (const group of filter_groups.value) {
        if (group.type !== 'checkbox') continue

        const valid_names = new Set((group.items ?? []).map((item) => item.name))
        const current = query_to_array(route.query[group.query_key])
        const cleaned = current.filter((name) => valid_names.has(name))

        if (cleaned.length !== current.length) {
            patch[group.query_key] = cleaned.length ? cleaned.join(',') : undefined
            changed = true
        }
    }

    for (const key of ['price_min', 'price_max', 'page'] as const) {
        const raw = route.query[key]
        if (raw !== undefined && raw !== null && Number.isNaN(Number(Array.isArray(raw) ? raw[0] : raw))) {
            patch[key] = undefined
            changed = true
        }
    }

    if (changed) {
        patch_query(patch, { reset_page: false, order: query_key_order.value })
    }
}

function fetch_products() {
    is_loading.value = true
    api.get('products', { params: route.query }).then((res) => {
        const items: Paginated<ProductSummary> = res.data.data.items
        products.value = items.data
        filter_groups.value = res.data.data.filter_groups
        pagination.value = items.pagination
        sanitize_query()
    }).finally(() => {
        is_loading.value = false
    })
}

watch(
    () => route.query,
    fetch_products,
    {
        immediate: true,
        deep: true,
    },
)
</script>

<style lang="scss" scoped>
.products {
    &__inner {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    &__main {
        flex: 1;
        min-width: 0;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    &__pagination {
        margin-top: 40px;
    }
}
</style>
