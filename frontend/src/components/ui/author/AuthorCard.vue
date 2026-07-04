<template>
    <div class="author-card">
        <div v-if="loading" class="author-card__avatar">
            <BaseSkeleton circle />
        </div>
        <router-link
            v-else
            :to="{ name: 'products', query: { author: name } }"
            class="author-card__avatar"
            :style="{ background: color }"
        >
            {{ initials }}
        </router-link>
        <div class="author-card__body">
            <template v-if="loading">
                <div class="author-card__skeleton-lines">
                    <BaseSkeleton width="160px" height="18px" />
                    <BaseSkeleton width="100%" height="14px" />
                    <BaseSkeleton width="90%" height="14px" />
                    <BaseSkeleton width="120px" height="13px" />
                    <BaseSkeleton width="120px" height="13px" />
                </div>
            </template>
            <template v-else>
                <h3 class="author-card__name">
                    <router-link :to="{ name: 'products', query: { author: name } }">{{ name }}</router-link>
                </h3>
                <p class="author-card__bio">{{ bio }}</p>
                <div class="author-card__stats">
                    <span class="author-card__books">Selling books: {{ books }}</span>
                    <span class="author-card__bestsellers">Bestsellers: {{ bestsellers }}</span>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'

interface Props {
    name?: string
    initials?: string
    color?: string
    bio?: string
    books?: number
    bestsellers?: number
    loading?: boolean
}

withDefaults(defineProps<Props>(), {
    name: '',
    initials: '',
    color: '',
    bio: '',
    books: 0,
    bestsellers: 0,
    loading: false,
})
</script>

<style lang="scss" scoped>
.author-card {
    display: flex;
    gap: 24px;
    align-items: flex-start;
    padding: 24px;
    border: 1px solid $color-light;
    border-radius: 12px;
    transition: box-shadow 0.2s;

    &:hover {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }

    &__avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        color: $color-white;
        flex-shrink: 0;
        font-family: $font-heading;
    }

    &__body {
        flex: 1;
    }

    &__name {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;

        a {
            color: $color-dark;
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__bio {
        font-size: 14px;
        color: $color-gray;
        line-height: 1.65;
        margin-bottom: 12px;
    }

    &__stats {
        display: flex;
        gap: 16px;
    }

    &__books,
    &__bestsellers {
        font-size: 13px;
        font-weight: 600;
        color: $color-primary;
    }

    &__skeleton-lines {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
}
</style>
