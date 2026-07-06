<template>
    <section class="about-cta">
        <div class="container">
            <div class="about-cta__inner">
                <div class="about-cta__left">
                    <h2 class="about-cta__title">Immerse Yourself In The Fascinating World Of Literature</h2>
                    <router-link :to="{ name: 'products' }" class="about-cta__link">
                        <BaseButton>Explore Collection</BaseButton>
                    </router-link>
                </div>
                <div class="about-cta__right">
                    <p class="about-cta__sub">Join our community</p>
                    <p class="about-cta__count">58,000+ subscribers</p>
                    <NewsletterForm />
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import NewsletterForm from '@/components/ui/forms/NewsletterForm.vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
</script>

<style lang="scss" scoped>
.about-cta {
    background: $color-dark;
    padding: 60px 0;

    &__inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    &__title {
        font-size: clamp(20px, 2vw, 30px);
        color: $color-white;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 24px;
    }

    &__link {
        display: inline-flex;
    }

    &__sub {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.5);
        margin-bottom: 4px;
    }

    &__count {
        font-size: 28px;
        font-weight: 700;
        color: $color-primary;
        margin-bottom: 20px;
    }
}
</style>
