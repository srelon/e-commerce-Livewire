<template>
    <div class="filter-group">
        <div v-if="items.length > 8" class="filter-group__search">
            <input
                v-model="search"
                type="text"
                class="filter-group__search-input"
                placeholder="Search..."
            >
            <button
                v-if="search"
                class="filter-group__search-clear"
                aria-label="Clear search"
                @click="search = ''"
            >
                <svg viewBox="0 0 10 10" aria-hidden="true">
                    <path d="M1 1l8 8M9 1L1 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <ul class="filter-group__list">
            <li v-for="item in filtered_items" :key="item.name" class="filter-group__item">
                <label class="filter-group__label">
                    <input
                        v-if="type === 'radio'"
                        type="radio"
                        :name="title"
                        :value="item.name"
                        v-model="selected"
                    >
                    <input
                        v-else
                        type="checkbox"
                        :value="item.name"
                        v-model="selected"
                    >
                    <span class="filter-group__check"></span>
                    <slot name="label" :item="item">{{ item.name }}</slot>
                </label>
                <span v-if="item.count !== undefined" class="filter-group__count">{{ item.count }}</span>
            </li>
            <li v-if="filtered_items.length === 0" class="filter-group__empty">No results</li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface Item {
    name: string
    count?: number
}

interface Props {
    title: string
    items: Item[]
    type?: 'checkbox' | 'radio'
    modelValue?: string[] | string | null
}

const props = withDefaults(defineProps<Props>(), {
    type: 'checkbox',
    modelValue: () => [],
})

const emit = defineEmits<{
    'update:modelValue': [val: string[] | string | null]
}>()

const search = ref('')

const filtered_items = computed(() => {
    if (!search.value) return props.items
    const q = search.value.toLowerCase()
    return props.items.filter((item) => item.name.toLowerCase().includes(q))
})

const selected = ref<string[] | string | null>(
    props.type === 'radio' ? (props.modelValue as string ?? null) : (props.modelValue as string[] ?? [])
)

watch(selected, (val) => emit('update:modelValue', val))
watch(
    () => props.modelValue,
    (val) => {
        const next = val ?? (props.type === 'radio' ? null : [])
        if (JSON.stringify(next) === JSON.stringify(selected.value)) return
        selected.value = next
    },
)
</script>

<style lang="scss" scoped>
.filter-group {
    &__search {
        position: relative;
        margin-bottom: 12px;
    }

    &__search-input {
        width: 100%;
        padding: 7px 32px 7px 10px;
        border: 1.5px solid $color-light;
        border-radius: 6px;
        font-size: 13px;
        font-family: $font-body;
        color: $color-dark;
        outline: none;
        transition: border-color 0.15s;

        &:focus {
            border-color: $color-primary;
        }

        &::placeholder {
            color: $color-gray;
        }
    }

    &__search-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        color: $color-gray;
        cursor: pointer;
        transition: color 0.15s;

        svg {
            width: 10px;
            height: 10px;
        }

        &:hover {
            color: $color-dark;
        }
    }

    &__list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 280px;
        overflow-y: auto;
        padding-right: 4px;

        &::-webkit-scrollbar {
            width: 4px;
        }

        &::-webkit-scrollbar-track {
            background: $color-lightest;
            border-radius: 2px;
        }

        &::-webkit-scrollbar-thumb {
            background: $color-light;
            border-radius: 2px;

            &:hover {
                background: $color-gray;
            }
        }
    }

    &__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    &__label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 14px;
        color: $color-dark;

        input {
            display: none;
        }

        input:checked + .filter-group__check {
            background: $color-primary;
            border-color: $color-primary;

            &::after {
                opacity: 1;
            }
        }
    }

    &__check {
        width: 16px;
        height: 16px;
        border: 1.5px solid $color-light;
        border-radius: 3px;
        flex-shrink: 0;
        position: relative;
        transition: background 0.15s, border-color 0.15s;

        &::after {
            content: '';
            position: absolute;
            left: 4px;
            top: 1px;
            width: 5px;
            height: 9px;
            border: 2px solid $color-white;
            border-left: none;
            border-top: none;
            transform: rotate(45deg);
            opacity: 0;
        }
    }

    &__count {
        font-size: 12px;
        color: $color-gray;
        flex-shrink: 0;
    }

    &__empty {
        font-size: 13px;
        color: $color-gray;
    }
}
</style>
