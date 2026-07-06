<template>
    <section class="team section">
        <div class="container">
            <h2 class="section__title">Meet The People Behind Our Bookstore</h2>
            <div class="team__grid">
                <template v-if="loading">
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
</template>

<script setup lang="ts">
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import type { TeamMember } from '@/types/global'

interface Props {
    team: TeamMember[]
    loading?: boolean
}

withDefaults(defineProps<Props>(), {
    loading: false,
})
</script>

<style lang="scss" scoped>
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
</style>
