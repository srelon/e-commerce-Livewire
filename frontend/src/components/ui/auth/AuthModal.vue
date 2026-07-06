<template>
    <Teleport to="body">
        <div v-if="auth_store.modal_open" class="auth-modal-overlay" @click.self="auth_store.close_modal">
            <div class="auth-modal">
                <button class="auth-modal__close" aria-label="Close" @click="auth_store.close_modal">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>

                <BaseTabs v-if="is_tabbed" v-model="active_tab_model" :tabs="['login', 'register']">
                    <template #label="{ tab }">{{ tab === 'login' ? 'Sign In' : 'Sign Up' }}</template>
                    <component :is="tab_components[auth_store.active_tab]" />
                </BaseTabs>
                <template v-else>
                    <h2 class="auth-modal__title">{{ titles[auth_store.active_tab] }}</h2>
                    <component :is="tab_components[auth_store.active_tab]" />
                </template>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, watch, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import LoginForm from './LoginForm.vue'
import RegisterForm from './RegisterForm.vue'
import ForgotPasswordForm from './ForgotPasswordForm.vue'
import ResetPasswordForm from './ResetPasswordForm.vue'
import BaseTabs from '@/components/ui/base/BaseTabs.vue'

const auth_store = useAuthStore()

const tab_components = {
    login: LoginForm,
    register: RegisterForm,
    forgot: ForgotPasswordForm,
    reset: ResetPasswordForm,
}

const titles = {
    login: 'Sign In',
    register: 'Sign Up',
    forgot: 'Forgot Password',
    reset: 'Reset Password',
}

const is_tabbed = computed(() => auth_store.active_tab === 'login' || auth_store.active_tab === 'register')

const active_tab_model = computed({
    get: () => auth_store.active_tab,
    set: (tab: string) => auth_store.open_modal(tab as 'login' | 'register'),
})

function on_keydown(e: KeyboardEvent) {
    if (e.key === 'Escape') auth_store.close_modal()
}

watch(() => auth_store.modal_open, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})

onMounted(() => window.addEventListener('keydown', on_keydown))
onUnmounted(() => window.removeEventListener('keydown', on_keydown))
</script>

<style lang="scss" scoped>
.auth-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 900;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.auth-modal {
    position: relative;
    background: $color-white;
    border-radius: 12px;
    width: 100%;
    max-width: 420px;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    padding: 32px;

    &__close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: $color-lightest;
        border: 1.5px solid $color-light;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;

        svg {
            width: 16px;
            height: 16px;

            path {
                fill: none;
                stroke: $color-dark;
                stroke-width: 2;
                stroke-linecap: round;
                stroke-linejoin: round;
            }
        }

        &:hover {
            background: $color-light;
        }
    }

    &__title {
        font-size: 20px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 24px;
    }
}
</style>
