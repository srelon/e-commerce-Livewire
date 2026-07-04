<template>
    <article class="blog-card" :class="{ 'blog-card--horizontal': horizontal }">
        <div v-if="loading" class="blog-card__img-wrap">
            <BaseSkeleton radius="0" />
        </div>
        <router-link v-else :to="to" class="blog-card__img-wrap">
            <img :src="image" :alt="title" class="blog-card__img">
        </router-link>

        <div class="blog-card__body">
            <template v-if="loading && horizontal">
                <div class="blog-card__skeleton-lines">
                    <BaseSkeleton width="70px" height="11px" />
                    <BaseSkeleton width="90%" height="14px" />
                </div>
            </template>
            <template v-else-if="loading">
                <BaseSkeleton width="80px" height="12px" />
                <BaseSkeleton width="90%" height="18px" />
                <BaseSkeleton width="100%" height="60px" />
                <BaseSkeleton width="140px" height="13px" />
            </template>
            <template v-else-if="horizontal">
                <time class="blog-card__date">{{ date }}</time>
                <h3 class="blog-card__title">
                    <router-link :to="to">{{ title }}</router-link>
                </h3>
            </template>
            <template v-else>
                <button class="blog-card__category" @click="go_to_filter('news', 'category', category)">{{ category }}</button>
                <h2 class="blog-card__title">
                    <router-link :to="to">{{ title }}</router-link>
                </h2>
                <p class="blog-card__excerpt">{{ excerpt }}</p>
                <div class="blog-card__meta">
                    <template v-if="author">
                        <span class="blog-card__author">{{ author }}</span>
                        <span class="blog-card__dot">·</span>
                    </template>
                    <span class="blog-card__date">{{ date }}</span>
                </div>
                <router-link :to="to" class="blog-card__read-more">
                    Read More
                    <svg viewBox="0 0 15 15" aria-hidden="true">
                        <path d="M1 15a1 1 0 0 1-.707-1.707L11.586 2H1.52a1 1 0 0 1 0-2h12.483q.202.002.379.075a1 1 0 0 1 .542.543 1 1 0 0 1 .076.38V13.48a1 1 0 1 1-2 0V3.414L1.707 14.707A1 1 0 0 1 1 15"/>
                    </svg>
                </router-link>
            </template>
        </div>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import { useShopFilterNav } from '@/composables/useShopFilterNav'

interface Props {
    slug?: string
    title?: string
    date?: string
    image?: string
    horizontal?: boolean
    excerpt?: string
    category?: string
    author?: string
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    slug: '',
    title: '',
    date: '',
    image: '',
    horizontal: false,
    excerpt: '',
    category: '',
    author: '',
    loading: false,
})

const { go_to_filter } = useShopFilterNav()

const to = computed(() => ({ name: 'post', params: { slug: props.slug } }))
</script>

<style lang="scss" scoped>
@use "sass:color";

.blog-card {
    display: flex;
    flex-direction: column;
    border-radius: 10px;
    overflow: hidden;
    background: $color-white;
    border: 1px solid $color-light;
    transition: box-shadow 0.2s;

    &:hover {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    &--horizontal {
        flex-direction: row;
        gap: 14px;
        align-items: flex-start;
        border: none;
        border-radius: 0;
        background: transparent;
        overflow: visible;

        &:hover {
            box-shadow: none;
        }
    }

    &__img-wrap {
        display: block;
        overflow: hidden;

        .blog-card:not(.blog-card--horizontal) & {
            aspect-ratio: 16 / 10;
        }

        .blog-card--horizontal & {
            flex-shrink: 0;
            width: 50%;
            border-radius: 8px;
        }
    }

    &__img {
        width: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;

        .blog-card:not(.blog-card--horizontal) & {
            height: 100%;
        }

        .blog-card--horizontal & {
            aspect-ratio: 4 / 3;
        }

        .blog-card:hover & {
            transform: scale(1.04);
        }
    }

    &__body {
        flex: 1;

        .blog-card:not(.blog-card--horizontal) & {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .blog-card--horizontal & {
            padding-top: 4px;
        }
    }

    &__category {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: $color-primary;
        font-family: $font-body;
        background: none;
        border: none;
        padding: 0;
        align-self: flex-start;
        cursor: pointer;
        transition: color 0.2s;

        &:hover {
            color: color.adjust($color-primary, $lightness: -8%);
        }
    }

    &__title {
        font-size: 18px;
        font-weight: 700;
        line-height: 1.35;

        .blog-card--horizontal & {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.45;
        }

        a {
            color: $color-dark;
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__excerpt {
        font-size: 14px;
        color: $color-gray;
        line-height: 1.65;
        flex: 1;
    }

    &__meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: $color-gray;
    }

    &__dot {
        color: $color-light;
    }

    &__date {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: $color-gray;

        .blog-card--horizontal & {
            display: block;
            margin-bottom: 6px;
        }
    }

    &__skeleton-lines {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    &__read-more {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: $color-dark;
        transition: color 0.2s;

        svg {
            width: 11px;
            height: 11px;
            fill: currentColor;
            transition: transform 0.2s;
        }

        &:hover {
            color: $color-primary;

            svg {
                transform: translate(2px, -2px);
            }
        }
    }
}
</style>
