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

    <TeamSection :team="team" :loading="is_loading" />
    <AboutCta />
    <AboutPerks :perks="perks" :loading="is_loading" />
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import TeamSection from '@/components/ui/about/TeamSection.vue'
import AboutCta from '@/components/ui/about/AboutCta.vue'
import AboutPerks from '@/components/ui/about/AboutPerks.vue'
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
</style>
