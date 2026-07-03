<template>
    <select
        :value="modelValue"
        class="sort-select"
        @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>

<script setup lang="ts">
export interface SortOption {
    value: string
    label: string
}

interface Props {
    options: SortOption[]
    modelValue: string
}

defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<style lang="scss" scoped>
.sort-select {
    padding: 8px 14px;
    border: 1.5px solid $color-light;
    border-radius: 6px;
    font-size: 14px;
    font-family: $font-body;
    color: $color-dark;
    outline: none;
    cursor: pointer;

    &:focus {
        border-color: $color-primary;
    }
}
</style>
