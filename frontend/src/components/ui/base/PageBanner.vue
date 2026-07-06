<template>
    <div class="page-banner">
        <div class="container">
            <template v-if="loading">
                <BaseSkeleton width="220px" height="24px" />
            </template>
            <template v-else>
                <h1 class="page-banner__title">{{ title }}</h1>
                <nav class="page-banner__breadcrumb" aria-label="Breadcrumb">
                    <router-link :to="{ name: 'home' }">Home</router-link>
                    <span class="page-banner__sep">›</span>
                    <template v-if="parent">
                        <router-link :to="parent.to">{{ parent.label }}</router-link>
                        <span class="page-banner__sep">›</span>
                    </template>
                    <span>{{ title }}</span>
                </nav>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import type { RouteLocationRaw } from 'vue-router'

interface Crumb {
    label: string
    to: RouteLocationRaw
}

interface Props {
    title?: string
    loading?: boolean
    parent?: Crumb
}

withDefaults(defineProps<Props>(), {
    title: '',
    loading: false,
})
</script>

<style lang="scss" scoped>
.page-banner {
    background: $color-dark;
    padding: 24px 0;

    &__title {
        font-size: clamp(18px, 2vw, 26px);
        color: $color-white;
        font-weight: 700;
        margin-bottom: 6px;
        line-height: 1.2;
    }

    &__breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;

        a {
            color: rgba(255, 255, 255, 0.6);
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }

        span:last-child {
            color: $color-primary;
        }
    }

    &__sep {
        color: rgba(255, 255, 255, 0.3);
        font-size: 16px;
    }
}
</style>
