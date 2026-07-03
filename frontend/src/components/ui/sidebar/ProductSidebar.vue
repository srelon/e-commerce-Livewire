<template>
    <aside class="product-sidebar">
        <div v-for="group in filter_groups" :key="group.title" class="product-sidebar__block">
            <h4 class="product-sidebar__heading">{{ group.title }}</h4>

            <PriceFilter
                v-if="group.type === 'price'"
                :min="price_min_bound"
                :max="price_max_bound"
                :model_min="price_min"
                :model_max="price_max"
                @filter="on_price_filter"
            />

            <FilterGroup
                v-else-if="group.type === 'checkbox'"
                :title="group.title"
                :items="group.items ?? []"
                :modelValue="checkbox_selected(group)"
                @update:modelValue="on_checkbox_change(group, $event)"
            />

            <RatingFilter
                v-else-if="group.type === 'rating'"
                :modelValue="rating_selected(group)"
                @update:modelValue="on_rating_change(group, $event)"
            />
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import PriceFilter from '@/components/ui/filters/PriceFilter.vue'
import FilterGroup from '@/components/ui/filters/FilterGroup.vue'
import RatingFilter from '@/components/ui/filters/RatingFilter.vue'
import type { FilterGroup as FilterGroupConfig } from '@/types/shop'

interface Props {
    filter_groups?: FilterGroupConfig[]
    selected?: Record<string, string[] | number | null>
    price_min?: number
    price_max?: number
}

const props = withDefaults(defineProps<Props>(), {
    filter_groups: () => [],
    selected: () => ({}),
    price_min: 0,
    price_max: 100,
})

const emit = defineEmits<{
    filter: [patch: Record<string, string | undefined>]
}>()

const price_group = computed(() => props.filter_groups.find((group) => group.type === 'price'))
const price_min_bound = computed(() => price_group.value?.min ?? 0)
const price_max_bound = computed(() => price_group.value?.max ?? 100)

function checkbox_selected(group: FilterGroupConfig): string[] {
    return (props.selected[group.query_key] as string[]) ?? []
}

function rating_selected(group: FilterGroupConfig): number | null {
    return (props.selected[group.query_key] as number | null) ?? null
}

function on_checkbox_change(group: FilterGroupConfig, val: string[] | string | null) {
    const sel = Array.isArray(val) ? val : val ? [val] : []
    emit('filter', { [group.query_key]: sel.length ? sel.join(',') : undefined })
}

function on_rating_change(group: FilterGroupConfig, val: number | null) {
    emit('filter', { [group.query_key]: val ? String(val) : undefined })
}

function on_price_filter(val: { min: number, max: number }) {
    emit('filter', {
        price_min: val.min > price_min_bound.value ? String(val.min) : undefined,
        price_max: val.max < price_max_bound.value ? String(val.max) : undefined,
    })
}
</script>

<style lang="scss" scoped>
.product-sidebar {
    width: 260px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 32px;

    &__block {
        border-bottom: 1px solid $color-light;
        padding-bottom: 28px;

        &:last-child {
            border-bottom: none;
        }
    }

    &__heading {
        font-size: 15px;
        font-weight: 700;
        color: $color-dark;
        font-family: $font-heading;
        margin-bottom: 16px;
    }
}
</style>
