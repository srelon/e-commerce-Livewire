<template>
    <form class="contact-step" @submit.prevent="on_submit">
        <div class="contact-step__row">
            <BaseInput name="first_name" label="First Name" placeholder="John" autocomplete="given-name" />
            <BaseInput name="last_name" label="Last Name" placeholder="Smith" autocomplete="family-name" />
        </div>
        <div class="contact-step__row">
            <BaseInput name="phone" label="Phone" type="tel" placeholder="098 910 50 67" autocomplete="tel" />
            <BaseInput name="email" label="Email" type="email" placeholder="john@example.com" autocomplete="email" />
        </div>
        <BaseButton type="submit" :disabled="!meta.valid">Continue</BaseButton>
    </form>
</template>

<script setup lang="ts">
import { watch } from 'vue'
import { useForm } from 'vee-validate'
import { object, string } from 'yup'
import BaseInput from '@/components/ui/base/BaseInput.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import type { ContactData } from '@/types/shop'

const props = defineProps<{ initial_data: ContactData }>()

const emit = defineEmits<{
    change: [data: ContactData]
    complete: [data: ContactData]
}>()

const { handleSubmit, values, meta, resetForm } = useForm<ContactData>({
    validationSchema: object({
        first_name: string().min(1, 'First name is required'),
        last_name: string().min(1, 'Last name is required'),
        phone: string().min(6, 'Phone number is required'),
        email: string().min(1, 'Email is required').email('Enter a valid email'),
    }),
    initialValues: { ...props.initial_data },
    validateOnMount: true,
})

watch(values, (vals) => emit('change', { ...vals }), { deep: true })

watch(() => props.initial_data, (data) => {
    if (!meta.value.dirty) resetForm({ values: { ...data } })
})

const on_submit = handleSubmit((vals) => emit('complete', { ...vals }))
</script>

<style lang="scss" scoped>
.contact-step {
    display: flex;
    flex-direction: column;
    gap: 16px;

    &__row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
}
</style>
