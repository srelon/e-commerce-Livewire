<template>
    <PageBanner title="Authors" />

    <section class="authors section">
        <div class="container">
            <div class="authors__inner">
                <div class="authors__main">
                    <ListBar :title="`${pagination.total} ${pagination.total === 1 ? 'author' : 'authors'} found`" :loading="is_loading">
                        <template #right>
                            <SortSelect v-model="sort_by" :options="sort_options" />
                        </template>
                    </ListBar>

                    <div class="authors__grid">
                        <template v-if="is_loading">
                            <AuthorCard v-for="n in 6" :key="n" loading />
                        </template>
                        <template v-else>
                            <AuthorCard
                                v-for="author in authors"
                                :key="author.slug"
                                v-bind="author"
                            />
                        </template>
                    </div>

                    <BasePagination
                        :current_page="pagination.current_page"
                        :last_page="pagination.last_page"
                        class="authors__pagination"
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
import AuthorCard from '@/components/ui/author/AuthorCard.vue'
import BasePagination from '@/components/ui/base/BasePagination.vue'
import SortSelect from '@/components/ui/base/SortSelect.vue'
import api from '@/plugins/axios'
import { useQueryPatch } from '@/composables/useQueryPatch'
import type { AuthorListItem, Paginated, Pagination } from '@/types/shop'
import type { SortKey } from '@/components/ui/base/SortSelect.vue'

const route = useRoute()
const { patch_query } = useQueryPatch()

const sort_options: SortKey[] = ['newest', 'books', 'bestseller', 'oldest']

const is_loading = ref(true)
const authors = ref<AuthorListItem[]>([])
const pagination = ref<Pagination>({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const sort_by = computed({
    get: () => (route.query.sort_by as string) ?? 'newest',
    set: (value: string) => {
        patch_query({ sort_by: value === 'newest' ? undefined : value }, { order: ['sort_by', 'page'] })
    },
})

function fetch_authors() {
    is_loading.value = true
    api.get('authors', { params: route.query }).then((res) => {
        const items: Paginated<AuthorListItem> = res.data.data.items
        authors.value = items.data
        pagination.value = items.pagination
    }).finally(() => {
        is_loading.value = false
    })
}

watch(
    () => route.query,
    fetch_authors,
    {
        immediate: true,
        deep: true,
    },
)
</script>

<style lang="scss" scoped>
.authors {
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
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    &__pagination {
        margin-top: 40px;
    }
}
</style>
