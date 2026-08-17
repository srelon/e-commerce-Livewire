import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Placeholder from '@tiptap/extension-placeholder'

const EMOJIS = [
    '📚', '📖', '📕', '⭐', '❤️', '🔖',
    '👍', '👎', '🙌', '👏', '🤝', '💬',
    '😊', '😍', '🥰', '🤔', '😮', '😢',
    '🔥', '💯', '✨', '💡', '🎉', '☕',
]

export default function richTextEditor(field_name, initial_value, disabled, placeholder = 'Start writing...') {
    // Kept outside the returned object on purpose: Alpine wraps everything returned
    // from an x-data factory in a reactive Proxy, and ProseMirror's transaction
    // handling does strict identity checks internally (`RangeError: Applying a
    // mismatched transaction`) that break once the Editor instance itself is
    // silently Proxy-wrapped. A plain closure variable never goes through Alpine's
    // reactivity at all.
    let editor = null

    return {
        field_name,
        active: {},
        emojis: EMOJIS,
        show_emoji: false,
        emoji_picker_style: '',

        init() {
            editor = new Editor({
                element: this.$refs.editor,
                editable: ! disabled,
                content: initial_value,
                extensions: [
                    StarterKit.configure({
                        heading: {
                            levels: [2, 3],
                        },
                        link: {
                            openOnClick: false,
                            HTMLAttributes: {
                                rel: 'noopener noreferrer nofollow',
                                target: '_blank',
                            },
                        },
                    }),
                    Image.configure({
                        inline: false,
                        allowBase64: false,
                    }),
                    Placeholder.configure({
                        placeholder,
                    }),
                ],
                onUpdate: () => this.sync(),
                onTransaction: () => this.updateActiveState(),
            })

            this.$refs.hidden.value = initial_value
            this.updateActiveState()
        },

        updateActiveState() {
            this.active = {
                bold: editor.isActive('bold'),
                italic: editor.isActive('italic'),
                underline: editor.isActive('underline'),
                strike: editor.isActive('strike'),
                heading2: editor.isActive('heading', { level: 2 }),
                heading3: editor.isActive('heading', { level: 3 }),
                bulletList: editor.isActive('bulletList'),
                orderedList: editor.isActive('orderedList'),
                blockquote: editor.isActive('blockquote'),
                link: editor.isActive('link'),
            }
        },

        focusEditor() {
            editor.chain().focus().run()
        },

        toggleBold() {
            editor.chain().focus().toggleBold().run()
        },

        toggleItalic() {
            editor.chain().focus().toggleItalic().run()
        },

        toggleUnderline() {
            editor.chain().focus().toggleUnderline().run()
        },

        toggleStrike() {
            editor.chain().focus().toggleStrike().run()
        },

        toggleEmojiPicker(button_el) {
            this.show_emoji = ! this.show_emoji

            if (this.show_emoji) {
                const rect = button_el.getBoundingClientRect()
                const top = rect.bottom + window.scrollY + 4
                const left = rect.left + window.scrollX
                this.emoji_picker_style = `top:${top}px; left:${left}px;`
            }
        },

        insertEmoji(emoji) {
            editor.chain().focus().insertContent(emoji).run()
            this.show_emoji = false
        },

        toggleHeading(level) {
            editor.chain().focus().toggleHeading({ level }).run()
        },

        toggleBulletList() {
            editor.chain().focus().toggleBulletList().run()
        },

        toggleOrderedList() {
            editor.chain().focus().toggleOrderedList().run()
        },

        toggleBlockquote() {
            editor.chain().focus().toggleBlockquote().run()
        },

        undo() {
            editor.chain().focus().undo().run()
        },

        redo() {
            editor.chain().focus().redo().run()
        },

        addLink() {
            const previous_url = editor.getAttributes('link').href
            let url = window.prompt('Link URL (must start with http://, https://, / or #)', previous_url ?? '')

            if (url === null) return

            url = url.trim()

            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run()
                return
            }

            // Kept in sync with News\Form::sanitizeContent()'s own href allowlist on the
            // backend — rejecting here means the user finds out immediately instead of
            // saving and getting back a silently stripped, href-less <a> tag.
            if (! /^(https?:\/\/|\/|#)/i.test(url)) {
                window.alert('Enter a valid URL starting with http://, https://, / or #')
                return
            }

            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
        },

        openImagePicker() {
            this.$refs.image_input.click()
        },

        insertImage(url) {
            editor.chain().focus().setImage({ src: url }).run()
        },

        sync() {
            const html = editor.getHTML()
            this.$refs.hidden.value = html
            this.$refs.hidden.dispatchEvent(new Event('input'))
        },
    }
}
