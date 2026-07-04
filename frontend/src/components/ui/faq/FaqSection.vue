<template>
    <section class="faq section">
        <div class="container">
            <h2 class="section__title">{{ title }}</h2>
            <div class="faq__list">
                <template v-if="loading">
                    <div v-for="n in 6" :key="n" class="faq__item">
                        <div class="faq__question">
                            <BaseSkeleton width="70%" height="15px" />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div
                        v-for="(item, index) in items"
                        :key="index"
                        class="faq__item"
                        :class="{ 'faq__item--open': open_index === index }"
                    >
                        <button class="faq__question" @click="toggle(index)">
                            {{ item.question }}
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="faq__icon">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>
                        <div class="faq__answer">
                            <p>{{ item.answer }}</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import type { FaqItem } from '@/types/global'

interface Props {
    title: string
    items?: FaqItem[]
    loading?: boolean
}

withDefaults(defineProps<Props>(), {
    items: () => [],
    loading: false,
})

const open_index = ref<number | null>(0)

function toggle(index: number) {
    open_index.value = open_index.value === index ? null : index
}
</script>

<style lang="scss" scoped>
.faq {
    background: $color-lightest;

    &__list {
        margin-top: 40px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    &__item {
        border-bottom: 1px solid $color-light;

        &:first-child {
            border-top: 1px solid $color-light;
        }

        &--open {
            .faq__answer {
                max-height: 300px;
                opacity: 1;
                padding: 0 20px 20px;
            }

            .faq__icon {
                transform: rotate(180deg);
            }
        }
    }

    &__question {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 20px;
        text-align: left;
        font-size: 15px;
        font-weight: 600;
        color: $color-dark;
        font-family: $font-body;
        cursor: pointer;
        gap: 16px;
        transition: color 0.2s;

        &:hover {
            color: $color-primary;
        }
    }

    &__icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        transition: transform 0.25s;

        path {
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
    }

    &__answer {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.3s ease, padding 0.3s ease;
        padding: 0 20px;

        p {
            font-size: 14px;
            color: $color-gray;
            line-height: 1.7;
        }
    }
}
</style>
