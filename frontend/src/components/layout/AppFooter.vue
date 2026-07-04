<template>
    <footer class="footer">
        <div class="footer__main">
            <div class="container footer__grid">

                <div class="footer__col footer__col--brand">
                    <router-link :to="{ name: 'home' }" class="footer__logo" aria-label="Book Store">
                        <svg class="footer__logo-icon" viewBox="0 0 34 28" fill="none" aria-hidden="true">
                            <path d="M17 5L17 23" stroke="#ff6310" stroke-width="2" stroke-linecap="round"/>
                            <path d="M17 5L4 7.5L4 25L17 23Z" fill="#ff6310" fill-opacity="0.18" stroke="#ff6310" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M17 5L30 7.5L30 25L17 23Z" fill="#ff6310" fill-opacity="0.18" stroke="#ff6310" stroke-width="1.5" stroke-linejoin="round"/>
                            <line x1="7" y1="12" x2="14" y2="11.4" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                            <line x1="7" y1="16" x2="14" y2="15.4" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                            <line x1="20" y1="11.4" x2="27" y2="12" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                            <line x1="20" y1="15.4" x2="27" y2="16" stroke="#ff6310" stroke-width="1.2" stroke-linecap="round" opacity="0.65"/>
                        </svg>
                        <span class="footer__logo-text"><strong>Book</strong>Store</span>
                    </router-link>

                    <p class="footer__desc">Your trusted destination for books across every genre. Browse thousands of titles and discover your next great read.</p>

                    <ul class="footer__contacts">
                        <li>
                            <svg viewBox="0 0 15 15" aria-hidden="true">
                                <path d="M7.5.5A6.5 6.5 0 1 0 14 7 6.508 6.508 0 0 0 7.5.5m0 11.75A5.25 5.25 0 1 1 12.75 7 5.256 5.256 0 0 1 7.5 12.25M8.125 7V4.25a.625.625 0 0 0-1.25 0V7.5a.625.625 0 0 0 .625.625H10a.625.625 0 0 0 0-1.25z"/>
                            </svg>
                            <span>Mon–Sat: 9:00AM – 6:00PM</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 15 15" aria-hidden="true">
                                <path d="M7.5 0A7.5 7.5 0 1 0 15 7.5 7.509 7.509 0 0 0 7.5 0m3.31 11.01-3.66-2.13a.5.5 0 0 1-.15-.12V3.75a.75.75 0 0 1 1.5 0v4.6l3.26 1.9a.75.75 0 0 1-.95 1.16"/>
                            </svg>
                            <a href="tel:578-393-4937">578-393-4937</a>
                        </li>
                        <li>
                            <svg viewBox="0 0 15 15" aria-hidden="true">
                                <path d="M13.5 2h-12A1.5 1.5 0 0 0 0 3.5v8A1.5 1.5 0 0 0 1.5 13h12a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 13.5 2m0 1 .09.007L7.5 7.572 1.41 3.007A.5.5 0 0 1 1.5 3zM1 11.5v-7.8l6.22 4.573a.5.5 0 0 0 .56 0L14 3.7v7.8a.5.5 0 0 1-.5.5h-12a.5.5 0 0 1-.5-.5"/>
                            </svg>
                            <a href="mailto:hello@example.com">hello@example.com</a>
                        </li>
                        <li>
                            <svg viewBox="0 0 15 15" aria-hidden="true">
                                <path d="M7.5 0A5.5 5.5 0 0 0 2 5.5C2 9.358 7.5 15 7.5 15S13 9.358 13 5.5A5.5 5.5 0 0 0 7.5 0m0 7.5a2 2 0 1 1 2-2 2 2 0 0 1-2 2"/>
                            </svg>
                            <span>27 Division St, New York, NY 10002</span>
                        </li>
                    </ul>

                    <div class="footer__socials">
                        <a href="#" aria-label="Facebook" class="footer__social">
                            <svg width="16" height="16" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M20,10.1c0-5.5-4.5-10-10-10S0,4.5,0,10.1c0,5,3.7,9.1,8.4,9.9v-7H5.9v-2.9h2.5V7.9C8.4,5.4,9.9,4,12.2,4c1.1,0,2.2,0.2,2.2,0.2v2.5h-1.3c-1.2,0-1.6,0.8-1.6,1.6v1.9h2.8L13.9,13h-2.3v7C16.3,19.2,20,15.1,20,10.1z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="X (Twitter)" class="footer__social">
                            <svg width="16" height="16" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M2.9 0C1.3 0 0 1.3 0 2.9v14.3C0 18.7 1.3 20 2.9 20h14.3c1.6 0 2.9-1.3 2.9-2.9V2.9C20 1.3 18.7 0 17.1 0H2.9zm13.2 3.8L11.5 9l5.5 7.2h-4.3l-3.3-4.4-3.8 4.4H3.4l5-5.7-5.3-6.7h4.4l3 4 3.5-4h2.1zM14.4 15 6.8 5H5.6l7.7 10h1.1z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram" class="footer__social">
                            <svg width="16" height="16" viewBox="0 0 20 20" aria-hidden="true">
                                <circle cx="10" cy="10" r="3.3"/>
                                <path d="M14.2,0H5.8C2.6,0,0,2.6,0,5.8v8.3C0,17.4,2.6,20,5.8,20h8.3c3.2,0,5.8-2.6,5.8-5.8V5.8C20,2.6,17.4,0,14.2,0zM10,15c-2.8,0-5-2.2-5-5s2.2-5,5-5s5,2.2,5,5S12.8,15,10,15z M15.8,5C15.4,5,15,4.6,15,4.2s0.4-0.8,0.8-0.8s0.8,0.4,0.8,0.8S16.3,5,15.8,5z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="YouTube" class="footer__social">
                            <svg width="16" height="16" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M15,0H5C2.2,0,0,2.2,0,5v10c0,2.8,2.2,5,5,5h10c2.8,0,5-2.2,5-5V5C20,2.2,17.8,0,15,0z M14.5,10.9l-6.8,3.8c-0.1,0.1-0.3,0.1-0.5,0.1c-0.5,0-1-0.4-1-1l0,0V6.2c0-0.5,0.4-1,1-1c0.2,0,0.3,0,0.5,0.1l6.8,3.8c0.5,0.3,0.7,0.8,0.4,1.3C14.8,10.6,14.6,10.8,14.5,10.9z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="footer__col">
                    <h4 class="footer__title">Explore</h4>
                    <ul class="footer__links">
                        <li><router-link :to="{ name: 'about' }">About Us</router-link></li>
                        <li><router-link :to="{ name: 'authors' }">Authors</router-link></li>
                        <li><router-link :to="{ name: 'products' }">Products</router-link></li>
                        <li><router-link :to="{ name: 'news' }">News</router-link></li>
                        <li><router-link :to="{ name: 'contact' }">Contact Us</router-link></li>
                    </ul>
                </div>

                <div class="footer__col">
                    <h4 class="footer__title">Useful Links</h4>
                    <ul class="footer__links">
                        <li><a href="#">My Account</a></li>
                        <li><a href="#">Wishlist</a></li>
                        <li><a href="#">Order Tracking</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms &amp; Conditions</a></li>
                    </ul>
                </div>

                <div class="footer__col">
                    <h4 class="footer__title">Categories</h4>
                    <ul class="footer__links">
                        <li><a href="#">Art &amp; Design</a></li>
                        <li><a href="#">Self-help</a></li>
                        <li><a href="#">Science</a></li>
                        <li><a href="#">Romance</a></li>
                        <li><a href="#">Novels</a></li>
                        <li><a href="#">History</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer__bottom">
            <div class="container footer__bottom-inner">
                <p class="footer__copy">&copy; {{ year }} Book Store. All rights reserved.</p>
                <div class="footer__payments">
                    <svg width="42" height="26" viewBox="0 0 42 26" aria-label="Visa" role="img">
                        <rect width="42" height="26" rx="4" fill="#fff" fill-opacity="0.08" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                        <text x="21" y="17" text-anchor="middle" font-size="11" font-weight="800" fill="#fff" font-family="sans-serif" letter-spacing="0.5">VISA</text>
                    </svg>
                    <svg width="42" height="26" viewBox="0 0 42 26" aria-label="Mastercard" role="img">
                        <rect width="42" height="26" rx="4" fill="#fff" fill-opacity="0.08" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                        <circle cx="16" cy="13" r="7" fill="#eb001b" opacity="0.9"/>
                        <circle cx="26" cy="13" r="7" fill="#f79e1b" opacity="0.9"/>
                        <path d="M21 7.4a7 7 0 0 1 0 11.2A7 7 0 0 1 21 7.4z" fill="#ff5f00"/>
                    </svg>
                    <svg width="42" height="26" viewBox="0 0 42 26" aria-label="PayPal" role="img">
                        <rect width="42" height="26" rx="4" fill="#fff" fill-opacity="0.08" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                        <text x="21" y="17" text-anchor="middle" font-size="8" font-weight="700" fill="#fff" font-family="sans-serif">PayPal</text>
                    </svg>
                    <svg width="42" height="26" viewBox="0 0 42 26" aria-label="Apple Pay" role="img">
                        <rect width="42" height="26" rx="4" fill="#fff" fill-opacity="0.08" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                        <text x="21" y="17" text-anchor="middle" font-size="8" font-weight="600" fill="#fff" font-family="sans-serif">Apple Pay</text>
                    </svg>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
