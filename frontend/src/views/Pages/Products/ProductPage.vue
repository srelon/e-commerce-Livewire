<template>
    <PageBanner v-if="!quick" :title="product.title" />

    <section class="product section" :class="{ 'product--quick': quick }">
        <div class="container">
            <div class="product__inner">
                <div class="product__gallery">
                    <button class="product__main-img" @click="lightbox_open = true" aria-label="View full image">
                        <img :src="active_image" :alt="product.title" class="product__img">
                        <span class="product__zoom-hint">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                        </span>
                    </button>
                    <ThumbSlider
                        :images="product.images"
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
                    :images="product.images"
                    :alt="product.title"
                />

                <div class="product__summary">
                    <h1 class="product__title">{{ product.title }}</h1>
                    <StarRating :rating="product.rating" :count="product.reviews" />
                    <div class="product__price">${{ product.price }}</div>
                    <p class="product__desc">{{ product.short_desc }}</p>

                    <div class="product__actions">
                        <div v-if="!is_in_cart" class="product__qty">
                            <button @click="qty > 1 && qty--">−</button>
                            <span>{{ qty }}</span>
                            <button @click="qty++">+</button>
                        </div>
                        <BaseButton v-if="!is_in_cart" class="product__add-to-cart" @click="handle_add_to_cart">
                            Add to Cart
                        </BaseButton>
                        <BaseButton v-else class="product__add-to-cart" @click="store.open_popup()">
                            View Cart
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
            </div>

            <div v-if="!quick" class="product__tabs">
                <div class="product__tab-nav">
                    <button
                        v-for="tab in tabs"
                        :key="tab"
                        class="product__tab-btn"
                        :class="{ 'product__tab-btn--active': active_tab === tab }"
                        @click="active_tab = tab"
                    >
                        {{ tab }}
                    </button>
                </div>
                <div class="product__tab-content">
                    <div v-if="active_tab === 'Description'">
                        <p>{{ product.description }}</p>
                    </div>
                    <div v-else-if="active_tab === 'Reviews (0)'" class="product__reviews">
                        <p class="product__no-reviews">There are no reviews yet. Be the first to write one.</p>
                        <form class="product__review-form" @submit.prevent>
                            <div class="product__review-rating">
                                <label>Your Rating</label>
                                <div class="product__stars">
                                    <button
                                        v-for="i in 5"
                                        :key="i"
                                        type="button"
                                        class="product__star-btn"
                                        :class="{ 'product__star-btn--filled': i <= review_rating }"
                                        @click="review_rating = i"
                                    >★</button>
                                </div>
                            </div>
                            <textarea v-model="review_text" rows="4" placeholder="Write your review..."></textarea>
                            <div class="product__review-row">
                                <input v-model="review_name" type="text" placeholder="Your name">
                                <input v-model="review_email" type="email" placeholder="Your email">
                            </div>
                            <button type="submit" class="product__review-submit">Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>

            <div v-if="!quick" class="product__related">
                <h2 class="section__title">Related Products</h2>
                <div class="product__related-grid">
                    <ProductCard
                        v-for="p in related"
                        :key="p.title"
                        v-bind="p"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useShopStore } from '@/stores/shop'

const store = useShopStore()

interface Props {
    quick?: boolean
    slug?: string
}

const { quick = false, slug = 'anxiety-unmasked' } = defineProps<Props>()

import PageBanner from '@/components/ui/base/PageBanner.vue'
import ProductCard from '@/components/ui/product/ProductCard.vue'
import ThumbSlider from '@/components/ui/product/ThumbSlider.vue'
import ImageLightbox from '@/components/ui/product/ImageLightbox.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import StarRating from '@/components/ui/base/StarRating.vue'

const qty = ref(1)
const lightbox_open = ref(false)

const to = computed(() => ({ name: 'product', params: { slug } }))
const is_in_cart = computed(() => store.in_cart(slug))

function handle_add_to_cart() {
    store.add_to_cart({
        id: slug,
        title: product.title,
        author: 'Theodore Langley',
        price: parseFloat(product.price),
        image: product.images[0],
        href: to.value,
    }, qty.value)
}
const active_tab = ref('Description')
const review_rating = ref(0)
const review_text = ref('')
const review_name = ref('')
const review_email = ref('')

const tabs = ['Description', 'Reviews (0)']

