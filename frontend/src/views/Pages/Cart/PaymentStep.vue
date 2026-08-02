<template>
    <div class="payment-step">
        <BaseRadioGroup
            v-model="local.method"
            name="payment_method"
            :options="method_options"
        />

        <BaseButton type="button" :disabled="!is_valid" @click="on_continue">Continue</BaseButton>
    </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import BaseRadioGroup from '@/components/ui/base/BaseRadioGroup.vue'
import { useWizardStep } from '@/composables/useWizardStep'
import type { PaymentData, PaymentOption } from '@/types/shop'

const props = defineProps<{
    initial_data: PaymentData
    options: PaymentOption[]
}>()

const emit = defineEmits<{
    change: [data: PaymentData]
    complete: [data: PaymentData]
}>()

const { local, is_valid, on_continue } = useWizardStep<PaymentData>(
    props.initial_data,
    emit,
    (data) => !!data.method
)

const method_options = computed(() => props.options.map((option) => ({
    value: option.key,
    label: option.name,
})))

watch(
    () => props.options,
    (options) => {
        if (!local.method && options.length) local.method = options[0].key
    },
    { immediate: true }
)
</script>

<style lang="scss" scoped>
.payment-step {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
</style>
