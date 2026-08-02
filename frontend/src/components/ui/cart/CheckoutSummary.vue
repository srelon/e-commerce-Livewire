<template>
    <aside class="checkout-summary">
        <div class="checkout-summary__box">
            <h3 class="checkout-summary__title">Order Summary</h3>

            <div class="checkout-summary__row">
                <span>Order Subtotal</span>
                <span>${{ store.cart_total.toFixed(2) }}</span>
            </div>
            <div class="checkout-summary__row">
                <span>Shipping Cost</span>
                <span>{{ delivery_cost_label }}</span>
            </div>
            <p class="checkout-summary__note">
                Shipping is paid separately at the carrier's rates and is not included in the order total.
            </p>

            <div class="checkout-summary__divider" />

            <div class="checkout-summary__row checkout-summary__row--total">
                <span>Total to Pay</span>
                <span>${{ store.cart_total.toFixed(2) }}</span>
            </div>

            <BaseButton
                type="button"
                class="checkout-summary__confirm"
                :disabled="!can_confirm"
                :loading="is_placing_order"
                @click="$emit('confirm')"
            >
                Place Order
            </BaseButton>
        </div>
    </aside>
</template>

<script setup lang="ts">
import BaseButton from '@/components/ui/base/BaseButton.vue'
import { useShopStore } from '@/stores/shop'

defineProps<{
    delivery_cost_label: string
    can_confirm: boolean
    is_placing_order: boolean
}>()

defineEmits<{ confirm: [] }>()

const store = useShopStore()
</script>

<style lang="scss" scoped>
.checkout-summary {
    position: sticky;
    top: 90px;

    &__box {
        border: 1.5px solid $color-light;
        border-radius: 10px;
        padding: 24px;
    }

    &__title {
        font-size: 16px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 16px;
    }

    &__row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: $color-gray;
        margin-bottom: 8px;

        span:last-child {
            font-weight: 600;
            color: $color-dark;
        }

        &--total {
            font-size: 16px;
            font-weight: 700;
            color: $color-dark;
            margin-bottom: 0;

            span:last-child {
                color: $color-primary;
                font-size: 18px;
            }
        }
    }

    &__note {
        font-size: 12px;
        color: $color-gray;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    &__divider {
        height: 1px;
        background: $color-light;
        margin: 16px 0;
    }

    &__confirm {
        width: 100%;
        margin-top: 20px;
    }
}
</style>
