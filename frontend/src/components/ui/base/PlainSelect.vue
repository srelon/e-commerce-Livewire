<template>
    <div class="plain-select">
        <select
            :value="modelValue"
            class="plain-select__field"
            @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <svg class="plain-select__arrow" viewBox="0 0 10 6" aria-hidden="true">
            <path d="M1 1l4 4 4-4"/>
        </svg>
    </div>
</template>

<script setup lang="ts">
export interface PlainSelectOption {
    value: string
    label: string
}

interface Props {
    options: PlainSelectOption[]
    modelValue: string
}

defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<style lang="scss" scoped>
.plain-select {
    position: relative;

    &__field {
        @include form-field-base;
        width: 100%;
        padding-right: 36px;
        background: $color-white;
        appearance: none;
        cursor: pointer;
    }

    &__arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 6px;
        pointer-events: none;

        path {
            fill: none;
            stroke: $color-gray;
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
    }
}
</style>
