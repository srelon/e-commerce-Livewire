<template>
    <PageBanner :title="post.title" />

    <section class="post section">
        <div class="container">
            <div class="post__layout">
                <article class="post__main">
                    <div class="post__cover">
                        <img :src="post.image" :alt="post.title" class="post__cover-img">
                    </div>

                    <div class="post__meta">
                        <span class="post__category">{{ post.category }}</span>
                        <span class="post__dot">·</span>
                        <span class="post__author">{{ post.author }}</span>
                        <span class="post__dot">·</span>
                        <span class="post__date">{{ post.date }}</span>
                    </div>

                    <div class="post__content">
                        <p v-for="(para, i) in post.content" :key="i">{{ para }}</p>
                    </div>

                    <div class="post__footer">
                        <router-link :to="{ name: 'news' }" class="post__back">← Back to News</router-link>
                    </div>
                </article>

                <Sidebar :show_newsletter="true" :recent_posts="related_posts" />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import PageBanner from '@/components/ui/base/PageBanner.vue'
import Sidebar from '@/components/ui/sidebar/Sidebar.vue'

const post = {
    title: 'Blandit Praesent Morbi Faucibus',
    category: 'Literature',
    author: 'Admin',
    date: 'December 20, 2025',
    image: '/images/blog-image-3.webp',
    content: [
        'Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; proin vel ante a orci tempus eleifend ut et magna.',
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur aliquet quam id dui posuere blandit. Nulla porttitor accumsan tincidunt. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Cras ultricies ligula sed magna dictum porta.',
        'Sed porttitor lectus nibh. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Donec rutrum congue leo eget malesuada. Quisque velit nisi, pretium ut lacinia in, elementum id enim. Pellentesque in ipsum id orci porta dapibus.',
        'Proin eget tortor risus. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Nulla quis lorem ut libero malesuada feugiat.',
    ],
}

const related_posts = [
    {
        title: 'Ornare Curabitur Vitae Scelerisque',
        date: 'December 18, 2025',
        image: '/images/blog-image-4.webp',
        slug: 'ornare-curabitur',
    },
    {
        title: 'Massa Fames Eleifend Convallis',
        date: 'December 15, 2025',
        image: '/images/blog-image-5.webp',
        slug: 'massa-fames',
    },
    {
        title: 'Porttitor Suspendisse Bibendum',
        date: 'December 12, 2025',
        image: '/images/blog-image-6.webp',
        slug: 'porttitor-suspendisse',
    },
    {
        title: 'Platea Justo Curabitur Consequat',
        date: 'December 10, 2025',
        image: '/images/blog-image-7.webp',
        slug: 'platea-justo',
    },
    {
        title: 'Volutpat Tempor Accumsan Porta',
        date: 'December 8, 2025',
        image: '/images/blog-image-8.webp',
        slug: 'volutpat-tempor',
    },
]
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
