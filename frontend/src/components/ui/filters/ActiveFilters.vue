<template>
    <div v-if="chips.length" class="active-filters">
        <button
            v-for="chip in chips"
            :key="chip.key"
            class="active-filters__chip"
            :aria-label="`Remove ${chip.label} filter`"
            @click="emit('remove', chip.patch)"
        >
            {{ chip.label }}
            <span class="active-filters__remove" aria-hidden="true">×</span>
        </button>

        <button class="active-filters__clear" @click="emit('clear', clear_patch)">
            Clear All
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { FilterGroup } from '@/types/shop'

interface Props {
    filter_groups?: FilterGroup[]
    selected?: Record<string, string[] | number | null>
    price_min?: number
    price_max?: number
    price_bounds_min?: number
    price_bounds_max?: number
}

const props = withDefaults(defineProps<Props>(), {
    filter_groups: () => [],
    selected: () => ({}),
    price_min: 0,
    price_max: 100,
    price_bounds_min: 0,
    price_bounds_max: 100,
})

const emit = defineEmits<{
    remove: [patch: Record<string, string | undefined>]
    clear: [patch: Record<string, string | undefined>]
}>()

interface Chip {
    key: string
    label: string
    patch: Record<string, string | undefined>
}

const chips = computed<Chip[]>(() => {
    const result: Chip[] = []

    for (const group of props.filter_groups) {
        if (group.type === 'price') {
            const narrowed = props.price_min > props.price_bounds_min || props.price_max < props.price_bounds_max
            if (narrowed) {
                result.push({
                    key: 'price',
                    label: `$${props.price_min} - $${props.price_max}`,
                    patch: { price_min: undefined, price_max: undefined },
                })
            }
        } else if (group.type === 'checkbox') {
            const values = (props.selected[group.query_key] as string[]) ?? []
            for (const value of values) {
                const remaining = values.filter((v) => v !== value)
                result.push({
                    key: `${group.query_key}:${value}`,
                    label: value,
                    patch: { [group.query_key]: remaining.length ? remaining.join(',') : undefined },
                })
            }
        } else if (group.type === 'rating') {
            const value = props.selected[group.query_key] as number | null
            if (value) {
                result.push({
                    key: 'rating',
                    label: `${value}+ Stars`,
                    patch: { rating: undefined },
                })
            }
        }
    }

    return result
})

const clear_patch = computed<Record<string, string | undefined>>(() => {
    const patch: Record<string, string | undefined> = {}
    for (const group of props.filter_groups) {
        if (group.type === 'price') {
            patch.price_min = undefined
            patch.price_max = undefined
        } else {
            patch[group.query_key] = undefined
        }
    }
    return patch
})
</script>

<style lang="scss" scoped>
.active-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;

    &__chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1.5px solid $color-light;
        background: $color-lightest;
        font-size: 13px;
        color: $color-dark;
        transition: border-color 0.15s, color 0.15s;

        &:hover {
            border-color: $color-primary;
            color: $color-primary;
        }
    }

    &__remove {
        font-size: 15px;
        line-height: 1;
        color: $color-gray;
    }

    &__chip:hover &__remove {
        color: $color-primary;
    }

    &__clear {
        font-size: 13px;
        font-weight: 600;
        color: $color-primary;
        text-decoration: underline;
        text-underline-offset: 2px;

        &:hover {
            color: $color-dark;
        }
    }
}
</style>
