<template>
    <section class="rated section">
        <div class="container">
            <div class="section__header">
                <h2 class="section__title">{{ title }}</h2>
                <router-link :to="view_all_href" class="rated__view-all">
                    {{ view_all_label }}
                    <svg viewBox="0 0 15 15" aria-hidden="true">
                        <path d="M1 15a1 1 0 0 1-.707-1.707L11.586 2H1.52a1 1 0 0 1 0-2h12.483q.202.002.379.075a1 1 0 0 1 .542.543 1 1 0 0 1 .076.38V13.48a1 1 0 1 1-2 0V3.414L1.707 14.707A1 1 0 0 1 1 15"/>
                    </svg>
                </router-link>
            </div>
            <div class="rated__grid">
                <template v-if="loading">
                    <RatedCard v-for="n in 3" :key="n" loading />
                </template>
                <template v-else>
                    <RatedCard
                        v-for="product in products"
                        :key="product.slug"
                        :slug="product.slug"
                        :title="product.title"
                        :category="product.category ?? ''"
                        :price="product.stock.price ?? ''"
                        :rating="product.rating"
                        :image="to_storage_url(product.image)"
                    />
                </template>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import RatedCard from '@/components/ui/product/RatedCard.vue'
import { to_storage_url } from '@/stores/layout'
import type { ProductSummary } from '@/types/shop'
import type { RouteLocationRaw } from 'vue-router'

interface Props {
    products?: ProductSummary[]
    loading?: boolean
    title?: string
    view_all_label?: string
    view_all_href?: RouteLocationRaw
}

withDefaults(defineProps<Props>(), {
    products: () => [],
    loading: false,
    title: 'Best Rated Books',
    view_all_label: 'View All Books',
    view_all_href: () => ({ name: 'products' }),
})
</script>

<style lang="scss" scoped>
.rated {
    background: $color-lightest;

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
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
}
</style>