const year = new Date().getFullYear()
</script>

<style lang="scss" scoped>
.footer {
    background: $color-dark;
    color: $color-white;

    &__main {
        padding: 64px 0 48px;
    }

    &__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr 1fr 1fr;
        gap: 48px;
    }

    &__col {
        &--brand {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
    }

    &__logo {
        display: inline-flex;
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
        color: $color-white;
        letter-spacing: -0.02em;
        white-space: nowrap;

        strong {
            font-weight: 700;
            color: $color-primary;
        }
    }

    &__desc {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.55);
        line-height: 1.7;
    }

    &__contacts {
        display: flex;
        flex-direction: column;
        gap: 10px;

        li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
        }

        svg {
            width: 14px;
            height: 14px;
            fill: $color-primary;
            flex-shrink: 0;
            margin-top: 2px;
        }

        a {
            color: rgba(255, 255, 255, 0.6);
            transition: color 0.2s;

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__socials {
        display: flex;
        gap: 8px;
    }

    &__social {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;

        svg {
            fill: rgba(255, 255, 255, 0.7);
            transition: fill 0.2s;
        }

        &:hover {
            background: $color-primary;

            svg {
                fill: $color-white;
            }
        }
    }

    &__title {
        font-size: 15px;
        font-weight: 700;
        color: $color-white;
        margin-bottom: 18px;
        font-family: $font-heading;
    }

    &__links {
        display: flex;
        flex-direction: column;
        gap: 10px;

        li {
            font-size: 14px;
        }

        a {
            color: rgba(255, 255, 255, 0.55);
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;

            &::before {
                content: '—';
                font-size: 11px;
                color: $color-primary;
                opacity: 0.7;
            }

            &:hover {
                color: $color-primary;
            }
        }
    }

    &__bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding: 20px 0;
    }

    &__bottom-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    &__copy {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.4);
    }

    &__payments {
        display: flex;
        gap: 8px;
        align-items: center;
    }
}
</style>
