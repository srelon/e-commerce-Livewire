<template>
    <header class="header">
        <div class="header__main">
            <div class="container header__main-inner">
                <router-link :to="{ name: 'home' }" class="header__logo" aria-label="Book Store">
                    <svg class="header__logo-icon" viewBox="0 0 34 28" fill="none" aria-hidden="true">
                        <path d="M17 5L17 23" stroke="#ff6310" stroke-width="2" stroke-linecap="round"/>
                        <path d="M17 5L4 7.5L4 25L17 23Z" fill="#ff6310" fill-opacity="0.12" stroke="#ff6310" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M17 5L30 7.5L30 25L17 23Z" fill="#ff6310" fill-opacity="0.12" stroke="#ff6310" stroke-width="1.5" stroke-linejoin="round"/>
                        <line x1="7" y1="12" x2="14" y2="11.4" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                        <line x1="7" y1="16" x2="14" y2="15.4" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                        <line x1="20" y1="11.4" x2="27" y2="12" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                        <line x1="20" y1="15.4" x2="27" y2="16" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                    </svg>
                    <span class="header__logo-text"><strong>Book</strong>Store</span>
                </router-link>

                <div class="header__search">
                    <input type="search" placeholder="Search books" aria-label="Search books">
                    <select aria-label="Search in category">
                        <option value="">Select Category</option>
                        <option v-for="cat in layout_store.categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
                    </select>
                    <button type="submit" aria-label="Search">
                        <svg width="15" height="15" viewBox="0 0 15 15" aria-hidden="true">
                            <path d="M14.8,13.7L12,11c0.9-1.2,1.5-2.6,1.5-4.2c0-3.7-3-6.8-6.8-6.8S0,3,0,6.8s3,6.8,6.8,6.8c1.6,0,3.1-0.6,4.2-1.5l2.8,2.8c0.1,0.1,0.3,0.2,0.5,0.2s0.4-0.1,0.5-0.2C15.1,14.5,15.1,14,14.8,13.7z M1.5,6.8c0-2.9,2.4-5.2,5.2-5.2S12,3.9,12,6.8S9.6,12,6.8,12S1.5,9.6,1.5,6.8z"/>
                        </svg>
                    </button>
                </div>

                <div class="header__actions">
                    <a
                        v-for="action in actions"
                        :key="action.label"
                        href="#"
                        class="header__action"
                        :class="{ 'header__action--cart': action.label === 'Cart' }"
                        :aria-label="action.label"
                        @click.prevent="action.label === 'Cart' ? store.open_popup() : undefined"
                    >
                        <svg width="15" height="15" viewBox="0 0 15 15" aria-hidden="true">
                            <path :d="action.icon" />
                        </svg>
                        <span v-if="action.label === 'Cart' && store.cart_count > 0" class="header__cart-badge">
                            {{ store.cart_count }}
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <div class="header__nav-bar">
            <div class="container header__nav-inner">

                <div class="header__cats-wrap" @mouseenter="cats_open = true" @mouseleave="cats_open = false; hovered_category = null">
                    <button class="header__cats-btn" :class="{ 'header__cats-btn--open': cats_open }" aria-haspopup="true" :aria-expanded="cats_open">
                        <svg viewBox="0 0 15 15" class="header__cats-icon" aria-hidden="true">
                            <path d="M.012 3.345c0 .627.006 1.249 0 1.879.135 2.153 1.916 1.945 3.54 1.934.56 0 1.134-.003 1.708-.012 1.058-.012 1.87-.815 1.89-1.867.02-1.099.02-2.224 0-3.434C7.133.83 6.31.007 5.316.005H4.16c-.726 0-1.491 0-2.274.002A1.87 1.87 0 0 0 .557.55C-.205 1.261.05 2.407.012 3.345M1.536 1.98c0-.314.131-.452.445-.452.495-.003 1.005-.003 1.553-.003 2.277.088 2.078-.483 2.075 1.574 0 .706.003 1.41 0 2.122-.003.249-.129.378-.375.378q-1.644.005-3.288 0c-.279 0-.41-.132-.41-.413-.003-1.1-.003-2.18 0-3.206M9.75 7.146c.545.009 1.107.012 1.67.012.99-.062 2.26.24 3.021-.563.75-.71.516-1.873.549-2.81 0-.648.005-1.29 0-1.943C15 .872 14.128-.01 13.158.004a221 221 0 0 0-3.414 0H9.72a1.877 1.877 0 0 0-1.87 1.882 128 128 0 0 0 0 3.355v.024a1.89 1.89 0 0 0 1.9 1.881m-.355-5.207c0-.27.135-.41.399-.41q1.636-.01 3.285 0c.234 0 .39.152.39.386.005 1.234.005 2.315 0 3.306-.006.565-.713.337-1.064.384a388 388 0 0 0-2.611 0c-.276 0-.399-.124-.399-.399zM5.17 7.846q-.732-.002-1.465-.002c-.85.032-1.556-.074-2.312.079A1.85 1.85 0 0 0 .015 9.704a247 247 0 0 0-.006 2.943c-.041.644.064 1.35.548 1.799.498.542 1.277.58 1.975.548.89.006 1.84-.018 2.72.003 1.06.017 1.934-.914 1.907-1.975-.006-.633-.003-1.278-.003-1.9.068-1.614.041-3.17-1.987-3.276m.434 4.074v1.122c-.003.296-.132.428-.422.428q-1.623.005-3.244 0c-.252-.003-.401-.15-.404-.393-.01-1.11-.01-2.221 0-3.305.003-.31.225-.378.41-.378h1.661q.813 0 1.621.003.376.002.378.38c.003.713-.003 1.434 0 2.143M8.9 8.904c-2.257 2.207-.604 6.11 2.526 6.093 3.147.02 4.762-3.889 2.526-6.099-1.333-1.403-3.719-1.386-5.052.006m2.511 4.563c-2.652-.088-2.681-3.997.015-4.073 2.72.082 2.687 3.988-.015 4.073"/>
                        </svg>
                        Categories
                        <svg class="header__cats-chevron" viewBox="0 0 10 6" aria-hidden="true">
                            <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="header__mega" :class="{ 'header__mega--open': cats_open }">
                        <div class="header__mega-inner">
                            <div class="header__mega-cats">
                                <router-link
                                    v-for="cat in layout_store.categories"
                                    :key="cat.id"
                                    :to="{ name: 'products', query: { category: cat.name } }"
                                    class="header__mega-item"
                                    :class="{ 'header__mega-item--active': cat.id === active_promo_category?.id }"
                                    @mouseenter="hovered_category = cat"
                                >
                                    <div class="header__mega-icon">
                                        <img :src="to_storage_url(cat.icon)" :alt="cat.name" width="30" height="30">
                                    </div>
                                    <div class="header__mega-info">
                                        <span class="header__mega-name">{{ cat.name }}</span>
                                        <span class="header__mega-count">{{ cat.count }} books</span>
                                    </div>
                                </router-link>
                            </div>

                            <div class="header__mega-promo">
                                <img :src="to_storage_url(active_promo_category?.image ?? null)" alt="" class="header__mega-promo-img">
                                <div class="header__mega-promo-overlay">
                                    <div class="header__mega-promo-arrow">
                                        <svg viewBox="0 0 15 15" aria-hidden="true">
                                            <path d="M1 15a1 1 0 0 1-.707-1.707L11.586 2H1.52a1 1 0 0 1 0-2h12.483q.202.002.379.075a1 1 0 0 1 .542.543 1 1 0 0 1 .076.38V13.48a1 1 0 1 1-2 0V3.414L1.707 14.707A1 1 0 0 1 1 15"/>
                                        </svg>
                                    </div>
                                    <h5 class="header__mega-promo-title">Explore {{ active_promo_category?.name }} Books</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <nav class="header__nav">
                    <div
                        v-for="item in layout_store.menu"
                        :key="item.id"
                        class="header__nav-item"
                        @mouseenter="item.children.length && (open_menu_id = item.id)"
                        @mouseleave="open_menu_id = null"
                    >
                        <router-link
                            v-if="item.type === 'route'"
                            :to="menu_route_target(item)"
                            class="header__nav-link"
                            exact-active-class="header__nav-link--active"
                        >
                            {{ item.name }}
                            <svg v-if="item.children.length" class="header__nav-chevron" viewBox="0 0 10 6" aria-hidden="true">
                                <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </router-link>
                        <a v-else :href="item.route ?? '#'" class="header__nav-link" target="_blank" rel="noopener">
                            {{ item.name }}
                        </a>

                        <div
                            v-if="item.children.length"
                            class="header__nav-dropdown"
                            :class="{ 'header__nav-dropdown--open': open_menu_id === item.id }"
                        >
                            <template v-for="child in item.children" :key="child.id">
                                <router-link
                                    v-if="child.type === 'route'"
                                    :to="menu_route_target(child)"
                                    class="header__nav-dropdown-item"
                                >
                                    {{ child.name }}
                                </router-link>
                                <a v-else :href="child.route ?? '#'" class="header__nav-dropdown-item" target="_blank" rel="noopener">
                                    {{ child.name }}
                                </a>
                            </template>
                        </div>
                    </div>
                </nav>

                <div class="header__contact" v-if="phone_contact">
                    <svg viewBox="0 0 15 15" class="header__contact-icon" aria-hidden="true">
                        <path :d="phone_contact.icon ?? undefined"/>
                    </svg>
                    <div>
                        <span class="header__contact-label">24/7 Support Center</span>
                        <a :href="`tel:${phone_contact.content.replace(/[^0-9+]/g, '')}`" class="header__contact-phone">{{ phone_contact.content }}</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useShopStore } from '@/stores/shop'
