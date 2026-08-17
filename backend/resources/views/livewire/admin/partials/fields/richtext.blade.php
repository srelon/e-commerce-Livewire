@php
    $field_name = $field['name'];
@endphp

<div
    x-data="richTextEditor('{{ $field_name }}', @js($field['value'] ?? ''), {{ $disabled ? 'true' : 'false' }})"
    x-on:richtext-image-inserted.window="if ($event.detail.field === '{{ $field_name }}') insertImage($event.detail.url)"
    wire:ignore.self
>
    <flux:field>
        <flux:label x-on:click="focusEditor()">{{ $field['label'] }}</flux:label>

        <div wire:ignore class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-white/10 dark:bg-white/10">
            @unless ($disabled)
                <div class="flex flex-wrap items-center gap-1 border-b border-zinc-200 bg-zinc-50 p-1.5 dark:border-white/10 dark:bg-white/5">
                    <button type="button" class="richtext-tool" title="Undo" x-on:click="undo()">
                        <x-heroicon-o-arrow-uturn-left class="w-4" />
                    </button>
                    <button type="button" class="richtext-tool" title="Redo" x-on:click="redo()">
                        <x-heroicon-o-arrow-uturn-right class="w-4" />
                    </button>

                    <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-white/10"></div>

                    <button type="button" class="richtext-tool" :class="active.bold && 'is-active'" title="Bold" x-on:click="toggleBold()"><b>B</b></button>
                    <button type="button" class="richtext-tool italic" :class="active.italic && 'is-active'" title="Italic" x-on:click="toggleItalic()"><i>I</i></button>
                    <button type="button" class="richtext-tool underline" :class="active.underline && 'is-active'" title="Underline" x-on:click="toggleUnderline()"><u>U</u></button>

                    <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-white/10"></div>

                    <button type="button" class="richtext-tool" :class="active.heading2 && 'is-active'" title="Heading 2" x-on:click="toggleHeading(2)">H2</button>
                    <button type="button" class="richtext-tool" :class="active.heading3 && 'is-active'" title="Heading 3" x-on:click="toggleHeading(3)">H3</button>

                    <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-white/10"></div>

                    <button type="button" class="richtext-tool" :class="active.bulletList && 'is-active'" title="Bullet list" x-on:click="toggleBulletList()">
                        <x-heroicon-o-list-bullet class="w-4" />
                    </button>
                    <button type="button" class="richtext-tool" :class="active.orderedList && 'is-active'" title="Numbered list" x-on:click="toggleOrderedList()">
                        <x-heroicon-o-numbered-list class="w-4" />
                    </button>
                    <button type="button" class="richtext-tool" :class="active.blockquote && 'is-active'" title="Quote" x-on:click="toggleBlockquote()">
                        <x-heroicon-o-chat-bubble-bottom-center-text class="w-4" />
                    </button>

                    <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-white/10"></div>

                    <button type="button" class="richtext-tool" :class="active.link && 'is-active'" title="Link" x-on:click="addLink()">
                        <x-heroicon-o-link class="w-4" />
                    </button>
                    <button type="button" class="richtext-tool" title="Image" x-on:click="openImagePicker()">
                        <x-heroicon-o-photo class="w-4" />
                    </button>
                </div>
            @endunless

            <div x-ref="editor" class="richtext-content min-h-40 text-sm text-zinc-700 dark:text-zinc-300"></div>
        </div>

        <flux:error name="{{ $field_name }}" />
    </flux:field>

    <div wire:ignore>
        <textarea wire:model="{{ $field_name }}" x-ref="hidden" class="hidden"></textarea>
        <input type="file" accept="image/*" x-ref="image_input" wire:model="{{ $field_name }}_image" class="hidden">
    </div>
</div>
