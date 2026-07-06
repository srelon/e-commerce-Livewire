<template>
    <div class="review-form">
        <h3 class="review-form__title">{{ title ?? (is_editing ? 'Edit Your Review' : 'Write a Review') }}</h3>

        <div v-if="reply_to_name" class="review-form__reply-target">
            Replying to <strong>{{ reply_to_name }}</strong>
            <button type="button" class="review-form__reply-target-clear" aria-label="Clear reply target" @click="emit('clear-reply-to')">×</button>
        </div>

        <div v-if="show_rating" class="review-form__rating">
            <label>Your Rating</label>
            <div class="review-form__stars">
                <button
                    v-for="i in 5"
                    :key="i"
                    type="button"
                    class="review-form__star-btn"
                    :class="{ 'review-form__star-btn--filled': i <= rating }"
                    @click="rating = i"
                >★</button>
            </div>
        </div>

        <div class="review-form__field">
            <div class="review-form__editor-wrap">
                <div
                    ref="editor"
                    contenteditable="true"
                    class="review-form__editor"
                    data-placeholder="Write your review..."
                    @input="update_count"
                    @keydown="on_keydown"
                ></div>
                <button
                    ref="emoji_btn"
                    type="button"
                    class="review-form__emoji-btn"
                    title="Emoji"
                    @click.stop="show_emoji = !show_emoji"
                >🙂</button>
                <div v-if="show_emoji" class="review-form__emoji-picker" @click.stop>
                    <button
                        v-for="emoji in EMOJIS"
                        :key="emoji"
                        type="button"
                        class="review-form__emoji-item"
                        @click="insert_emoji(emoji)"
                    >{{ emoji }}</button>
                </div>
            </div>

            <div class="review-form__toolbar">
                <div class="review-form__tools">
                    <button type="button" class="review-form__tool" title="Bold" @click="exec('bold')"><b>B</b></button>
                    <button type="button" class="review-form__tool review-form__tool--italic" title="Italic" @click="exec('italic')"><i>I</i></button>
                    <button type="button" class="review-form__tool review-form__tool--underline" title="Underline" @click="exec('underline')"><u>U</u></button>
                    <button type="button" class="review-form__tool review-form__tool--strike" title="Strikethrough" @click="exec('strikeThrough')"><s>S</s></button>
                </div>
                <span
                    class="review-form__counter"
                    :class="{
                        'review-form__counter--warn': char_count > max_len - 100,
                        'review-form__counter--over': char_count > max_len,
                    }"
                >
                    {{ char_count }}/{{ max_len }}
                </span>
            </div>
        </div>

        <p v-if="error" class="review-form__error">{{ error }}</p>

        <div class="review-form__actions">
            <BaseButton type="button" :disabled="is_submitting" @click="submit">
                {{ is_submitting ? 'Saving...' : default_submit_label }}
            </BaseButton>
            <BaseButton v-if="is_editing" type="button" variant="text" @click="emit('cancel')">Cancel</BaseButton>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'

interface Props {
    initial_rating?: number
    initial_body?: string
    is_editing?: boolean
    is_submitting?: boolean
    max_len?: number
    show_rating?: boolean
    submit_label?: string
    title?: string
    reply_to_name?: string
}

const props = withDefaults(defineProps<Props>(), {
    initial_rating: 0,
    initial_body: '',
    is_editing: false,
    is_submitting: false,
    max_len: 1000,
    show_rating: true,
    submit_label: undefined,
    title: undefined,
    reply_to_name: undefined,
})

const emit = defineEmits<{
    submit: [payload: { rating: number | null, body: string }]
    cancel: []
    'clear-reply-to': []
}>()

const default_submit_label = computed(() => {
    if (props.submit_label) return props.submit_label

    return props.is_editing ? 'Update Review' : 'Submit Review'
})

const EMOJIS = [
    '📚', '📖', '📕', '⭐', '❤️', '🔖',
    '👍', '👎', '🙌', '👏', '🤝', '💬',
    '😊', '😍', '🥰', '🤔', '😮', '😢',
    '🔥', '💯', '✨', '💡', '🎉', '☕',
]

const editor = ref<HTMLDivElement | null>(null)
const emoji_btn = ref<HTMLButtonElement | null>(null)
const rating = ref(props.initial_rating)
const show_emoji = ref(false)
const char_count = ref(0)
const error = ref('')

function exec(command: string) {
    editor.value?.focus()
    document.execCommand('styleWithCSS', false, 'false')
    document.execCommand(command, false)
}

function insert_emoji(emoji: string) {
    editor.value?.focus()
    document.execCommand('insertText', false, emoji)
    show_emoji.value = false
    update_count()
}

