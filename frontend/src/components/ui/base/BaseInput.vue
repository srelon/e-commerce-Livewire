<template>
    <div class="base-input">
        <label v-if="label" class="base-input__label">{{ label }}</label>
        <textarea
            v-if="type === 'textarea'"
            :value="display_value"
            :placeholder="placeholder"
            :rows="rows"
            class="base-input__field"
            :class="{ 'base-input__field--error': display_error, 'base-input__field--pill': pill }"
            @input="on_input"
            @blur="on_blur"
        ></textarea>
        <input
            v-else
            :value="display_value"
            :type="type"
            :placeholder="placeholder"
            class="base-input__field"
            :class="{ 'base-input__field--error': display_error, 'base-input__field--pill': pill }"
            @input="on_input"
            @blur="on_blur"
        >
        <span v-if="display_error" class="base-input__error">{{ display_error }}</span>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useField } from 'vee-validate'

interface Props {
    name?: string
    modelValue?: string
    label?: string
    type?: string
    placeholder?: string
    rows?: number
    error?: string
    pill?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    rows: 5,
    name: '',
    pill: false,
})

const emit = defineEmits<{ 'update:modelValue': [value: string] }>()

const { value: field_value, errorMessage, meta: field_meta, handleBlur } = useField<string>(() => props.name)

const display_value = computed(() => props.name ? field_value.value : (props.modelValue ?? ''))
const display_error = computed(() => {
    if (props.name) return field_meta.touched ? errorMessage.value : undefined
    return props.error
})

function format_phone(raw: string): string {
    const digits = raw.replace(/\D/g, '')
    if (!digits) return ''

    const pattern = [3, 3, 2, 2, 2, 2, 2]
    const chunks: string[] = []
    let i = 0

    for (const len of pattern) {
        if (i >= digits.length) break
        chunks.push(digits.slice(i, i + len))
        i += len
    }

    if (i < digits.length) chunks.push(digits.slice(i))

    return chunks.join(' ')
}

function on_input(event: Event) {
    const target = event.target as HTMLInputElement | HTMLTextAreaElement
    let v = target.value

    if (props.type === 'tel') {
        v = format_phone(v)
        ;(target as HTMLInputElement).value = v
    }

    if (props.name) {
        field_value.value = v
    } else {
        emit('update:modelValue', v)
    }
}

function on_blur(event: Event) {
    if (props.name) handleBlur(event)
}
</script>

<style lang="scss" scoped>
.base-input {
    display: flex;
    flex-direction: column;
    gap: 6px;

    &__label {
        @include form-field-label;
    }

    &__field {
        @include form-field-base;
        width: 100%;

        &::placeholder {
            color: $color-gray;
        }

        &--pill {
            border-radius: 30px;
            padding: 10px 16px;
        }
    }

    textarea {
        resize: vertical;
    }

    &__error {
        @include form-field-error-text;
    }
}
</style>
