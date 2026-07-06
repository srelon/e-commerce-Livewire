<template>
    <div class="about-perks">
        <div class="container">
            <div class="about-perks__grid">
                <template v-if="loading">
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
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import type { Perk } from '@/types/global'

interface Props {
    perks: Perk[]
    loading?: boolean
}

withDefaults(defineProps<Props>(), {
    loading: false,
})
</script>

<style lang="scss" scoped>
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
