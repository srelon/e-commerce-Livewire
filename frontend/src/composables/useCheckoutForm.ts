import { ref, watch } from 'vue'
import type { ContactData, DeliveryData, PaymentData } from '@/types/shop'

const STORAGE_KEY = 'checkout_form'

interface StoredForm {
    contact: ContactData
    delivery: DeliveryData
    payment: PaymentData
}

function load(): StoredForm | null {
    try {
        const raw = localStorage.getItem(STORAGE_KEY)
        return raw ? (JSON.parse(raw) as StoredForm) : null
    } catch {
        return null
    }
}

function save(data: StoredForm) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data))
    } catch {}
}

export function useCheckoutForm() {
    const stored = load()

    const contact = ref<ContactData>(stored?.contact ?? {
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
    })

    const delivery = ref<DeliveryData>(stored?.delivery ?? {
        delivery_id: null,
        branch_id: null,
    })

    const payment = ref<PaymentData>(stored?.payment ?? {
        method: '',
    })

    watch(
        [contact, delivery, payment],
        () => save({ contact: contact.value, delivery: delivery.value, payment: payment.value }),
        { deep: true }
    )

    function clear_form() {
        localStorage.removeItem(STORAGE_KEY)
    }

    return { contact, delivery, payment, clear_form }
}
