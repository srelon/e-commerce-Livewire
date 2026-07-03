<template>
    <div class="book-card">
        <div class="book-card__figure">
            <router-link :to="href" class="book-card__img-link">
                <img :src="image" :alt="title" class="book-card__img">
            </router-link>
        </div>

        <div class="book-card__body">
            <div class="book-card__title-row">
                <h3 class="book-card__title">
                    <router-link :to="href">{{ title }}</router-link>
                </h3>
                <span v-if="rating !== undefined" class="book-card__rating-badge">★ {{ rating }}</span>
            </div>
            <div class="book-card__meta">
                <span class="book-card__author">{{ author }}</span>
                <span class="book-card__separator">·</span>
                <button class="book-card__category" @click="go_to_category">{{ category }}</button>
            </div>
            <div class="book-card__footer">
                <span class="book-card__price">${{ price }}</span>
                <div class="book-card__actions btn-group">
                    <button aria-label="Add to wishlist" class="book-card__action">
                        <svg viewBox="0 0 15 15" aria-hidden="true">
                            <path d="M13.4,3.2c-0.9-0.8-2.3-1-3.5-0.8C8.9,2.6,8.1,3,7.5,3.7C6.9,3,6.1,2.6,5.2,2.4c-1.3-0.2-2.6,0-3.6,0.8C0.7,3.9,0.1,5,0,6.1c-0.1,1.3,0.3,2.6,1.3,3.7c1.2,1.4,5.6,4.7,5.8,4.8L7.5,15L8,14.6c0.2-0.1,4.5-3.5,5.7-4.8c1-1.1,1.4-2.4,1.3-3.7C14.9,5,14.3,3.9,13.4,3.2z"/>
                        </svg>
                    </button>
                    <QuickView :href="href" />
                    <template v-if="is_in_cart">
                        <button class="book-card__cart book-card__cart--in" @click="store.open_popup()">
                            View Cart
                        </button>
                    </template>
                    <template v-else>
                        <BaseButton variant="outline" class="book-card__cart" @click="handle_add_to_cart">
                            Add to cart
                        </BaseButton>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import QuickView from '@/components/ui/product/QuickView.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import { useShopStore } from '@/stores/shop'
import { useQueryPatch } from '@/composables/useQueryPatch'

interface Props {
    id: string
    title: string
    author: string
    category: string
    price: string
    image: string
    href?: string
    rating?: number
}

const props = withDefaults(defineProps<Props>(), {
    href: '/product',
})

const store = useShopStore()
const route = useRoute()
const router = useRouter()
const { patch_query } = useQueryPatch()

const is_in_cart = computed(() => store.in_cart(props.id))

function handle_add_to_cart() {
    store.add_to_cart({
        id: props.id,
        title: props.title,
        author: props.author,
        price: parseFloat(props.price),
        image: props.image,
        href: props.href ?? '/product',
    })
}

function go_to_category() {
    if (route.path === '/products') {
        patch_query({ category: props.category })
    } else {
        router.push({ path: '/products', query: { category: props.category } })
    }
}
</script>

<style lang="scss" scoped>
@use "sass:color";

.book-card {
    &__figure {
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 16px;
        aspect-ratio: 2 / 3;
    }

    &__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.4s ease;

        .book-card:hover & {
            transform: scale(1.04);
        }
    }

    &__img-link {
        display: block;
        width: 100%;
        height: 100%;
    }

    &__title-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    &__rating-badge {
        font-size: 16px;
        font-weight: 700;
        color: #f5a623;
        white-space: nowrap;
        flex-shrink: 0;
    }

    &__title {
        font-size: 15px;
        font-weight: 600;

        a {
            color: $color-dark;
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__meta {
        font-size: 13px;
        color: $color-gray;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    &__category {
        color: inherit;
        font-size: inherit;
        font-family: $font-body;
        background: none;
        padding: 0;
        cursor: pointer;
        transition: color 0.2s;

        &:hover {
            color: $color-primary;
        }
    }

    &__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    &__price {
        font-size: 16px;
        font-weight: 700;
        color: $color-primary;
        flex-shrink: 0;
    }

    &__action {
        padding: 0 9px;
        background: transparent;
        border: 1.5px solid $color-light;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;

        svg {
            width: 13px;
            height: 13px;
            fill: $color-dark;
            transition: fill 0.2s;
        }

        &:hover {
            background: $color-primary;
            border-color: $color-primary;

            svg {
                fill: $color-white;
            }
        }
    }

    &__cart {
        font-size: 13px;
        padding: 6px 14px;

        &--in {
            background: $color-primary;
            color: $color-white;
            border-color: $color-primary;
            cursor: pointer;

            &:hover {
                background: color.adjust($color-primary, $lightness: -8%);
                border-color: color.adjust($color-primary, $lightness: -8%);
            }
        }
    }
}
</style>
