<template>
    <Form :validation-schema="schema" @submit="on_submit" class="auth-form">
        <BaseInput name="name" label="Name" placeholder="John Doe" />
        <BaseInput name="email" label="Email" type="email" placeholder="john@example.com" />
        <BaseInput name="password" label="Password" type="password" placeholder="••••••••" />
        <BaseInput name="password_confirmation" label="Confirm Password" type="password" placeholder="••••••••" />

        <BaseButton type="submit" :disabled="is_loading" class="auth-form__submit">
            {{ is_loading ? 'Creating account...' : 'Sign Up' }}
        </BaseButton>

        <p class="auth-form__footer">
            Already have an account?
            <BaseButton type="button" variant="text" @click="auth_store.open_modal('login')">Sign In</BaseButton>
        </p>
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
    name: string().min(1, 'Name is required'),
    email: string().min(1, 'Email is required').email('Enter a valid email'),
    password: string().min(8, 'Password must be at least 8 characters'),
    password_confirmation: string().oneOf([yup_ref('password')], 'Passwords must match').min(1, 'Please confirm your password'),
})

async function on_submit(values: Record<string, string>) {
    is_loading.value = true
    try {
        const res = await api.post('auth/register', values)
        auth_store.set_user(res.data.data.user, res.data.data.cart)
        auth_store.close_modal()
        useToast().success('Account created!')
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
