<template>
    <PageBanner v-if="!quick" :title="page_title" :loading="is_loading" :parent="{ label: 'Products', to: { name: 'products' } }" />

    <section class="product section" :class="{ 'product--quick': quick }">
        <div class="container">
            <div class="product__inner">
                <template v-if="is_loading">
                    <div class="product__gallery">
                        <div class="product__main-img">
                            <BaseSkeleton radius="12px" />
                        </div>
                        <div class="product__thumbs-skeleton">
                            <BaseSkeleton v-for="n in 6" :key="n" width="72px" height="72px" radius="8px" />
                        </div>
                    </div>
                    <div class="product__summary">
                        <BaseSkeleton width="70%" height="30px" />
                        <BaseSkeleton width="140px" height="18px" />
                        <BaseSkeleton width="90px" height="30px" />
                        <BaseSkeleton height="90px" />
                        <BaseSkeleton width="220px" height="46px" radius="6px" />
                    </div>
                </template>
                <template v-else-if="product">
                    <div class="product__gallery">
                        <button class="product__main-img" @click="lightbox_open = true" aria-label="View full image">
                            <img :src="active_image" :alt="product.title" class="product__img">
                            <span class="product__zoom-hint">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                            </span>
                        </button>
                        <ThumbSlider
                            v-if="gallery_images.length > 1"
                            :images="gallery_images"
                            :active="active_image"
                            :alt="product.title"
                            :visible="6"
                            :item_size="72"
                            @select="open_lightbox($event)"
                            @hover="active_image = $event"
                        />
                    </div>

                    <ImageLightbox
                        v-model="active_image"
                        v-model:open="lightbox_open"
                        :images="gallery_images"
                        :alt="product.title"
                    />

                    <div class="product__summary">
                        <h1 class="product__title">{{ product.title }}</h1>
                        <StarRating :rating="product.rating" :count="product.reviews_count" @click-count="on_view_reviews" />
                        <div class="product__price">
                            ${{ product.stock.price }}
                            <span v-if="product.stock.before_price" class="product__price-before">${{ product.stock.before_price }}</span>
                        </div>
                        <p class="product__desc">{{ product.short_description }}</p>

                        <div class="product__actions">
                            <div v-if="!is_in_cart && !out_of_stock" class="product__qty">
                                <button type="button" :disabled="qty <= 1" @click="decrement_qty">−</button>
                                <input
                                    type="number"
                                    class="product__qty-input"
                                    :min="1"
                                    :max="max_qty"
                                    v-model.number="qty"
                                    @change="on_qty_change"
                                >
                                <button type="button" :disabled="qty >= max_qty" @click="increment_qty">+</button>
                            </div>
                            <BaseButton v-if="is_in_cart" class="product__add-to-cart" @click="store.open_popup()">
                                View Cart
                            </BaseButton>
                            <BaseButton v-else-if="out_of_stock" class="product__add-to-cart" disabled>
                                Out of Stock
                            </BaseButton>
                            <BaseButton v-else class="product__add-to-cart" @click="handle_add_to_cart">
                                Add to Cart
                            </BaseButton>
                        </div>

                        <div class="product__meta">
                            <span>Category:</span>
                            <router-link :to="{ name: 'products', query: { category: product.category } }">{{ product.category }}</router-link>
                        </div>

                        <router-link v-if="quick" :to="to" class="product__view-link">
                            <BaseButton variant="outline">View Product</BaseButton>
                        </router-link>
                    </div>
                </template>
                <p v-else class="product__not-found">Product not found.</p>
            </div>

            <div v-if="!quick && product" ref="tabs_wrap">
                <BaseTabs v-model="active_tab" :tabs="tabs" class="product__tabs">
                    <template #label="{ tab }">{{ tab === 'Reviews' ? `Reviews (${product.reviews_count})` : tab }}</template>

                    <p v-if="active_tab === 'Description'" class="product__description-text">{{ product.description }}</p>
                    <ReviewsPanel
                        v-else-if="active_tab === 'Reviews'"
                        :product_slug="product.slug"
                        :product_title="product.title"
                        :product_rating="product.rating"
                        :product_reviews_count="product.reviews_count"
                    />
                </BaseTabs>
            </div>

            <div v-if="!quick && (is_loading || product)" class="product__related">
                <h2 class="section__title">Related Products</h2>
                <div class="product__related-grid">
                    <template v-if="is_loading">
                        <ProductCard v-for="n in 4" :key="n" loading />
                    </template>
                    <template v-else>
                        <ProductCard
                            v-for="p in product?.related"
                            :key="p.slug"
                            :id="p.slug"
                            :title="p.title"
                            :author="p.author ?? ''"
                            :category="p.category ?? ''"
                            :price="p.stock.price ?? ''"
                            :rating="p.rating"
                            :quantity="p.stock.quantity"
                            :image="to_storage_url(p.image)"
                        />
                    </template>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useShopStore } from '@/stores/shop'
