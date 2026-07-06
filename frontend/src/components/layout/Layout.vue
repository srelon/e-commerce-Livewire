<template>
    <div class="layout">
        <AppHeader />
        <main class="layout__main">
            <router-view />
        </main>
        <AppFooter />
        <CartPopup />
        <AuthModal />
    </div>
</template>

<script setup lang="ts">
import AppHeader from './AppHeader.vue'
import AppFooter from './AppFooter.vue'
import CartPopup from '@/components/ui/cart/CartPopup.vue'
import AuthModal from '@/components/ui/auth/AuthModal.vue'
</script>

<style lang="scss" scoped>
.layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;

    &__main {
        flex: 1;
    }
}
</style>
