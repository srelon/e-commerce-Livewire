<template>
    <section class="blog section">
        <div class="container">
            <div class="section__header">
                <h2 class="section__title">{{ title }}</h2>
                <router-link :to="view_all_href" class="blog__view-all">
                    {{ view_all_label }}
                    <svg viewBox="0 0 15 15" aria-hidden="true">
                        <path d="M1 15a1 1 0 0 1-.707-1.707L11.586 2H1.52a1 1 0 0 1 0-2h12.483q.202.002.379.075a1 1 0 0 1 .542.543 1 1 0 0 1 .076.38V13.48a1 1 0 1 1-2 0V3.414L1.707 14.707A1 1 0 0 1 1 15"/>
                    </svg>
                </router-link>
            </div>

            <div v-if="loading || featured_post" class="blog__grid">
                <div class="blog__col blog__col--list">
                    <template v-if="loading">
                        <BlogCard v-for="n in 3" :key="n" :horizontal="true" loading />
                    </template>
                    <template v-else>
                        <BlogCard
                            v-for="post in left_posts"
                            :key="post.slug"
                            :title="post.title"
                            :date="format_date(post.date)"
                            :image="to_storage_url(post.image)"
                            :slug="post.slug"
                            :horizontal="true"
                        />
                    </template>
                </div>

                <div class="blog__col blog__col--featured">
                    <div v-if="loading" class="blog-featured">
                        <BaseSkeleton radius="0" />
                    </div>
                    <article v-else class="blog-featured">
                        <img :src="to_storage_url(featured_post!.image)" :alt="featured_post!.title" class="blog-featured__img">
                        <div class="blog-featured__overlay">
                            <time class="blog-featured__date">{{ format_date(featured_post!.date) }}</time>
                            <h2 class="blog-featured__title">
                                <router-link :to="{ name: 'post', params: { slug: featured_post!.slug } }">{{ featured_post!.title }}</router-link>
                            </h2>
                            <p v-if="featured_post!.category" class="blog-featured__cats">In {{ featured_post!.category }}</p>
                        </div>
                    </article>
                </div>

                <div class="blog__col blog__col--list">
                    <template v-if="loading">
                        <BlogCard v-for="n in 3" :key="n" :horizontal="true" loading />
                    </template>
                    <template v-else>
                        <BlogCard
                            v-for="post in right_posts"
                            :key="post.slug"
                            :title="post.title"
                            :date="format_date(post.date)"
                            :image="to_storage_url(post.image)"
                            :slug="post.slug"
                            :horizontal="true"
                        />
                    </template>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BlogCard from '@/components/ui/blog/BlogCard.vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import { to_storage_url } from '@/stores/layout'
import type { BlogPostSummary } from '@/types/shop'
import type { RouteLocationRaw } from 'vue-router'

interface Props {
    posts?: BlogPostSummary[]
    loading?: boolean
    title?: string
    view_all_label?: string
    view_all_href?: RouteLocationRaw
}

const props = withDefaults(defineProps<Props>(), {
    posts: () => [],
    loading: false,
    title: 'Latest Blog Posts',
    view_all_label: 'View All Posts',
    view_all_href: () => ({ name: 'news' }),
})

const featured_post = computed(() => props.posts[0] ?? null)
const left_posts = computed(() => props.posts.slice(1, 4))
const right_posts = computed(() => props.posts.slice(4, 7))

function format_date(date: string | null): string {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en', { month: 'long', day: 'numeric', year: 'numeric' })
}
</script>

<style lang="scss" scoped>
.blog {
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
        grid-template-columns: 1fr 1fr 1fr;
        gap: 24px;
        align-items: stretch;
    }

    &__col {
        display: flex;
        flex-direction: column;
        gap: 24px;

        &--featured {
            display: flex;
        }
    }
}

.blog-featured {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    min-height: 300px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;

    &__img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    &__overlay {
        position: relative;
        padding: 24px;
        background: linear-gradient(0deg, rgba(0, 0, 0, 0.75) 0%, transparent 70%);
        color: $color-white;
    }

    &__date {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 8px;
    }

    &__title {
        font-size: clamp(16px, 1.4vw, 22px);
        font-weight: 700;
        color: $color-white;
        line-height: 1.35;
        margin-bottom: 8px;

        a {
            color: inherit;
            transition: color 0.2s;

            &:hover {
                color: $color-primary-light;
            }
        }
    }

    &__cats {
        font-size: 11px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.7);
    }
}
</style>
