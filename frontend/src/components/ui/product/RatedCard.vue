<template>
    <div class="rated-card">
        <div v-if="loading" class="rated-card__img-wrap">
            <BaseSkeleton radius="6px" />
        </div>
        <router-link v-else :to="to" class="rated-card__img-wrap">
            <img :src="image" :alt="title" class="rated-card__img">
        </router-link>

        <div v-if="loading" class="rated-card__info">
            <BaseSkeleton width="80%" height="14px" />
            <BaseSkeleton width="60px" height="12px" />
            <BaseSkeleton width="40%" height="12px" />
            <BaseSkeleton width="50px" height="16px" />
        </div>
        <div v-else class="rated-card__info">
            <h3 class="rated-card__title">
                <router-link :to="to">{{ title }}</router-link>
            </h3>
            <div v-if="rating" class="rated-card__stars" :aria-label="`Rated ${rating} out of 5`">
                <span
                    v-for="i in 5"
                    :key="i"
                    class="rated-card__star"
                    :class="{ 'rated-card__star--filled': i <= Math.round(rating) }"
                >★</span>
            </div>
            <button class="rated-card__category" @click="go_to_filter('products', 'category', category)">{{ category }}</button>
            <span class="rated-card__price">${{ price }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import { useShopFilterNav } from '@/composables/useShopFilterNav'

interface Props {
    slug?: string
    title?: string
    category?: string
    price?: string
    rating?: number
    image?: string
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    slug: '',
    title: '',
    category: '',
    price: '',
    image: '',
    loading: false,
})

const { go_to_filter } = useShopFilterNav()

const to = computed(() => ({ name: 'product', params: { slug: props.slug } }))
</script>

<style lang="scss" scoped>
.rated-card {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    background: $color-white;
    border-radius: 10px;
    padding: 16px;

    &__img-wrap {
        flex-shrink: 0;
        width: 70px;
        border-radius: 6px;
        overflow: hidden;
    }

    &__img {
        width: 70px;
        aspect-ratio: 2 / 3;
        object-fit: cover;
        display: block;
        border-radius: 6px;
        transition: transform 0.3s ease;

        .rated-card:hover & {
            transform: scale(1.04);
        }
    }

    &__info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    &__title {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;

        a {
            color: $color-dark;
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__stars {
        display: flex;
        gap: 2px;
    }

    &__star {
        font-size: 14px;
        color: $color-lighter;

        &--filled {
            color: #f4a41e;
        }
    }

    &__category {
        font-size: 12px;
        color: $color-gray;
        font-family: $font-body;
        background: none;
        border: none;
        padding: 0;
        text-align: left;
        cursor: pointer;
        transition: color 0.2s;

        &:hover {
            color: $color-primary;
        }
    }

    &__price {
        font-size: 16px;
        font-weight: 700;
        color: $color-primary;
        margin-top: 4px;
    }
}
</style>
