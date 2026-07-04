<template>
    <section v-if="loading || author" class="best-author section">
        <div class="container">
            <div class="best-author__inner">
                <div class="best-author__left">
                    <h2 class="best-author__title">Best Author<br>Of The Month</h2>

                    <div class="best-author__awards">
                        <div class="best-author__award">
                            <strong>Best</strong>
                            <span>Author</span>
                        </div>
                        <div class="best-author__award">
                            <strong>#1</strong>
                            <span>Best Seller</span>
                        </div>
                    </div>

                    <p class="best-author__desc">This author received multiple awards and nominations for his work.</p>

                    <router-link :to="{ name: 'authors' }" class="best-author__btn">
                        Explore Collection
                        <svg viewBox="0 0 15 15" aria-hidden="true">
                            <path d="M1 15a1 1 0 0 1-.707-1.707L11.586 2H1.52a1 1 0 0 1 0-2h12.483q.202.002.379.075a1 1 0 0 1 .542.543 1 1 0 0 1 .076.38V13.48a1 1 0 1 1-2 0V3.414L1.707 14.707A1 1 0 0 1 1 15"/>
                        </svg>
                    </router-link>
                </div>

                <div class="best-author__center">
                    <BaseSkeleton v-if="loading" width="280px" height="380px" radius="6px" />
                    <img v-else :src="author_photo" :alt="author?.name" class="best-author__photo">
                </div>

                <div class="best-author__right">
                    <template v-if="loading">
                        <BaseSkeleton width="90%" height="22px" />
                        <BaseSkeleton width="60%" height="22px" />
                        <BaseSkeleton width="140px" height="16px" />
                    </template>
                    <template v-else>
                        <blockquote class="best-author__quote">
                            {{ author?.content }}
                        </blockquote>
                        <cite class="best-author__name">{{ author?.name }}</cite>
                    </template>

                    <div class="best-author__socials">
                        <a href="#" aria-label="Facebook" class="best-author__social">
                            <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M20,10.1c0-5.5-4.5-10-10-10S0,4.5,0,10.1c0,5,3.7,9.1,8.4,9.9v-7H5.9v-2.9h2.5V7.9C8.4,5.4,9.9,4,12.2,4c1.1,0,2.2,0.2,2.2,0.2v2.5h-1.3c-1.2,0-1.6,0.8-1.6,1.6v1.9h2.8L13.9,13h-2.3v7C16.3,19.2,20,15.1,20,10.1z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="X (Twitter)" class="best-author__social">
                            <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M2.9 0C1.3 0 0 1.3 0 2.9v14.3C0 18.7 1.3 20 2.9 20h14.3c1.6 0 2.9-1.3 2.9-2.9V2.9C20 1.3 18.7 0 17.1 0H2.9zm13.2 3.8L11.5 9l5.5 7.2h-4.3l-3.3-4.4-3.8 4.4H3.4l5-5.7-5.3-6.7h4.4l3 4 3.5-4h2.1zM14.4 15 6.8 5H5.6l7.7 10h1.1z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram" class="best-author__social">
                            <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true">
                                <circle cx="10" cy="10" r="3.3"/>
                                <path d="M14.2,0H5.8C2.6,0,0,2.6,0,5.8v8.3C0,17.4,2.6,20,5.8,20h8.3c3.2,0,5.8-2.6,5.8-5.8V5.8C20,2.6,17.4,0,14.2,0zM10,15c-2.8,0-5-2.2-5-5s2.2-5,5-5s5,2.2,5,5S12.8,15,10,15z M15.8,5C15.4,5,15,4.6,15,4.2s0.4-0.8,0.8-0.8s0.8,0.4,0.8,0.8S16.3,5,15.8,5z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="YouTube" class="best-author__social">
                            <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M15,0H5C2.2,0,0,2.2,0,5v10c0,2.8,2.2,5,5,5h10c2.8,0,5-2.2,5-5V5C20,2.2,17.8,0,15,0z M14.5,10.9l-6.8,3.8c-0.1,0.1-0.3,0.1-0.5,0.1c-0.5,0-1-0.4-1-1l0,0V6.2c0-0.5,0.4-1,1-1c0.2,0,0.3,0,0.5,0.1l6.8,3.8c0.5,0.3,0.7,0.8,0.4,1.3C14.8,10.6,14.6,10.8,14.5,10.9z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import { to_storage_url } from '@/stores/layout'
import type { AuthorSummary } from '@/types/shop'

interface Props {
    author?: AuthorSummary | null
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
})

const author_photo = computed(() =>
    props.author?.photo ? to_storage_url(props.author.photo) : '/images/best-author-1.webp',
)
</script>

<style lang="scss" scoped>
@use "sass:color";

.best-author {
    background: $color-dark;
    overflow: hidden;

    &__inner {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 60px;
        align-items: center;
    }

    &__left {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    &__title {
        font-size: clamp(28px, 2.5vw, 40px);
        color: $color-white;
        line-height: 1.2;
    }

    &__awards {
        display: flex;
        gap: 16px;
        align-items: center;
    }

    &__award {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 2px solid $color-primary;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 6px;
        gap: 2px;

        strong {
            font-size: 13px;
            font-weight: 800;
            color: $color-primary;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1;
        }

        span {
            font-size: 9px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.55);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            line-height: 1.2;
        }
    }

    &__desc {
        font-size: 15px;
        color: rgba(255, 255, 255, 0.65);
        line-height: 1.6;
    }

    &__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 13px 24px;
        border-radius: 6px;
        background: $color-primary;
        color: $color-white;
        font-size: 15px;
        font-weight: 600;
        align-self: flex-start;
        transition: background 0.2s;

        svg {
            width: 13px;
            height: 13px;
            fill: currentColor;
        }

        &:hover {
            background: color.adjust($color-primary, $lightness: -8%);
        }
    }

    &__center {
        flex-shrink: 0;
    }

    &__photo {
        width: 280px;
        height: auto;
        display: block;
        object-fit: cover;
    }

    &__right {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    &__quote {
        font-size: clamp(16px, 1.4vw, 22px);
        color: $color-white;
        font-style: italic;
        line-height: 1.5;
        border-left: 3px solid $color-primary;
        padding-left: 20px;
    }

    &__name {
        font-size: 15px;
        font-weight: 700;
        color: $color-primary;
        font-style: normal;
    }

    &__socials {
        display: flex;
        gap: 10px;
    }

    &__social {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;

        svg {
            fill: $color-white;
        }

        &:hover {
            background: $color-primary;
        }
    }
}
</style>
