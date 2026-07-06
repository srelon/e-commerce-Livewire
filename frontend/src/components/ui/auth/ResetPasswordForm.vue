<template>
    <Form :validation-schema="schema" @submit="on_submit" class="auth-form">
        <BaseInput name="password" label="New Password" type="password" placeholder="••••••••" />
        <BaseInput name="password_confirmation" label="Confirm New Password" type="password" placeholder="••••••••" />

        <BaseButton type="submit" :disabled="is_loading" class="auth-form__submit">
            {{ is_loading ? 'Resetting...' : 'Reset Password' }}
        </BaseButton>
    </Form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Form } from 'vee-validate'
import { object, string, ref as yup_ref } from 'yup'
import { useToast } from 'vue-toastification'
import BaseInput from '@/components/ui/base/BaseInput.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import api from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'

const auth_store = useAuthStore()
const is_loading = ref(false)

const schema = object({
    password: string().min(8, 'Password must be at least 8 characters'),
    password_confirmation: string().oneOf([yup_ref('password')], 'Passwords must match').min(1, 'Please confirm your password'),
})

async function on_submit(values: Record<string, string>) {
    if (!auth_store.reset_credentials) return

    is_loading.value = true
    try {
        await api.post('auth/reset-password', {
            ...values,
            token: auth_store.reset_credentials.token,
            email: auth_store.reset_credentials.email,
        })
        useToast().success('Password reset — please sign in.')
        auth_store.open_modal('login')
    } catch {
        // error toast is already shown by the axios response interceptor
    } finally {
        is_loading.value = false
    }
}
</script>

<style lang="scss" scoped>
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 18px;

    &__submit {
        width: 100%;
    }
}
</style>
