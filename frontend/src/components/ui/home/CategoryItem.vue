<template>
    <router-link :to="{ name: 'products', query: { category: name } }" class="cats__item">
        <div class="cats__icon">
            <img :src="icon" :alt="name" width="40" height="40">
        </div>
        <h5 class="cats__name">{{ name }}</h5>
        <span class="cats__count">{{ count }} books</span>
    </router-link>
</template>

<script setup lang="ts">
interface Props {
    name: string
    icon: string
    count: number
}

defineProps<Props>()
</script>

<style lang="scss" scoped>
.cats__item {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 16px 20px;
    border-right: 1px solid $color-light;
    transition: color 0.2s;
    color: $color-dark;

    &:last-child {
        border-right: none;
    }

    &:hover {
        color: $color-primary;

        .cats__icon {
            background: rgba($color-primary, 0.08);
        }
    }
}

.cats__icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: $color-lightest;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    transition: background 0.2s;

    img {
        width: 28px;
        height: 28px;
        object-fit: contain;
    }
}

.cats__name {
    font-size: 13px;
    font-weight: 600;
    color: inherit;
    margin-bottom: 4px;
    white-space: nowrap;
}

.cats__count {
    font-size: 12px;
    color: $color-gray;
}
</style>
