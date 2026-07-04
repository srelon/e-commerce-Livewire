<template>
    <PageBanner :title="page?.title ?? 'About Us'" />

    <section class="about section">
        <div class="container">
            <div class="about__intro">
                <h2 class="about__quote">{{ page?.excerpt ?? 'The Right Book In The Right Hands At The Right Time Can Change The World' }}</h2>
                <div class="about__intro-side">
                    <p class="about__intro-desc">{{ page?.content ?? 'We are a passionate team of book lovers dedicated to connecting readers with stories that inspire, educate, and transform. Our curated collection spans every genre.' }}</p>
                    <ul v-if="layout_store.loaded" class="about__contact-list">
                        <li v-for="item in contact_info" :key="item.label">
                            <svg viewBox="0 0 15 15" aria-hidden="true"><path :d="item.icon ?? ''"/></svg>
                            <a v-if="item.href" :href="item.href">{{ item.value }}</a>
                            <span v-else>{{ item.value }}</span>
                        </li>
                    </ul>
                    <ul v-else class="about__contact-list">
                        <li v-for="n in 3" :key="n">
                            <BaseSkeleton width="16px" height="16px" circle />
                            <BaseSkeleton width="160px" height="14px" />
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="team section">
        <div class="container">
            <h2 class="section__title">Meet The People Behind Our Bookstore</h2>
            <div class="team__grid">
                <template v-if="is_loading">
                    <div v-for="n in 8" :key="n" class="team__card">
                        <BaseSkeleton width="64px" height="64px" circle class="team__avatar" />
                        <BaseSkeleton width="80%" height="16px" class="team__name" />
                        <BaseSkeleton width="60%" height="12px" class="team__role" />
                        <BaseSkeleton width="100%" height="36px" class="team__bio" />
                    </div>
                </template>
                <template v-else>
                    <div v-for="member in team" :key="member.name" class="team__card">
                        <div class="team__avatar" :style="{ background: member.color }">
                            {{ member.initials }}
                        </div>
                        <h3 class="team__name">{{ member.name }}</h3>
                        <span class="team__role">{{ member.role }}</span>
                        <p class="team__bio">{{ member.bio }}</p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <section class="about-cta">
        <div class="container">
            <div class="about-cta__inner">
                <div class="about-cta__left">
                    <h2 class="about-cta__title">Immerse Yourself In The Fascinating World Of Literature</h2>
                    <router-link :to="{ name: 'products' }" class="about-cta__btn">Explore Collection</router-link>
                </div>
                <div class="about-cta__right">
                    <p class="about-cta__sub">Join our community</p>
                    <p class="about-cta__count">58,000+ subscribers</p>
                    <form class="about-cta__form" @submit.prevent>
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="about-perks">
        <div class="container">
            <div class="about-perks__grid">
                <template v-if="is_loading">
                    <div v-for="n in 4" :key="n" class="about-perks__item">
                        <BaseSkeleton width="40px" height="40px" class="about-perks__icon" />
                        <div>
                            <BaseSkeleton width="100px" height="15px" class="about-perks__title" />
                            <BaseSkeleton width="140px" height="13px" class="about-perks__desc" />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div v-for="perk in perks" :key="perk.title" class="about-perks__item">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="about-perks__icon">
                            <path :d="perk.icon"/>
                        </svg>
                        <div>
                            <h4 class="about-perks__title">{{ perk.title }}</h4>
                            <p class="about-perks__desc">{{ perk.desc }}</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import api from '@/plugins/axios'
import { useLayoutStore } from '@/stores/layout'
import { useContactList } from '@/composables/useContactList'
import type { PageBundle } from '@/types/shop'
import type { Perk, TeamMember } from '@/types/global'

const layout_store = useLayoutStore()
const contact_info = useContactList()

const is_loading = ref(true)
const team = ref<TeamMember[]>([])
const perks = ref<Perk[]>([])
const page = ref<PageBundle | null>(null)

onMounted(() => {
    api.get('pages/about').then((res) => {
        team.value = res.data.data.team
        perks.value = res.data.data.perks
        page.value = res.data.data.page
    }).finally(() => {
        is_loading.value = false
    })
})
</script>

<style lang="scss" scoped>
@use "sass:color";

.about {
    &__intro {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: start;
    }

    &__quote {
        font-size: clamp(20px, 2.2vw, 32px);
        font-weight: 700;
        color: $color-dark;
        line-height: 1.3;
    }

    &__intro-desc {
        font-size: 15px;
        color: $color-gray;
        line-height: 1.7;
        margin-bottom: 24px;
    }

    &__contact-list {
        display: flex;
        flex-direction: column;
        gap: 12px;

        li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            color: $color-dark;
        }

        svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 2px;

            path {
                fill: $color-primary;
            }
        }

        a {
            color: $color-dark;

            &:hover {
                color: $color-primary;
            }
        }
    }
}

.team {
    background: $color-lightest;

    &__grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-top: 40px;
    }

    &__card {
        background: $color-white;
        border-radius: 12px;
        padding: 28px 20px;
        text-align: center;
        border: 1px solid $color-light;
        transition: box-shadow 0.2s;

        &:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }
    }

    &__avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        color: $color-white;
        margin: 0 auto 16px;
        font-family: $font-heading;
    }

    &__name {
        font-size: 16px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 4px;
    }

    &__role {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: $color-primary;
        display: block;
        margin-bottom: 12px;
    }

    &__bio {
        font-size: 13px;
        color: $color-gray;
        line-height: 1.6;
    }
}

.about-cta {
    background: $color-dark;
    padding: 60px 0;

    &__inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    &__title {
        font-size: clamp(20px, 2vw, 30px);
        color: $color-white;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 24px;
    }

    &__btn {
        display: inline-flex;
        align-items: center;
        padding: 13px 28px;
        background: $color-primary;
        color: $color-white;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        transition: background 0.2s;

        &:hover {
            background: color.adjust($color-primary, $lightness: -8%);
        }
    }

    &__sub {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.5);
        margin-bottom: 4px;
    }

    &__count {
        font-size: 28px;
        font-weight: 700;
        color: $color-primary;
        margin-bottom: 20px;
    }

    &__form {
        display: flex;
        gap: 8px;

        input {
            flex: 1;
            padding: 12px 16px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            font-family: $font-body;
            outline: none;
        }

        button {
            padding: 12px 24px;
            background: $color-primary;
            color: $color-white;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: $font-body;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;

            &:hover {
                background: color.adjust($color-primary, $lightness: -8%);
            }
        }
    }
}

.about-perks {
    padding: 40px 0;
    background: $color-lightest;

    &__grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    &__item {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    &__icon {
        width: 40px;
        height: 40px;
        flex-shrink: 0;

        path {
            fill: none;
            stroke: $color-primary;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
    }

    &__title {
        font-size: 15px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 2px;
    }

    &__desc {
        font-size: 13px;
        color: $color-gray;
    }
}
</style>
