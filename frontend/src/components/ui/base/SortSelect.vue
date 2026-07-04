<template>
    <PlainSelect :model-value="modelValue" :options="select_options" @update:model-value="emit('update:modelValue', $event)" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import PlainSelect from '@/components/ui/base/PlainSelect.vue'

export type SortKey =
    | 'default'
    | 'popularity'
    | 'rating'
    | 'price_asc'
    | 'price_desc'
    | 'newest'
    | 'oldest'
    | 'books'
    | 'bestseller'

const SORT_LABELS: Record<SortKey, string> = {
    default: 'Default sorting',
    popularity: 'Sort by popularity',
    rating: 'Sort by rating',
    price_asc: 'Sort by price: low to high',
    price_desc: 'Sort by price: high to low',
    newest: 'Newest first',
    oldest: 'Oldest first',
    books: 'Sort by books',
    bestseller: 'Sort by bestseller',
}

interface Props {
    options: SortKey[]
    modelValue: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

const select_options = computed(() => props.options.map((key) => ({
    value: key,
    label: SORT_LABELS[key],
})))
</script>
