<template>
    <Form :validation-schema="schema" @submit="on_submit" class="newsletter-form">
        <Field name="email" v-slot="{ field, errorMessage }">
            <input
                v-bind="field"
                type="email"
                placeholder="Your email *"
                aria-label="Email address"
                class="newsletter-form__input"
                :class="{ 'newsletter-form__input--error': errorMessage }"
            >
        </Field>
        <button type="submit" class="newsletter-form__submit" :disabled="is_loading">
            {{ is_loading ? 'Enrolling...' : 'Enroll' }}
        </button>
    </Form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Form, Field } from 'vee-validate'
import { object, string } from 'yup'
import { useToast } from 'vue-toastification'
import api from '@/plugins/axios'

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
@use "sass:color";

.newsletter-form {
    display: flex;
    flex-direction: column;
    gap: 8px;

    &__input {
        border: none;
        outline: none;
        border-radius: 30px;
        padding: 10px 16px;
        font-size: 14px;
        font-family: $font-body;
        width: 100%;

        &::placeholder {
            color: $color-gray;
        }

        &--error {
            box-shadow: 0 0 0 1.5px $color-danger;
        }
    }

    &__submit {
        border-radius: 30px;
        padding: 10px 16px;
        background: $color-dark;
        color: $color-white;
        font-size: 14px;
        font-weight: 600;
        font-family: $font-body;
        cursor: pointer;
        transition: background 0.2s;

        &:hover:not(:disabled) {
            background: color.adjust($color-dark, $lightness: 15%);
        }

        &:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    }
}
</style>