import { useWebsocketStore } from '@/stores/websocket'
import api from '@/plugins/axios'
import { to_storage_url } from '@/stores/layout'
import type { ProductDetail } from '@/types/shop'

const store = useShopStore()
const websocket_store = useWebsocketStore()
const route = useRoute()

interface Props {
    quick?: boolean
    slug?: string
}

const props = withDefaults(defineProps<Props>(), {
    quick: false,
    slug: '',
})

import PageBanner from '@/components/ui/base/PageBanner.vue'
import BaseTabs from '@/components/ui/base/BaseTabs.vue'
import ProductCard from '@/components/ui/product/ProductCard.vue'
import ThumbSlider from '@/components/ui/product/ThumbSlider.vue'
import ImageLightbox from '@/components/ui/product/ImageLightbox.vue'
import ReviewsPanel from '@/components/ui/product/ReviewsPanel.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import StarRating from '@/components/ui/base/StarRating.vue'

const qty = ref(1)
const lightbox_open = ref(false)
const is_loading = ref(true)
const product = ref<ProductDetail | null>(null)
const active_image = ref('')
const tabs_wrap = ref<HTMLElement | null>(null)

const to = computed(() => ({ name: 'product', params: { slug: props.slug } }))
const is_in_cart = computed(() => store.in_cart(props.slug))
const gallery_images = computed(() => (product.value?.images ?? []).map(to_storage_url))
const page_title = computed(() => product.value?.title ?? (is_loading.value ? '' : 'Product Not Found'))
const max_qty = computed(() => product.value?.stock.quantity ?? 1)
const out_of_stock = computed(() => (product.value?.stock.quantity ?? 0) <= 0)

function clamp_qty(value: number): number {
    if (Number.isNaN(value)) return 1

    return Math.min(Math.max(Math.trunc(value), 1), Math.max(max_qty.value, 1))
}

function increment_qty() {
    qty.value = clamp_qty(qty.value + 1)
}

function decrement_qty() {
    qty.value = clamp_qty(qty.value - 1)
}

function on_qty_change() {
    qty.value = clamp_qty(qty.value)
}

function handle_add_to_cart() {
    if (!product.value) return

    qty.value = clamp_qty(qty.value)

    store.add_to_cart({
        id: props.slug,
        title: product.value.title,
        author: product.value.author ?? '',
        price: parseFloat(product.value.stock.price ?? '0'),
        image: gallery_images.value[0] ?? '',
        href: to.value,
        available: max_qty.value,
    }, qty.value)
}

let subscribed_channel: string | null = null

function subscribe_to_reviews(slug: string) {
    if (subscribed_channel) websocket_store.unsubscribe(subscribed_channel)
    subscribed_channel = `reviews.products.${slug}`
    websocket_store.subscribe(subscribed_channel)
}

function on_review_product_update(e: Event) {
    const data = (e as CustomEvent).detail

    if (!product.value || !data.product) return

    product.value.rating = data.product.rating
    product.value.reviews_count = data.product.reviews_count
}

function fetch_product() {
    if (!props.slug) return

    is_loading.value = true
    api.get(`products/${props.slug}`).then((res) => {
        product.value = res.data.data
        active_image.value = gallery_images.value[0] ?? ''
        qty.value = clamp_qty(1)
        subscribe_to_reviews(props.slug)
    }).catch(() => {
        product.value = null
    }).finally(() => {
        is_loading.value = false
    })
}

watch(() => props.slug, fetch_product, { immediate: true })

onMounted(() => {
    window.addEventListener('ws:review.created', on_review_product_update)
    window.addEventListener('ws:review.updated', on_review_product_update)
    window.addEventListener('ws:review.deleted', on_review_product_update)
})

