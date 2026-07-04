<template>
    <div class="page-banner">
        <div class="container">
            <h1 class="page-banner__title">{{ title }}</h1>
            <nav class="page-banner__breadcrumb" aria-label="Breadcrumb">
                <router-link :to="{ name: 'home' }">Home</router-link>
                <span class="page-banner__sep">›</span>
                <span>{{ title }}</span>
            </nav>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    title: string
}

defineProps<Props>()
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