const product = {
    title: 'Anxiety Unmasked',
    price: '18.00',
    rating: 5,
    reviews: 0,
    category: 'Self-help',
    short_desc: 'A compassionate and insightful guide to understanding anxiety, uncovering its roots, and finding practical strategies to reclaim your life and peace of mind.',
    description: 'Anxiety Unmasked takes readers on a transformative journey into the psychology of worry, fear, and overwhelm. With evidence-based techniques drawn from cognitive behavioral therapy and mindfulness, this book offers a comprehensive roadmap to lasting calm. Whether you struggle with everyday stress or clinical anxiety, you\'ll find the tools here to build resilience and live more freely. Perfect for anyone ready to stop letting anxiety run their story.',
    images: [
        '/images/blog-image-4.webp',
        '/images/book-image-7.webp',
        '/images/book-image-19.webp',
        '/images/book-image-24.webp',
        '/images/book-image-25.webp',
        '/images/book-image-18.webp',
        '/images/book-image-26.webp',
        '/images/blog-image-5.webp',
        '/images/book-image-33.webp',
        '/images/home-hero-image-1.webp',
        '/images/home-hero-image-2.webp',
        '/images/home-hero-image-3.webp',
    ],
}

const active_image = ref(product.images[0])

function open_lightbox(img: string) {
    active_image.value = img
    lightbox_open.value = true
}

const related = [
    {
        id: 'astral-journey',
        title: 'Astral Journey',
        author: 'Nathaniel Parker',
        category: 'Fantasy',
        price: '28.00',
        image: '/images/book-image-24.webp',
    },
    {
        id: 'autumn-journey',
        title: 'Autumn Journey',
        author: 'Samuel Wright',
        category: 'Adventure',
        price: '17.00',
        image: '/images/book-image-29.webp',
    },
    {
        id: 'journey-through-time',
        title: 'Journey Through Time',
        author: 'Amelia Brooks',
        category: 'History',
        price: '19.00',
        image: '/images/book-image-25.webp',
    },
    {
        id: 'mind-wellness',
        title: 'Mind & Wellness',
        author: 'Oliver Hartman',
        category: 'Self-help',
        price: '15.00',
        image: '/images/book-image-18.webp',
    },
]
</script>

<style lang="scss" scoped>
@use "sass:color";

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

            &:hover {
                background: $color-lightest;
            }
        }

        span {
            width: 46px;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            color: $color-dark;
            border-left: 1.5px solid $color-light;
            border-right: 1.5px solid $color-light;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
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

    &__tab-nav {
        display: flex;
        gap: 0;
        border-bottom: 1px solid $color-light;
        margin-bottom: 32px;
    }

    &__tab-btn {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        font-family: $font-body;
        color: $color-gray;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s;

        &:hover {
            color: $color-dark;
        }

        &--active {
            color: $color-dark;
            border-bottom-color: $color-primary;
        }
    }

    &__tab-content {
        font-size: 15px;
        color: $color-gray;
        line-height: 1.75;
    }

    &__no-reviews {
        margin-bottom: 24px;
        color: $color-gray;
        font-size: 14px;
    }

    &__review-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-width: 560px;

        textarea,
        input {
            padding: 11px 14px;
            border: 1.5px solid $color-light;
            border-radius: 6px;
            font-size: 14px;
            font-family: $font-body;
            outline: none;
            resize: vertical;

            &:focus {
                border-color: $color-primary;
            }
        }
    }

    &__review-rating {
        display: flex;
        flex-direction: column;
        gap: 8px;

        label {
            font-size: 14px;
            font-weight: 600;
            color: $color-dark;
        }
    }

    &__stars {
        display: flex;
        gap: 4px;
    }

    &__star-btn {
        font-size: 24px;
        color: $color-light;
        cursor: pointer;
        transition: color 0.15s;
        font-family: inherit;

        &--filled {
            color: #f5a623;
        }

        &:hover {
            color: #f5a623;
        }
    }

    &__review-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    &__review-submit {
        align-self: flex-start;
        padding: 11px 28px;
        background: $color-primary;
        color: $color-white;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        font-family: $font-body;
        cursor: pointer;
        transition: background 0.2s;

        &:hover {
            background: color.adjust($color-primary, $lightness: -8%);
        }
    }

    &__related-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-top: 32px;
    }
}

</style>
