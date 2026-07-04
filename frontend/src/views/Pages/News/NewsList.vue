<template>
    <PageBanner title="News" />

    <section class="news section">
        <div class="container">
            <div class="news__inner">
                <div class="news__main">
                    <ListBar :title="`${pagination.total} ${pagination.total === 1 ? 'post' : 'posts'} found`" :loading="is_loading">
                        <template #right>
                            <div class="news__sort-group">
                                <PlainSelect v-model="category" :options="category_options" />
                                <SortSelect v-model="sort_by" :options="sort_options" />
                            </div>
                        </template>
                    </ListBar>

                    <div class="news__grid">
                        <template v-if="is_loading">
                            <BlogCard v-for="n in 6" :key="n" loading />
                        </template>
                        <template v-else>
                            <BlogCard
                                v-for="post in posts"
                                :key="post.slug"
                                :title="post.title"
                                :excerpt="post.excerpt ?? ''"
                                :category="post.category ?? ''"
                                :author="post.author ?? ''"
                                :date="format_date(post.date)"
                                :image="to_storage_url(post.image)"
                                :slug="post.slug"
                            />
                        </template>
                    </div>

                    <BasePagination
                        :current_page="pagination.current_page"
                        :last_page="pagination.last_page"
                        class="news__pagination"
                    />
                </div>

                <Sidebar :show_newsletter="true" />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import PageBanner from '@/components/ui/base/PageBanner.vue'
import ListBar from '@/components/ui/base/ListBar.vue'
import Sidebar from '@/components/ui/sidebar/Sidebar.vue'
import BlogCard from '@/components/ui/blog/BlogCard.vue'
import BasePagination from '@/components/ui/base/BasePagination.vue'
import SortSelect from '@/components/ui/base/SortSelect.vue'
import PlainSelect from '@/components/ui/base/PlainSelect.vue'
import api from '@/plugins/axios'
import { to_storage_url } from '@/stores/layout'
import { useQueryPatch } from '@/composables/useQueryPatch'
import type { BlogPostSummary, Paginated, Pagination } from '@/types/shop'
import type { SortKey } from '@/components/ui/base/SortSelect.vue'

const route = useRoute()
const { patch_query } = useQueryPatch()

const sort_options: SortKey[] = ['newest', 'oldest']
const query_key_order = ['category', 'sort_by', 'page']

const is_loading = ref(true)
const posts = ref<BlogPostSummary[]>([])
const categories = ref<string[]>([])
const pagination = ref<Pagination>({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const sort_by = computed({
    get: () => (route.query.sort_by as string) ?? 'newest',
    set: (value: string) => {
        patch_query({ sort_by: value === 'newest' ? undefined : value }, { order: query_key_order })
    },
})

const category = computed({
    get: () => (route.query.category as string) ?? '',
    set: (value: string) => {
        patch_query({ category: value || undefined }, { order: query_key_order })
    },
})

const category_options = computed(() => [
    {
        value: '',
        label: 'All Categories',
    },
    ...categories.value.map((name) => ({
        value: name,
        label: name,
    })),
])

function format_date(date: string | null): string {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en', { month: 'long', day: 'numeric', year: 'numeric' })
}

function fetch_news() {
    is_loading.value = true
    api.get('news', { params: route.query }).then((res) => {
        const items: Paginated<BlogPostSummary> = res.data.data.items
        posts.value = items.data
        categories.value = res.data.data.categories
        pagination.value = items.pagination
    }).finally(() => {
        is_loading.value = false
    })
}

watch(
    () => route.query,
    fetch_news,
    {
        immediate: true,
        deep: true,
    },
)
</script>

<style lang="scss" scoped>
.news {
    &__sort-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    &__inner {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    &__main {
        flex: 1;
        min-width: 0;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    &__pagination {
        margin-top: 40px;
    }
}
</style>
