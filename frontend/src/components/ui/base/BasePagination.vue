<template>
    <nav v-if="last_page > 1" ref="container" class="pagination">
        <button
            class="pagination__btn"
            :class="{ 'pagination__btn--disabled': current_page === 1 }"
            :disabled="current_page === 1"
            @click="change_page(current_page - 1)"
        >←</button>

        <button v-if="first_visible > 1" class="pagination__btn" @click="change_page(1)">1</button>
        <span v-if="first_visible > 2" class="pagination__ellipsis">…</span>

        <button
            v-for="page in pages"
            :key="page"
            class="pagination__btn"
            :class="{ 'pagination__btn--active': page === current_page }"
            @click="change_page(page)"
        >{{ page }}</button>

        <span v-if="last_visible < last_page - 1" class="pagination__ellipsis">…</span>
        <button v-if="last_visible < last_page" class="pagination__btn" @click="change_page(last_page)">{{ last_page }}</button>

        <button
            class="pagination__btn"
            :class="{ 'pagination__btn--disabled': current_page === last_page }"
            :disabled="current_page === last_page"
            @click="change_page(current_page + 1)"
        >→</button>
    </nav>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQueryPatch } from '@/composables/useQueryPatch'

const props = defineProps<{
    current_page: number
    last_page: number
}>()

const emit = defineEmits<{
    'page-change': [page: number]
}>()

const { patch_query } = useQueryPatch()
const container = ref<HTMLElement | null>(null)

const pages = computed(() => {
    const start = Math.max(1, props.current_page - 2)
    const end = Math.min(props.last_page, props.current_page + 2)
    const arr = []
    for (let i = start; i <= end; i++) arr.push(i)
    return arr
})

const first_visible = computed(() => pages.value[0] ?? 0)
const last_visible = computed(() => pages.value[pages.value.length - 1] ?? 0)

function change_page(page: number) {
    if (page < 1 || page > props.last_page || page === props.current_page) return
    patch_query({ page: page > 1 ? page : undefined }, { reset_page: false })
    emit('page-change', page)

    const content = container.value?.closest('section')
    if (content instanceof HTMLElement) {
        window.scrollTo({ top: content.offsetTop - 120, behavior: 'smooth' })
    }
}
</script>

<style lang="scss" scoped>
.pagination {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;

    &__btn {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 10px;
        border: 1.5px solid $color-light;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        font-family: $font-body;
        color: $color-dark;
        background: $color-white;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, border-color 0.2s;

        &:hover {
            background: $color-primary;
            border-color: $color-primary;
            color: $color-white;
        }

        &--active {
            background: $color-primary;
            border-color: $color-primary;
            color: $color-white;
            pointer-events: none;
        }

        &--disabled {
            background: $color-lightest;
            border-color: $color-light;
            color: $color-gray;
            cursor: default;
            pointer-events: none;
        }
    }

    &__ellipsis {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        font-size: 14px;
        color: $color-gray;
    }
}
</style>