onUnmounted(() => {
    if (subscribed_channel) websocket_store.unsubscribe(subscribed_channel)
    window.removeEventListener('ws:review.created', on_review_product_update)
    window.removeEventListener('ws:review.updated', on_review_product_update)
    window.removeEventListener('ws:review.deleted', on_review_product_update)
})

const active_tab = ref(route.query.tab === 'reviews' ? 'Reviews' : 'Description')

const tabs = ['Description', 'Reviews']

watch(() => props.slug, () => {
    active_tab.value = route.query.tab === 'reviews' ? 'Reviews' : 'Description'
})

function on_view_reviews() {
    active_tab.value = 'Reviews'
    nextTick(() => tabs_wrap.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }))
}

function open_lightbox(img: string) {
    active_image.value = img
    lightbox_open.value = true
}
</script>

<style lang="scss" scoped>
.product {
    &--quick {
        padding: 32px 0 24px;
    }

    &__view-link {
        display: block;
        margin-top: 16px;

        .base-btn {
            width: 100%;
        }
    }

    &__inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: start;
        margin-bottom: 60px;
    }

    &__gallery {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    &__thumbs-skeleton {
        display: flex;
        gap: 12px;
    }

    &__not-found {
        font-size: 16px;
        color: $color-gray;
    }

    &__main-img {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 480px;
        border-radius: 12px;
        overflow: hidden;
        background: $color-lightest;
        cursor: zoom-in;
        border: none;
        padding: 0;

        &:hover .product__zoom-hint {
            opacity: 1;
        }
    }

    &__img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
    }

    &__zoom-hint {
        position: absolute;
        bottom: 12px;
        right: 12px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;

        svg {
            width: 16px;
            height: 16px;

            path {
                fill: none;
                stroke: $color-white;
                stroke-width: 2;
                stroke-linecap: round;
                stroke-linejoin: round;
            }
        }
    }

    &__title {
        font-size: clamp(22px, 2.5vw, 34px);
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 12px;
        line-height: 1.2;
    }

    .star-rating {
        margin-bottom: 16px;
    }

    &__price {
        font-size: 32px;
        font-weight: 700;
        color: $color-primary;
        margin-bottom: 20px;
    }

    &__price-before {
        font-size: 18px;
        font-weight: 600;
        color: $color-gray;
        text-decoration: line-through;
        margin-left: 8px;
    }

    &__desc {
        font-size: 15px;
        color: $color-gray;
        line-height: 1.7;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid $color-light;
    }

    &__actions {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 24px;
    }

    &__qty {
        display: flex;
        align-items: center;
        border: 1.5px solid $color-light;
        border-radius: 6px;
        overflow: hidden;

        button {
            width: 38px;
            height: 46px;
            font-size: 18px;
            color: $color-dark;
            font-family: $font-body;
            cursor: pointer;
            transition: background 0.15s;

            &:hover:not(:disabled) {
                background: $color-lightest;
            }

            &:disabled {
                color: $color-gray;
                cursor: not-allowed;
            }
        }
    }

    &__qty-input {
        width: 46px;
        height: 46px;
        text-align: center;
        font-size: 15px;
        font-weight: 600;
        color: $color-dark;
        font-family: $font-body;
        border: none;
        border-left: 1.5px solid $color-light;
        border-right: 1.5px solid $color-light;
        appearance: textfield;
        outline: none;

        &:focus {
            outline: none;
        }

        &::-webkit-outer-spin-button,
        &::-webkit-inner-spin-button {
            appearance: none;
            margin: 0;
        }
    }

    &__add-to-cart {
        flex: 1;
    }

    &__meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        margin-bottom: 20px;

        span {
            color: $color-gray;
        }

        a {
            color: $color-dark;
            font-weight: 600;
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__safe {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: $color-gray;
        padding: 10px 16px;
        border: 1px solid $color-light;
        border-radius: 6px;

        svg {
            width: 16px;
            height: 16px;

            path {
                fill: none;
                stroke: #18b96e;
                stroke-width: 2;
                stroke-linecap: round;
                stroke-linejoin: round;
            }
        }
    }

    &__tabs {
        border-top: 2px solid $color-light;
        padding-top: 40px;
        margin-bottom: 60px;
    }

    &__description-text {
        font-size: 15px;
        color: $color-gray;
        line-height: 1.75;
    }

    &__related-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-top: 32px;
    }
}

</style>
