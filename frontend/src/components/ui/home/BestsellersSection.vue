<template>
    <section class="bestsellers section">
        <div class="container">
            <div class="section__header">
                <div>
                    <h2 class="section__title">{{ title }}</h2>
                    <p class="bestsellers__desc">{{ description }}</p>
                </div>
                <router-link :to="view_all_href" class="bestsellers__view-all">
                    {{ view_all_label }}
                    <svg viewBox="0 0 15 15" aria-hidden="true">
                        <path d="M1 15a1 1 0 0 1-.707-1.707L11.586 2H1.52a1 1 0 0 1 0-2h12.483q.202.002.379.075a1 1 0 0 1 .542.543 1 1 0 0 1 .076.38V13.48a1 1 0 1 1-2 0V3.414L1.707 14.707A1 1 0 0 1 1 15"/>
                    </svg>
                </router-link>
            </div>

            <div class="bestsellers__grid">
                <template v-if="loading">
                    <ProductCard v-for="n in 4" :key="n" loading />
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
                        :image="to_storage_url(product.image)"
                    />
                </template>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import ProductCard from '@/components/ui/product/ProductCard.vue'
import { to_storage_url } from '@/stores/layout'
import type { ProductSummary } from '@/types/shop'
import type { RouteLocationRaw } from 'vue-router'

interface Props {
    products?: ProductSummary[]
    loading?: boolean
    title?: string
    description?: string
    view_all_label?: string
    view_all_href?: RouteLocationRaw
}

withDefaults(defineProps<Props>(), {
    products: () => [],
    loading: false,
    title: 'Bestsellers of the week',
    description: 'Quam elementum pulvinar etiam non quam. Faucibus nisl tincidunt eget nullam non nisi elementum sagittis vitae et leo duis ut diam quam.',
    view_all_label: 'View All',
    view_all_href: () => ({ name: 'products', query: { status: 'Bestseller' } }),
})
</script>

<style lang="scss" scoped>
.bestsellers {
    &__desc {
        font-size: 14px;
        color: $color-gray;
        margin-top: 8px;
        max-width: 600px;
    }

    &__view-all {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: $color-dark;
        white-space: nowrap;
        flex-shrink: 0;
        transition: color 0.2s;

        svg {
            width: 12px;
            height: 12px;
            fill: currentColor;
        }

        &:hover {
            color: $color-primary;
        }
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }
}
</style>
