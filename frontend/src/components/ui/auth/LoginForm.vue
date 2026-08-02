<template>
    <Form :validation-schema="schema" @submit="on_submit" class="auth-form">
        <BaseInput name="email" label="Email" type="email" placeholder="john@example.com" />
        <BaseInput name="password" label="Password" type="password" placeholder="••••••••" />

        <BaseButton type="button" variant="text" class="auth-form__forgot" @click="auth_store.open_modal('forgot')">
            Forgot password?
        </BaseButton>

        <BaseButton type="submit" :disabled="is_loading" class="auth-form__submit">
            {{ is_loading ? 'Signing in...' : 'Sign In' }}
        </BaseButton>

        <p class="auth-form__footer">
            Don't have an account?
            <BaseButton type="button" variant="text" @click="auth_store.open_modal('register')">Sign Up</BaseButton>
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
    password: string().min(1, 'Password is required'),
})

async function on_submit(values: Record<string, string>) {
    is_loading.value = true
    try {
        const res = await api.post('auth/login', values)
        auth_store.set_user(res.data.data.user, res.data.data.cart)
        auth_store.close_modal()
        useToast().success('Welcome back!')
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

    &__forgot {
        align-self: flex-end;
        margin-top: -10px;
    }

    &__footer {
        font-size: 14px;
        color: $color-gray;
        text-align: center;
    }
}
</style>
