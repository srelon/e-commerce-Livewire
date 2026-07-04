<template>
    <PageBanner title="Checkout" />

    <section class="checkout section">
        <div class="container">
            <div class="checkout__inner">
                <div class="checkout__main">
                    <div class="checkout__items-block">
                        <div class="checkout__items-head">
                            <h3 class="checkout__items-title">Order Items ({{ store.cart_count }})</h3>
                            <BaseButton type="button" variant="text" @click="store.open_popup()">
                                Edit Items
                            </BaseButton>
                        </div>

                        <div class="checkout__items">
                            <div v-for="item in store.cart_items" :key="item.id" class="checkout__item">
                                <img :src="item.image" :alt="item.title" class="checkout__item-img">
                                <div class="checkout__item-info">
                                    <p class="checkout__item-title">{{ item.title }}</p>
                                    <p class="checkout__item-author">{{ item.author }}</p>
                                </div>
                                <span class="checkout__item-qty">x{{ item.quantity }}</span>
                                <span class="checkout__item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <CheckoutStep
                        :step_number="1"
                        title="Contact Details"
                        :active="current_step === 'contact'"
                        :done="done.contact"
                        @edit="open_step('contact')"
                    >
                        <ContactStep
                            :initial_data="contact"
                            @change="contact = $event"
                            @complete="on_complete('contact', $event)"
                        />
                        <template #summary>
                            <p>{{ contact.first_name }} {{ contact.last_name }}</p>
                            <p>{{ contact.phone }}</p>
                            <p>{{ contact.email }}</p>
                        </template>
                    </CheckoutStep>

                    <CheckoutStep
                        :step_number="2"
                        title="Delivery Method"
                        :active="current_step === 'delivery'"
                        :done="done.delivery"
                        @edit="open_step('delivery')"
                    >
                        <DeliveryStep
                            :initial_data="delivery"
                            @change="delivery = $event"
                            @complete="on_complete('delivery', $event)"
                        />
                        <template #summary>
                            <p v-if="delivery.method === 'pickup'">Pickup</p>
                            <p v-else>Nova Poshta — {{ delivery.city }}, {{ delivery.warehouse }}</p>
                        </template>
                    </CheckoutStep>

                    <CheckoutStep
                        :step_number="3"
                        title="Payment Method"
                        :active="current_step === 'payment'"
                        :done="done.payment"
                        @edit="open_step('payment')"
                    >
                        <PaymentStep
                            :initial_data="payment"
                            @change="payment = $event"
                            @complete="on_complete('payment', $event)"
                        />
                        <template #summary>
                            <p>{{ payment.method === 'card' ? 'Pay by Card Online' : 'Cash on Delivery' }}</p>
                        </template>
                    </CheckoutStep>
                </div>

                <aside class="checkout__sidebar">
                    <div class="checkout__summary">
                        <h3 class="checkout__summary-title">Order Summary</h3>

                        <div class="checkout__summary-row">
                            <span>Order Subtotal</span>
                            <span>${{ store.cart_total.toFixed(2) }}</span>
                        </div>
                        <div class="checkout__summary-row">
                            <span>Shipping Cost</span>
                            <span>{{ delivery_cost_label }}</span>
                        </div>
                        <p class="checkout__summary-note">
                            Shipping is paid separately at the carrier's rates and is not included in the order total.
                        </p>

                        <div class="checkout__summary-divider" />

                        <div class="checkout__summary-row checkout__summary-row--total">
                            <span>Total to Pay</span>
                            <span>${{ store.cart_total.toFixed(2) }}</span>
                        </div>

                        <BaseButton
                            type="button"
                            class="checkout__confirm"
                            :disabled="!can_confirm"
                            @click="confirm_order"
                        >
                            Place Order
                        </BaseButton>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import CheckoutStep from '@/components/ui/cart/CheckoutStep.vue'
import ContactStep from './ContactStep.vue'
import DeliveryStep from './DeliveryStep.vue'
import PaymentStep from './PaymentStep.vue'
import { useShopStore } from '@/stores/shop'
import { useCheckoutForm } from '@/composables/useCheckoutForm'
import type { ContactData, DeliveryData, PaymentData } from '@/composables/useCheckoutForm'
import api from '@/plugins/axios'

const store = useShopStore()
const router = useRouter()

watch(
    () => store.cart_items.length,
    (length) => { if (length === 0) router.replace('/') },
    { immediate: true }
)

onMounted(() => {
    api.get('pages/cart')
})

type StepKey = 'contact' | 'delivery' | 'payment'

const current_step = ref<StepKey | null>('contact')
const done = ref({ contact: false, delivery: false, payment: false })

const { contact, delivery, payment, clear_form } = useCheckoutForm()

function on_complete(step: StepKey, data: ContactData | DeliveryData | PaymentData) {
    if (step === 'contact') contact.value = data as ContactData
    if (step === 'delivery') delivery.value = data as DeliveryData
    if (step === 'payment') payment.value = data as PaymentData

    done.value[step] = true
    current_step.value = step === 'contact' ? 'delivery'
        : step === 'delivery' ? 'payment'
        : null
}

function open_step(step: StepKey) {
    current_step.value = step
}

const can_confirm = computed(() =>
    done.value.contact && done.value.delivery && done.value.payment && current_step.value === null
)

const delivery_cost_label = computed(() => {
    if (!done.value.delivery) return '—'
    return delivery.value.method === 'pickup' ? 'Free' : "Carrier's rates"
})

function confirm_order() {
    if (!can_confirm.value) return
    clear_form()
    console.log('Order confirmed', {
        contact: contact.value,
        delivery: delivery.value,
        payment: payment.value,
        items: store.cart_items,
        total: store.cart_total,
    })
}
</script>

<style lang="scss" scoped>
.checkout {
    &__inner {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 32px;
        align-items: start;
    }

    &__main {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    &__items-block {
        border: 1.5px solid $color-light;
        border-radius: 10px;
        padding: 20px 24px;
    }

    &__items-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    &__items-title {
        font-size: 16px;
        font-weight: 700;
        color: $color-dark;
    }

    &__items {
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

    &__sidebar {
        position: sticky;
        top: 90px;
    }

    &__summary {
        border: 1.5px solid $color-light;
        border-radius: 10px;
        padding: 24px;
    }

    &__summary-title {
        font-size: 16px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 16px;
    }

    &__summary-row {
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

    &__summary-note {
        font-size: 12px;
        color: $color-gray;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    &__summary-divider {
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
