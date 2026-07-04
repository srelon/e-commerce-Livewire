<template>
    <PageBanner :title="page_title" :loading="is_loading" :parent="{ label: 'News', to: { name: 'news' } }" />

    <section class="post section">
        <div class="container">
            <div class="post__layout">
                <article class="post__main">
                    <template v-if="is_loading">
                        <div class="post__cover">
                            <BaseSkeleton radius="0" />
                        </div>
                        <div class="post__meta">
                            <BaseSkeleton width="80px" height="14px" />
                            <BaseSkeleton width="60px" height="14px" />
                            <BaseSkeleton width="100px" height="14px" />
                        </div>
                        <div class="post__content">
                            <BaseSkeleton height="16px" />
                            <BaseSkeleton height="16px" />
                            <BaseSkeleton height="16px" width="70%" />
                        </div>
                    </template>
                    <template v-else-if="post">
                        <div class="post__cover">
                            <img :src="to_storage_url(post.image)" :alt="post.title" class="post__cover-img">
                        </div>

                        <div class="post__meta">
                            <span class="post__category">{{ post.category }}</span>
                            <span class="post__dot">·</span>
                            <span class="post__author">{{ post.author }}</span>
                            <span class="post__dot">·</span>
                            <span class="post__date">{{ post.date }}</span>
                        </div>

                        <div class="post__content">
                            <p v-for="(para, i) in content_paragraphs" :key="i">{{ para }}</p>
                        </div>

                        <div class="post__footer">
                            <router-link :to="{ name: 'news' }" class="post__back">← Back to News</router-link>
                        </div>
                    </template>
                    <p v-else class="post__not-found">Post not found.</p>
                </article>

                <Sidebar :show_newsletter="true" />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import Sidebar from '@/components/ui/sidebar/Sidebar.vue'
import api from '@/plugins/axios'
import { to_storage_url } from '@/stores/layout'
import type { BlogPostDetail } from '@/types/shop'

interface Props {
    slug?: string
}

const props = withDefaults(defineProps<Props>(), {
    slug: '',
})

const is_loading = ref(true)
const post = ref<BlogPostDetail | null>(null)

const page_title = computed(() => post.value?.title ?? (is_loading.value ? '' : 'Post Not Found'))
const content_paragraphs = computed(() => (post.value?.content ?? '').split('\n\n').filter(Boolean))

function fetch_post() {
    if (!props.slug) return

    is_loading.value = true
    api.get(`news/${props.slug}`).then((res) => {
        post.value = res.data.data
    }).catch(() => {
        post.value = null
    }).finally(() => {
        is_loading.value = false
    })
}

watch(() => props.slug, fetch_post, { immediate: true })
</script>

<style lang="scss" scoped>
.post {
    &__layout {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    &__main {
        flex: 1;
        min-width: 0;
    }

    &__not-found {
        font-size: 16px;
        color: $color-gray;
    }

    &__cover {
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 28px;
        aspect-ratio: 16 / 9;
    }

    &__cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    &__meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: $color-gray;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    &__category {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: $color-primary;
    }

    &__dot {
        color: $color-light;
    }

    &__content {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 40px;

        p {
            font-size: 16px;
            color: $color-dark;
            line-height: 1.8;
        }
    }

    &__footer {
        padding-top: 24px;
        border-top: 1px solid $color-light;
    }

    &__back {
        font-size: 14px;
        font-weight: 600;
        color: $color-dark;
        transition: color 0.2s;

        &:hover {
            color: $color-primary;
        }
    }
}
</style>
