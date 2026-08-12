<template>
    <Form :validation-schema="schema" @submit="on_submit" class="auth-form">
        <p class="auth-form__hint">Enter your email and we'll send you a link to reset your password.</p>

        <BaseInput name="email" label="Email" type="email" placeholder="john@example.com" />

        <BaseButton type="submit" :disabled="is_loading" class="auth-form__submit">
            {{ is_loading ? 'Sending...' : 'Send Reset Link' }}
        </BaseButton>

        <p class="auth-form__footer">
            <BaseButton type="button" variant="text" @click="auth_store.open_modal('login')">Back to Sign In</BaseButton>
        </p>
    </Form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Form } from 'vee-validate'
import { object, string } from 'yup'
import { useToast } from 'vue-toastification'
import BaseInput from '@/components/ui/base/BaseInput.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import api from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'

const auth_store = useAuthStore()
const is_loading = ref(false)

const schema = object({
    email: string().min(1, 'Email is required').email('Enter a valid email'),
})

async function on_submit(values: Record<string, string>) {
    is_loading.value = true
    try {
        await api.post('auth/forgot-password', values)
        useToast().success('Check your email for a password reset link.')
        auth_store.close_modal()
    } catch {
        // error toast is already shown by the axios response interceptor
        is_loading.value = false
    }
}
</script>

<style lang="scss" scoped>
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 18px;

    &__hint {
        font-size: 14px;
        color: $color-gray;
        margin-top: -8px;
    }

    &__submit {
        width: 100%;
    }

    &__footer {
        font-size: 14px;
        color: $color-gray;
        text-align: center;
    }
}
</style>
