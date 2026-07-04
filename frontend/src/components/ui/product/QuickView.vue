<template>
    <button class="quick-view__trigger" aria-label="Quick view" @click.prevent="open = true">
        <svg viewBox="0 0 15 15" aria-hidden="true">
            <path d="M7.5,5.5c-1.1,0-1.9,0.9-1.9,2s0.9,2,1.9,2s1.9-0.9,1.9-2S8.6,5.5,7.5,5.5z M14.7,6.9c-0.9-1.6-2.9-5.2-7.1-5.2S1.3,5.3,0.4,6.9L0,7.5l0.4,0.6c0.9,1.6,2.9,5.2,7.1,5.2s6.3-3.7,7.1-5.2L15,7.5L14.7,6.9zM7.5,11.8c-3.2,0-4.9-2.8-5.7-4.3C2.6,6,4.3,3.2,7.5,3.2s4.9,2.8,5.7,4.3C12.4,9,10.8,11.8,7.5,11.8z"/>
        </svg>
    </button>

    <Teleport to="body">
        <div v-if="open" class="qv-modal" @click.self="close">
            <div class="qv-modal__inner">
                <button class="qv-modal__close" @click="close" aria-label="Close">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
                <ProductPage :quick="true" :slug="slug" />
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import ProductPage from '@/views/Pages/Products/ProductPage.vue'

interface Props {
    slug?: string
}

withDefaults(defineProps<Props>(), {
    slug: '',
})

const open = ref(false)

function close() {
    open.value = false
}

function on_keydown(e: KeyboardEvent) {
    if (e.key === 'Escape') close()
}

watch(open, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})

onMounted(() => window.addEventListener('keydown', on_keydown))
onUnmounted(() => window.removeEventListener('keydown', on_keydown))
</script>

<style lang="scss" scoped>
.quick-view {
    &__trigger {
        padding: 0 9px;
        border-radius: 0;
        background: transparent;
        border: 1.5px solid $color-light;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;

        svg {
            width: 14px;
            height: 14px;
            fill: $color-dark;
            transition: fill 0.2s;
        }

        &:hover {
            background: $color-primary;
            border-color: $color-primary;

            svg {
                fill: $color-white;
            }
        }
    }
}

.qv-modal {
    position: fixed;
    inset: 0;
    z-index: 900;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;

    &__inner {
        position: relative;
        background: $color-white;
        border-radius: 12px;
        width: 100%;
        max-width: 1000px;
        max-height: calc(100vh - 48px);
        overflow-y: auto;
    }

    &__close {
        position: sticky;
        top: 16px;
        float: right;
        margin: 16px 16px 0 0;
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
        z-index: 1;

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
}
</style>
