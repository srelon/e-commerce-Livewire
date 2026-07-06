import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/plugins/axios'
import type { AuthUser, ResetCredentials } from '@/types/auth'

export type AuthTab = 'login' | 'register' | 'forgot' | 'reset'

export const useAuthStore = defineStore('auth', () => {
    const user = ref<AuthUser | null>(null)
    const modal_open = ref(false)
    const active_tab = ref<AuthTab>('login')
    const reset_credentials = ref<ResetCredentials | null>(null)

    const is_authenticated = computed(() => !!user.value)

    function open_modal(tab: AuthTab = 'login') {
        active_tab.value = tab
        modal_open.value = true
    }

    function close_modal() {
        modal_open.value = false
    }

    function set_user(new_user: AuthUser) {
        user.value = new_user
    }

    function fetch_user() {
        return api.get('auth/profile').then((res) => {
            user.value = res.data.data.user
        })
    }

    let ready_promise: Promise<void> | null = null

    function ensure_ready() {
        ready_promise ??= fetch_user()

        return ready_promise
    }

    function logout() {
        return api.post('auth/logout').then(() => {
            user.value = null
            useToast().success('You have been logged out.')
        })
    }

    function set_reset_pending(credentials: ResetCredentials) {
        reset_credentials.value = credentials
        open_modal('reset')
    }

    return {
        user,
        is_authenticated,
        modal_open,
        active_tab,
        reset_credentials,
        open_modal,
        close_modal,
        set_user,
        fetch_user,
        ensure_ready,
        logout,
        set_reset_pending,
    }
})
