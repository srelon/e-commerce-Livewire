<template>
    <HeroSection :title="page?.title" :description="page?.excerpt ?? undefined" />
    <CategoryStrip />
    <BestsellersSection :products="bestsellers" :loading="is_loading" />
    <BestAuthorSection :author="best_author" :loading="is_loading" />
    <BestRatedSection :products="best_rated" :loading="is_loading" />
    <BlogSection :posts="blog" :loading="is_loading" />
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import HeroSection from '@/components/ui/home/HeroSection.vue'
import CategoryStrip from '@/components/ui/home/CategoryStrip.vue'
import BestsellersSection from '@/components/ui/home/BestsellersSection.vue'
import BestAuthorSection from '@/components/ui/home/BestAuthorSection.vue'
import BestRatedSection from '@/components/ui/home/BestRatedSection.vue'
import BlogSection from '@/components/ui/blog/BlogSection.vue'
import api from '@/plugins/axios'
import type { AuthorSummary, BlogPostSummary, PageBundle, ProductSummary } from '@/types/shop'

const is_loading = ref(true)
const bestsellers = ref<ProductSummary[]>([])
const best_author = ref<AuthorSummary | null>(null)
const best_rated = ref<ProductSummary[]>([])
const blog = ref<BlogPostSummary[]>([])
const page = ref<PageBundle | null>(null)

onMounted(() => {
    api.get('pages/home').then((res) => {
        const data = res.data.data
        bestsellers.value = data.bestsellers
        best_author.value = data.best_author
        best_rated.value = data.best_rated
        blog.value = data.blog
        page.value = data.page
    }).finally(() => {
        is_loading.value = false
    })
})
</script>