import { useLayoutStore, to_storage_url, menu_route_target, type LayoutCategory } from '@/stores/layout'

const cats_open = ref(false)
const open_menu_id = ref<number | null>(null)
const hovered_category = ref<LayoutCategory | null>(null)
const store = useShopStore()
const layout_store = useLayoutStore()

const active_promo_category = computed(() => hovered_category.value ?? layout_store.categories[0] ?? null)

const phone_contact = computed(() => layout_store.contacts.find(contact => contact.key === 'phone'))

const actions = [
    {
        label: 'My Account',
        icon: 'M10.5,9h-6c-2.1,0-3.8,1.7-3.8,3.8v1.5c0,0.4,0.3,0.8,0.8,0.8s0.8-0.3,0.8-0.8v-1.5c0-1.2,1-2.2,2.2-2.2h6c1.2,0,2.2,1,2.2,2.2v1.5c0,0.4,0.3,0.8,0.8,0.8s0.8-0.3,0.8-0.8v-1.5C14.2,10.7,12.6,9,10.5,9zM7.5,7C9.4,7,11,5.4,11,3.5S9.4,0,7.5,0S4,1.6,4,3.5S5.6,7,7.5,7zM7.5,1.5c1.1,0,2,0.9,2,2s-0.9,2-2,2s-2-0.9-2-2S6.4,1.5,7.5,1.5z',
    },
    {
        label: 'Wishlist',
        icon: 'M7.5,13.9l-0.4-0.3c-0.2-0.2-4.6-3.5-5.8-4.8C0.4,7.7-0.1,6.4,0,5.1c0.1-1.2,0.7-2.2,1.6-3c0.9-0.8,2.3-1,3.6-0.8C6.1,1.5,6.9,2,7.5,2.6c0.6-0.6,1.4-1.1,2.4-1.3c1.3-0.2,2.6,0,3.5,0.8l0,0c0.9,0.7,1.5,1.8,1.6,3c0.1,1.3-0.3,2.6-1.3,3.7c-1.2,1.4-5.6,4.7-5.7,4.8L7.5,13.9z',
    },
    {
        label: 'Cart',
        icon: 'M14.1,1.6C14,0.7,13.3,0,12.4,0H2.7C1.7,0,1,0.7,0.9,1.6L0.1,13.1c0,0.5,0.1,1,0.5,1.3C0.9,14.8,1.3,15,1.8,15h11.4c0.5,0,0.9-0.2,1.3-0.6c0.3-0.4,0.5-0.8,0.5-1.3L14.1,1.6z',
    },
]
</script>

