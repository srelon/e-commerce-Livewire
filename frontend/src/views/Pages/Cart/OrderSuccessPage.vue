<template>
    <PageBanner title="Order Confirmed" />

    <section class="order-success section">
        <div class="container">
            <div class="order-success__inner">
                <h2 class="order-success__title">Thank you for your order!</h2>
                <p class="order-success__text">
                    Order <strong>№{{ order?.public_id }}</strong> has been successfully placed.
                </p>

                <router-link :to="{ name: 'products' }" class="order-success__link">
                    <BaseButton variant="primary">Back to Shopping</BaseButton>
                </router-link>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import PageBanner from '@/components/ui/base/PageBanner.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import { useShopStore } from '@/stores/shop'

const order = useShopStore().consume_last_order()
</script>

<style lang="scss" scoped>
.order-success {
    &__inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 16px;
        padding: 40px 0;
    }

    &__title {
        font-size: 24px;
        font-weight: 700;
        color: $color-dark;
    }

    &__text {
        font-size: 16px;
        color: $color-gray;
    }

    &__link {
        margin-top: 12px;
    }
}
</style>