function update_count() {
    const text = editor.value?.textContent?.trim() ?? ''
    char_count.value = text.length
    error.value = char_count.value > props.max_len ? `Maximum ${props.max_len} characters` : ''
}

function on_keydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        e.preventDefault()
        document.execCommand('insertLineBreak')
        update_count()
    }
}

function submit() {
    const text = editor.value?.textContent?.trim() ?? ''
    const body = editor.value?.innerHTML?.trim() ?? ''

    if (props.show_rating && rating.value === 0) {
        error.value = 'Please select a rating'
        return
    }
    if (text.length < 3) {
        error.value = 'Review must be at least 3 characters'
        return
    }
    if (text.length > props.max_len) {
        error.value = `Maximum ${props.max_len} characters`
        return
    }

    error.value = ''
    emit('submit', { rating: props.show_rating ? rating.value : null, body })
}

function on_click_outside(e: MouseEvent) {
    if (show_emoji.value && !emoji_btn.value?.contains(e.target as Node)) {
        show_emoji.value = false
    }
}

onMounted(() => {
    if (editor.value) editor.value.innerHTML = props.initial_body
    update_count()
    document.addEventListener('click', on_click_outside)
})

onBeforeUnmount(() => document.removeEventListener('click', on_click_outside))
</script>

<style lang="scss" scoped>
.review-form {
    padding: 24px;
    background: $color-lightest;
    border-radius: 12px;

    &__title {
        font-size: 17px;
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 18px;
    }

    &__reply-target {
        display: flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        max-width: 100%;
        padding: 6px 8px 6px 12px;
        margin-bottom: 14px;
        background: rgba($color-primary, 0.08);
        border-radius: 999px;
        font-size: 13px;
        color: $color-dark;

        strong {
            color: $color-primary;
        }
    }

    &__reply-target-clear {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 14px;
        line-height: 1;
        color: $color-gray;
        cursor: pointer;

        &:hover {
            background: rgba($color-dark, 0.08);
            color: $color-dark;
        }
    }

    &__rating {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;

        label {
            font-size: 14px;
            font-weight: 600;
            color: $color-dark;
        }
    }

    &__stars {
        display: flex;
        gap: 4px;
    }

    &__star-btn {
        font-size: 24px;
        color: $color-light;
        cursor: pointer;
        transition: color 0.15s;
        font-family: inherit;

        &--filled {
            color: #f5a623;
        }

        &:hover {
            color: #f5a623;
        }
    }

    &__field {
        max-width: 560px;
        border: 1.5px solid $color-light;
        border-radius: 6px;
        background: $color-white;
        transition: border-color 0.2s;

        &:focus-within {
            border-color: $color-primary;
        }
    }

    &__editor-wrap {
        position: relative;
    }

    &__editor {
        min-height: 90px;
        max-height: 260px;
        overflow-y: auto;
        padding: 11px 44px 11px 14px;
        font-size: 14px;
        line-height: 1.6;
        color: $color-dark;
        outline: none;

        &:empty::before {
            content: attr(data-placeholder);
            color: $color-gray;
        }

        :deep(b),
        :deep(strong) {
            font-weight: 700;
        }

        :deep(i),
        :deep(em) {
            font-style: italic;
        }

        :deep(u) {
            text-decoration: underline;
        }

        :deep(s) {
            text-decoration: line-through;
        }
    }

    &__emoji-btn {
        position: absolute;
        top: 8px;
        right: 10px;
        font-size: 16px;
        opacity: 0.6;
        cursor: pointer;
        transition: opacity 0.2s;

        &:hover {
            opacity: 1;
        }
    }

    &__emoji-picker {
        position: absolute;
        top: 38px;
        right: 6px;
        z-index: 20;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 2px;
        padding: 8px;
        background: $color-white;
        border: 1px solid $color-light;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        max-width: calc(100% - 12px);
    }

    &__emoji-item {
        font-size: 18px;
        line-height: 1;
        padding: 5px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.15s;

        &:hover {
            background: $color-lightest;
        }
    }

    &__toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 10px;
        border-top: 1.5px solid $color-light;
        border-radius: 0 0 5px 5px;
        background: $color-lightest;
    }

    &__tools {
        display: flex;
        gap: 4px;
    }

    &__tool {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 14px;
        color: $color-dark;
        cursor: pointer;
        transition: background 0.15s;

        &:hover {
            background: $color-light;
        }
    }

    &__counter {
        font-size: 12px;
        color: $color-gray;

        &--warn {
            color: #f5a623;
        }

        &--over {
            color: $color-danger;
            font-weight: 600;
        }
    }

    &__error {
        margin-top: 10px;
        font-size: 13px;
        color: $color-danger;
    }

    &__actions {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-top: 16px;
    }
}
</style>
