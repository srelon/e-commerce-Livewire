<template>
    <Teleport to="body">
        <Transition name="cart-popup">
            <div v-if="store.popup_open" class="cart-popup-overlay" @click.self="store.close_popup">
                <div class="cart-popup">
                    <div class="cart-popup__head">
                        <div class="cart-popup__head-title">
                            <svg class="cart-popup__head-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                            Your Cart
                            <span class="cart-popup__count">{{ store.cart_count }}</span>
                        </div>
                        <button class="cart-popup__close" aria-label="Close" @click="store.close_popup">
                            <svg viewBox="0 0 14 14" aria-hidden="true">
                                <path d="M13 1L1 13M1 1l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <div class="cart-popup__divider" />

                    <div v-if="store.cart_items.length === 0" class="cart-popup__empty">
                        Your cart is empty
                    </div>

                    <div v-else class="cart-popup__items">
                        <div v-for="item in store.cart_items" :key="item.id" class="cart-popup__item">
                            <img :src="item.image" :alt="item.title" class="cart-popup__img">
                            <div class="cart-popup__info">
                                <p class="cart-popup__title">{{ item.title }}</p>
                                <p class="cart-popup__author">{{ item.author }}</p>
                                <p class="cart-popup__price">${{ (item.price * item.quantity).toFixed(2) }}</p>
                                <div class="cart-popup__qty">
                                    <button class="cart-popup__qty-btn" aria-label="Decrease" @click="store.update_qty(item.id, -1)">−</button>
                                    <span class="cart-popup__qty-val">{{ item.quantity }}</span>
                                    <button class="cart-popup__qty-btn" aria-label="Increase" @click="store.update_qty(item.id, 1)">+</button>
                                    <button class="cart-popup__delete" aria-label="Remove" @click="store.remove_from_cart(item.id)">
                                        <svg viewBox="0 0 16 16" aria-hidden="true">
                                            <path d="M2 4h12M5 4V2h6v2M6 7v5M10 7v5M3 4l1 9a1 1 0 001 1h6a1 1 0 001-1l1-9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template v-if="store.cart_items.length > 0">
                        <div class="cart-popup__divider" />

                        <div class="cart-popup__totals">
                            <div class="cart-popup__row">
                                <span>Shipping Cost</span>
                                <span>$0.00</span>
                            </div>
                            <div class="cart-popup__row cart-popup__row--total">
                                <span>Cart Total</span>
                                <span>${{ store.cart_total.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="cart-popup__divider" />

                        <div class="cart-popup__actions">
                            <router-link :to="{ name: 'cart' }" class="cart-popup__btn cart-popup__btn--primary" @click="store.close_popup">
                                Checkout
                            </router-link>
                            <button class="cart-popup__btn cart-popup__btn--outline" @click="store.close_popup">
                                Continue Shopping
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { useShopStore } from '@/stores/shop'

const store = useShopStore()
</script>

<style lang="scss" scoped>
@use "sass:color";

.cart-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.cart-popup {
    background: $color-white;
    border-radius: 12px;
    padding: 24px 28px;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;

    &__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    &__head-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 700;
        color: $color-dark;
    }

    &__head-icon {
        width: 20px;
        height: 20px;
        color: $color-primary;
        flex-shrink: 0;
    }

    &__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        background: $color-primary;
        color: $color-white;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    &__close {
        background: none;
        border: none;
        cursor: pointer;
        color: $color-gray;
        padding: 4px;
        display: flex;
        align-items: center;
        transition: color 0.2s;
        flex-shrink: 0;

        &:hover {
            color: $color-dark;
        }

        svg {
            width: 14px;
            height: 14px;
        }
    }

    &__divider {
        height: 1px;
        background: $color-light;
        margin: 16px 0;
        flex-shrink: 0;
    }

    &__empty {
        text-align: center;
        padding: 32px 0;
        color: $color-gray;
        font-size: 15px;
    }

    &__items {
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-height: 0;
        padding-right: 4px;

        &::-webkit-scrollbar {
            width: 4px;
        }

        &::-webkit-scrollbar-track {
            background: $color-lighter;
            border-radius: 2px;
        }

        &::-webkit-scrollbar-thumb {
            background: $color-light;
            border-radius: 2px;
        }
    }

    &__item {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    &__img {
        width: 70px;
        height: 96px;
        object-fit: cover;
        border-radius: 6px;
        flex-shrink: 0;
    }

    &__info {
        flex: 1;
        min-width: 0;
    }

    &__title {
        font-size: 14px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 2px;
        line-height: 1.3;
    }

    &__author {
        font-size: 12px;
        color: $color-gray;
        margin-bottom: 6px;
    }

    &__price {
        font-size: 14px;
        font-weight: 700;
        color: $color-primary;
        margin-bottom: 10px;
    }

    &__qty {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    &__qty-btn {
        width: 28px;
        height: 28px;
        border: 1.5px solid $color-light;
        background: none;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: $color-dark;
        transition: background 0.2s, border-color 0.2s, color 0.2s;

        &:hover {
            background: $color-primary;
            border-color: $color-primary;
            color: $color-white;
        }
    }

    &__qty-val {
        min-width: 22px;
        text-align: center;
        font-size: 14px;
        font-weight: 600;
        color: $color-dark;
    }

    &__delete {
        margin-left: 4px;
        background: none;
        border: 1.5px solid $color-light;
        border-radius: 4px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: $color-gray;
        transition: background 0.2s, border-color 0.2s, color 0.2s;

        svg {
            width: 13px;
            height: 13px;
        }

        &:hover {
            background: #ff3b30;
            border-color: #ff3b30;
            color: $color-white;
        }
    }

    &__totals {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-shrink: 0;
    }

    &__row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: $color-gray;

        span:last-child {
            font-weight: 600;
            color: $color-dark;
        }

        &--total {
            font-size: 15px;
            font-weight: 700;
            color: $color-dark;

            span:last-child {
                color: $color-primary;
                font-size: 16px;
            }
        }
    }

    &__actions {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }

    &__btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 13px 20px;
        border-radius: 999px;
        font-size: 15px;
        font-weight: 600;
        font-family: $font-body;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, border-color 0.2s;
        text-decoration: none;
        border: 2px solid transparent;

        &--primary {
            background: $color-primary;
            color: $color-white;
            border-color: $color-primary;

            &:hover {
                background: color.adjust($color-primary, $lightness: -8%);
                border-color: color.adjust($color-primary, $lightness: -8%);
            }
        }

        &--outline {
            background: transparent;
            color: $color-primary;
            border-color: $color-primary;

            &:hover {
                background: $color-primary;
                color: $color-white;
            }
        }
    }
}

.cart-popup-enter-active,
.cart-popup-leave-active {
    transition: opacity 0.2s ease;

    .cart-popup {
        transition: transform 0.2s ease, opacity 0.2s ease;
    }
}

.cart-popup-enter-from,
.cart-popup-leave-to {
    opacity: 0;

    .cart-popup {
        transform: scale(0.95);
        opacity: 0;
    }
}
</style>
