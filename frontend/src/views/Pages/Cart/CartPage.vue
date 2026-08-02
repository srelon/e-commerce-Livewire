<template>
    <PageBanner title="Checkout" />

    <section class="checkout section">
        <div class="container">
            <div class="checkout__inner">
                <div class="checkout__main">
                    <CheckoutItemsList />

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
                            :options="delivery_options"
                            @change="delivery = $event"
                            @complete="on_complete('delivery', $event)"
                        />
                        <template #summary>
                            <p>{{ delivery_summary_label }}</p>
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
                            :options="payment_options"
                            @change="payment = $event"
                            @complete="on_complete('payment', $event)"
                        />
                        <template #summary>
                            <p>{{ payment_summary_label }}</p>
                        </template>
                    </CheckoutStep>
                </div>

                <CheckoutSummary
                    :delivery_cost_label="delivery_cost_label"
                    :can_confirm="can_confirm"
                    :is_placing_order="is_placing_order"
                    @confirm="confirm_order"
                />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import CheckoutStep from '@/components/ui/cart/CheckoutStep.vue'
import CheckoutItemsList from '@/components/ui/cart/CheckoutItemsList.vue'
import CheckoutSummary from '@/components/ui/cart/CheckoutSummary.vue'
import ContactStep from './ContactStep.vue'
import DeliveryStep from './DeliveryStep.vue'
import PaymentStep from './PaymentStep.vue'
import { useShopStore } from '@/stores/shop'
import { useAuthStore } from '@/stores/auth'
import { useCheckoutForm } from '@/composables/useCheckoutForm'
import type { ContactData, DeliveryData, PaymentData, DeliveryOption, PaymentOption, SavedContact } from '@/types/shop'
import api from '@/plugins/axios'

const store = useShopStore()
const auth_store = useAuthStore()
const router = useRouter()

watch(
    () => store.cart_items.length,
    (length) => { if (length === 0) router.replace('/') },
    { immediate: true }
)

const delivery_options = ref<DeliveryOption[]>([])
const payment_options = ref<PaymentOption[]>([])

const { contact, delivery, payment, clear_form } = useCheckoutForm()

function fill_from_saved_contact(saved: SavedContact | null) {
    if (!saved) return

    contact.value = {
        first_name: saved.first_name,
        last_name: saved.last_name,
        phone: saved.phone,
        email: saved.email,
    }

    if (saved.delivery_id) {
        delivery.value = {
            delivery_id: saved.delivery_id,
            branch_id: saved.branch_id,
        }
    }
}

function fetch_cart_bundle() {
    return api.get('pages/cart').then((res) => {
        fill_from_saved_contact(res.data.data?.contact ?? null)
        delivery_options.value = res.data.data?.delivery_options ?? []
        payment_options.value = res.data.data?.payment_options ?? []
    })
}

onMounted(() => {
    fetch_cart_bundle()

    auth_store.ensure_ready().then(() => {
        watch(() => auth_store.user, (new_user, old_user) => {
            if (new_user && !old_user) fetch_cart_bundle()
        })
    })
})

type StepKey = 'contact' | 'delivery' | 'payment'

const current_step = ref<StepKey | null>('contact')
const done = ref({ contact: false, delivery: false, payment: false })

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

const selected_delivery_option = computed(() => delivery_options.value.find((o) => o.id === delivery.value.delivery_id))

const delivery_cost_label = computed(() => {
    if (!done.value.delivery) return '—'
    const option = selected_delivery_option.value
    if (!option) return '—'
    if (option.price) return `$${Number(option.price).toFixed(2)}`
    return option.requires_branch ? "Carrier's rates" : 'Free'
})

const delivery_summary_label = computed(() => {
    const option = selected_delivery_option.value
    if (!option) return ''
    if (!option.requires_branch) return option.name

    const branch = option.branches.find((b) => b.id === delivery.value.branch_id)
    return branch ? `${option.name} — ${branch.city}, ${branch.branch}` : option.name
})

const payment_summary_label = computed(() => payment_options.value.find((o) => o.key === payment.value.method)?.name ?? '')

const is_placing_order = ref(false)

async function confirm_order() {
    if (!can_confirm.value) return

    is_placing_order.value = true
    try {
        const res = await api.post('cart/orders', {
            contact: contact.value,
            delivery: delivery.value,
            payment: payment.value,
            items: store.cart_items.map((item) => ({
                slug: item.id,
                quantity: item.quantity,
            })),
        })

        store.set_last_order({ public_id: res.data.data.public_id })
        await router.push({ name: 'order-success' })
        clear_form()
        store.clear_cart()
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            const conflicts = error.response.data?.items as { slug: string; available: number }[] | undefined
            conflicts?.forEach((conflict) => store.set_qty(conflict.slug, conflict.available))
        }
    } finally {
        is_placing_order.value = false
    }
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
}
</style>
