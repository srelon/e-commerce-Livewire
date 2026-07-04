<template>
    <PageBanner title="Contact Us" />

    <section class="contact section">
        <div class="container">
            <div class="contact__grid">
                <div class="contact__form-wrap">
                    <h2 class="contact__heading">{{ page?.title ?? 'Get In Touch With Us' }}</h2>
                    <p class="contact__sub">{{ page?.excerpt ?? 'Have a question or need help finding the perfect book? Our team is here for you.' }}</p>
                    <ContactForm />
                </div>

                <StoreLocation title="Our Store Location" />
            </div>
        </div>
    </section>

    <FaqSection title="Helpful Answers To Your Questions" :items="faq" :loading="is_loading" />
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import ContactForm from '@/components/ui/forms/ContactForm.vue'
import FaqSection from '@/components/ui/faq/FaqSection.vue'
import StoreLocation from '@/components/ui/contact/StoreLocation.vue'
import api from '@/plugins/axios'
import type { FaqItem } from '@/types/global'
import type { PageBundle } from '@/types/shop'

const is_loading = ref(true)
const faq = ref<FaqItem[]>([])
const page = ref<PageBundle | null>(null)

onMounted(() => {
    api.get('pages/contact').then((res) => {
        faq.value = res.data.data.faqs
        page.value = res.data.data.page
    }).finally(() => {
        is_loading.value = false
    })
})
</script>

<style lang="scss" scoped>
.contact {
    &__grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 60px;
        align-items: start;
    }

    &__heading {
        font-size: clamp(18px, 1.8vw, 26px);
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 10px;
    }

    &__sub {
        font-size: 14px;
        color: $color-gray;
        line-height: 1.65;
        margin-bottom: 28px;
    }
}
</style>