<style lang="scss" scoped>
@use "sass:color";

.header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: $color-white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);

    &__top {
        background: $color-lightest;
        border-bottom: 1px solid $color-light;
        padding: 8px 0;
        font-size: 13px;
    }

    &__top-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    &__top-nav {
        display: flex;
        gap: 20px;

        a {
            color: $color-gray;
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__top-socials {
        display: flex;
        gap: 12px;

        a {
            color: $color-gray;
            display: flex;
            align-items: center;
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__main {
        padding: 16px 0;
    }

    &__main-inner {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    &__logo {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    &__logo-icon {
        width: 34px;
        height: 28px;
        display: block;
        flex-shrink: 0;
    }

    &__logo-text {
        font-family: $font-heading;
        font-size: 18px;
        font-weight: 400;
        color: $color-dark;
        letter-spacing: -0.02em;
        white-space: nowrap;

        strong {
            font-weight: 700;
            color: $color-primary;
        }
    }

    &__search {
        flex: 1;
        display: flex;
        align-items: center;
        border: 1px solid $color-light;
        border-radius: 6px;
        overflow: hidden;

        input {
            flex: 1;
            border: none;
            outline: none;
            padding: 10px 14px;
            font-size: 14px;
            font-family: $font-body;
            color: $color-dark;

            &::placeholder {
                color: $color-gray;
            }
        }

        select {
            border: none;
            border-left: 1px solid $color-light;
            outline: none;
            padding: 10px 12px;
            font-size: 13px;
            font-family: $font-body;
            color: $color-gray;
            background: $color-lightest;
            cursor: pointer;
        }

        button {
            padding: 10px 14px;
            background: $color-primary;
            color: $color-white;
            display: flex;
            align-items: center;
            transition: background 0.2s;

            svg path {
                fill: $color-white;
            }

            &:hover {
                background: color.adjust($color-primary, $lightness: -8%);
            }
        }
    }

    &__actions {
        display: flex;
        gap: 16px;
    }

    &__action {
        color: $color-dark;
        display: flex;
        align-items: center;
        position: relative;
        transition: color 0.2s;

        svg {
            fill: currentColor;
        }

        &:hover {
            color: $color-primary;
        }
    }

    &__cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        min-width: 17px;
        height: 17px;
        padding: 0 4px;
        background: $color-primary;
        color: $color-white;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    &__contact {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        padding: 8px 0;
        margin-left: auto;
    }

    &__contact-icon {
        width: 28px;
        height: 28px;
        flex-shrink: 0;

        path {
            fill: $color-primary;
        }
    }

    &__contact-label {
        display: block;
        font-size: 11px;
        color: $color-gray;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        line-height: 1;
        margin-bottom: 3px;
    }

    &__contact-phone {
        display: block;
        font-weight: 700;
        font-size: 14px;
        color: $color-dark;
        line-height: 1;
        transition: color 0.2s;

        &:hover {
            color: $color-primary;
        }
    }

    &__nav-bar {
        border-top: 1px solid $color-light;
        padding: 0;
    }

    &__nav-inner {
        display: flex;
        align-items: center;
    }

    &__nav {
        display: flex;
        gap: 0;
        font-family: $font-lato;
    }

    &__nav-item {
        position: relative;
    }

    &__nav-link {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 14px 18px;
        font-size: 14px;
        font-weight: 500;
        color: $color-dark;
        transition: color 0.2s;
        position: relative;
        white-space: nowrap;

        &::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 18px;
            right: 18px;
            height: 2px;
            background: $color-primary;
            transform: scaleX(0);
            transition: transform 0.2s;
        }

        &:hover,
        &--active {
            color: $color-primary;

            &::after {
                transform: scaleX(1);
            }
        }
    }

    &__nav-chevron {
        width: 8px;
        height: 5px;
        flex-shrink: 0;
    }

    &__nav-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 200;
        min-width: 200px;
        background: $color-white;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.14);
        padding: 8px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;

        &--open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    }

    &__nav-dropdown-item {
        display: block;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 14px;
        color: $color-dark;
        white-space: nowrap;
        transition: background 0.15s, color 0.15s;

        &:hover {
            background: $color-lightest;
            color: $color-primary;
        }
    }

    &__cats-wrap {
        position: relative;
        flex-shrink: 0;
    }

    &__cats-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 18px;
        font-size: 14px;
        font-weight: 600;
        font-family: $font-lato;
        color: $color-dark;
        background: none;
        cursor: pointer;
        transition: color 0.2s;
        white-space: nowrap;
        position: relative;

        &::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 18px;
            right: 18px;
            height: 2px;
            background: $color-primary;
            transform: scaleX(0);
            transition: transform 0.2s;
        }

        &:hover,
        &--open {
            color: $color-primary;

            &::after {
                transform: scaleX(1);
            }
        }
    }

    &__cats-icon {
        width: 15px;
        height: 15px;
        fill: currentColor;
        flex-shrink: 0;
    }

    &__cats-chevron {
        width: 9px;
        height: 6px;
        flex-shrink: 0;
        transition: transform 0.2s;

        .header__cats-btn--open & {
            transform: rotate(180deg);
        }
    }

    &__mega {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 200;
        width: 600px;
        background: $color-white;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.14);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;

        &--open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    }

    &__mega-inner {
        display: grid;
        grid-template-columns: 1fr 180px;
        gap: 0;
    }

    &__mega-cats {
        padding: 16px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        border-right: 1px solid $color-light;
    }

    &__mega-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        background: $color-lightest;
        color: $color-dark;
        transition: background 0.15s, color 0.15s;

        &:hover,
        &--active {
            background: $color-lighter;
            color: $color-primary;
        }
    }

    &__mega-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: $color-white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
    }

    &__mega-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    &__mega-name {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    &__mega-count {
        font-size: 11px;
        color: $color-gray;
    }

    &__mega-promo {
        position: relative;
        border-radius: 0 0 12px 0;
        overflow: hidden;
        min-height: 200px;
    }

    &__mega-promo-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 60%;
        transition: transform 0.4s ease;

        .header__mega-promo:hover & {
            transform: scale(1.08);
        }
    }

    &__mega-promo-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 16px;
        background: linear-gradient(0deg, rgba(0, 0, 0, 0.65) 0%, transparent 60%);
    }

    &__mega-promo-arrow {
        align-self: flex-end;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: $color-white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background 0.2s, transform 0.2s;

        svg {
            width: 11px;
            height: 11px;
            fill: $color-dark;
            transition: fill 0.2s;
        }

        .header__mega-promo:hover & {
            background: $color-primary;
            transform: rotate(45deg);

            svg {
                fill: $color-white;
            }
        }
    }

    &__mega-promo-title {
        font-size: 13px;
        font-weight: 600;
        color: $color-white;
        line-height: 1.4;
        max-width: 90%;
    }
}
</style>
