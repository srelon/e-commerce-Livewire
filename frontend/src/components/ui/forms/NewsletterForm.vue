<template>
    <Form :validation-schema="schema" @submit="on_submit" class="newsletter-form">
        <BaseInput name="email" type="email" placeholder="Your email *" pill />
        <BaseButton type="submit" variant="dark" :loading="is_loading">Enroll</BaseButton>
    </Form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Form } from 'vee-validate'
import { object, string } from 'yup'
import { useToast } from 'vue-toastification'
import api from '@/plugins/axios'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import BaseInput from '@/components/ui/base/BaseInput.vue'

const is_loading = ref(false)
const toast = useToast()

const schema = object({
    email: string().min(1, 'Email is required').email('Enter a valid email'),
})

async function on_submit(values: Record<string, string>, actions: { resetForm: () => void }) {
    is_loading.value = true
    try {
        await api.post('newsletter', { email: values.email })
        toast.success('Thanks for subscribing!')
        actions.resetForm()
    } catch {
        // error toast is already shown by the axios response interceptor
    } finally {
        is_loading.value = false
    }
}
</script>

<style lang="scss" scoped>
.newsletter-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
</style>
