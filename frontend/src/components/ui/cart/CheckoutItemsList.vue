<template>
    <div class="checkout-items">
        <div class="checkout-items__head">
            <h3 class="checkout-items__title">Order Items ({{ store.cart_count }})</h3>
            <BaseButton type="button" variant="text" @click="store.open_popup()">
                Edit Items
            </BaseButton>
        </div>

        <div class="checkout-items__list">
            <div v-for="item in store.cart_items" :key="item.id" class="checkout-items__item">
                <img :src="item.image" :alt="item.title" class="checkout-items__item-img">
                <div class="checkout-items__item-info">
                    <p class="checkout-items__item-title">{{ item.title }}</p>
                    <p class="checkout-items__item-author">{{ item.author }}</p>
                </div>
                <span class="checkout-items__item-qty">x{{ item.quantity }}</span>
                <span class="checkout-items__item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import BaseButton from '@/components/ui/base/BaseButton.vue'
import { useShopStore } from '@/stores/shop'

const store = useShopStore()
</script>

<style lang="scss" scoped>
.checkout-items {
    border: 1.5px solid $color-light;
    border-radius: 10px;
    padding: 20px 24px;

    &__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    &__title {
        font-size: 16px;
        font-weight: 700;
        color: $color-dark;
    }

    &__list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    &__item {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    &__item-img {
        width: 48px;
        height: 64px;
        object-fit: cover;
        border-radius: 6px;
        flex-shrink: 0;
    }

    &__item-info {
        flex: 1;
        min-width: 0;
    }

    &__item-title {
        font-size: 14px;
        font-weight: 600;
        color: $color-dark;
        line-height: 1.3;
    }

    &__item-author {
        font-size: 12px;
        color: $color-gray;
    }

    &__item-qty {
        font-size: 13px;
        color: $color-gray;
        flex-shrink: 0;
    }

    &__item-price {
        font-size: 14px;
        font-weight: 700;
        color: $color-primary;
        flex-shrink: 0;
        min-width: 60px;
        text-align: right;
    }
}
</style>
