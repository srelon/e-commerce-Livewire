@php
    $field_name = $field['name'];
    $buttons = $field['buttons'] ?? ['undo', 'redo', 'bold', 'italic', 'underline', 'heading2', 'heading3', 'bulletList', 'orderedList', 'blockquote', 'link', 'image'];
    $emoji = $field['emoji'] ?? false;
    $button_groups = [
        'undo' => 'history', 'redo' => 'history',
        'bold' => 'marks', 'italic' => 'marks', 'underline' => 'marks', 'strike' => 'marks',
        'heading2' => 'headings', 'heading3' => 'headings',
        'bulletList' => 'lists', 'orderedList' => 'lists', 'blockquote' => 'lists',
        'link' => 'media', 'image' => 'media',
    ];
    $last_group = null;
@endphp

<div
    x-data="richTextEditor('{{ $field_name }}', @js($field['value'] ?? ''), {{ $disabled ? 'true' : 'false' }}, @js($field['placeholder'] ?? 'Start writing...'))"
    x-on:richtext-image-inserted.window="if ($event.detail.field === '{{ $field_name }}') insertImage($event.detail.url)"
    wire:ignore.self
>
    <flux:field>
        @if (! empty($field['label']))
            <flux:label x-on:click="focusEditor()">{{ $field['label'] }}</flux:label>
        @endif

        <div wire:ignore class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-white/10 dark:bg-white/10">
            @unless ($disabled)
                <div class="flex flex-wrap items-center gap-1 border-b border-zinc-200 bg-zinc-50 p-1.5 dark:border-white/10 dark:bg-white/5">
                    @foreach ($buttons as $button)
                        @if ($last_group !== null && $button_groups[$button] !== $last_group)
                            <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-white/10"></div>
                        @endif
                        @php $last_group = $button_groups[$button]; @endphp

                        @switch($button)
                            @case('undo')
                                <button type="button" class="richtext-tool" title="Undo" x-on:mousedown.prevent="undo()">
                                    <x-heroicon-o-arrow-uturn-left class="w-4" />
                                </button>
                                @break
                            @case('redo')
                                <button type="button" class="richtext-tool" title="Redo" x-on:mousedown.prevent="redo()">
                                    <x-heroicon-o-arrow-uturn-right class="w-4" />
                                </button>
                                @break
                            @case('bold')
                                <button type="button" class="richtext-tool" :class="active.bold && 'is-active'" title="Bold" x-on:mousedown.prevent="toggleBold()"><b>B</b></button>
                                @break
                            @case('italic')
                                <button type="button" class="richtext-tool italic" :class="active.italic && 'is-active'" title="Italic" x-on:mousedown.prevent="toggleItalic()"><i>I</i></button>
                                @break
                            @case('underline')
                                <button type="button" class="richtext-tool underline" :class="active.underline && 'is-active'" title="Underline" x-on:mousedown.prevent="toggleUnderline()"><u>U</u></button>
                                @break
                            @case('strike')
                                <button type="button" class="richtext-tool line-through" :class="active.strike && 'is-active'" title="Strikethrough" x-on:mousedown.prevent="toggleStrike()"><s>S</s></button>
                                @break
                            @case('heading2')
                                <button type="button" class="richtext-tool" :class="active.heading2 && 'is-active'" title="Heading 2" x-on:mousedown.prevent="toggleHeading(2)">H2</button>
                                @break
                            @case('heading3')
                                <button type="button" class="richtext-tool" :class="active.heading3 && 'is-active'" title="Heading 3" x-on:mousedown.prevent="toggleHeading(3)">H3</button>
                                @break
                            @case('bulletList')
                                <button type="button" class="richtext-tool" :class="active.bulletList && 'is-active'" title="Bullet list" x-on:mousedown.prevent="toggleBulletList()">
                                    <x-heroicon-o-list-bullet class="w-4" />
                                </button>
                                @break
                            @case('orderedList')
                                <button type="button" class="richtext-tool" :class="active.orderedList && 'is-active'" title="Numbered list" x-on:mousedown.prevent="toggleOrderedList()">
                                    <x-heroicon-o-numbered-list class="w-4" />
                                </button>
                                @break
                            @case('blockquote')
                                <button type="button" class="richtext-tool" :class="active.blockquote && 'is-active'" title="Quote" x-on:mousedown.prevent="toggleBlockquote()">
                                    <x-heroicon-o-chat-bubble-bottom-center-text class="w-4" />
                                </button>
                                @break
                            @case('link')
                                <button type="button" class="richtext-tool" :class="active.link && 'is-active'" title="Link" x-on:mousedown.prevent="addLink()">
                                    <x-heroicon-o-link class="w-4" />
                                </button>
                                @break
                            @case('image')
                                <button type="button" class="richtext-tool" title="Image" x-on:mousedown.prevent="openImagePicker()">
                                    <x-heroicon-o-photo class="w-4" />
                                </button>
                                @break
                        @endswitch
                    @endforeach

                    @if ($emoji)
                        @if ($last_group !== null)
                            <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-white/10"></div>
                        @endif

                        <button
                            type="button"
                            class="richtext-tool"
                            title="Emoji"
                            x-on:mousedown.prevent
                            x-on:click.stop="toggleEmojiPicker($el)"
                        >🙂</button>

                        <template x-teleport="body">
                            <div
                                x-show="show_emoji"
                                x-cloak
                                x-on:click.outside="show_emoji = false"
                                :style="emoji_picker_style"
                                class="absolute z-[5] grid grid-cols-6 gap-1 rounded-lg border border-zinc-200 bg-white p-2 shadow-lg dark:border-white/10 dark:bg-zinc-800"
                            >
                                <template x-for="item in emojis" :key="item">
                                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded text-lg hover:bg-zinc-100 dark:hover:bg-white/10" x-on:mousedown.prevent="insertEmoji(item)" x-text="item"></button>
                                </template>
                            </div>
                        </template>
                    @endif
                </div>
            @endunless

            <div x-ref="editor" class="richtext-content {{ $field['min_height'] ?? 'min-h-40' }} text-sm text-zinc-700 dark:text-zinc-300"></div>
        </div>

        <flux:error name="{{ $field_name }}" />
    </flux:field>

    <div wire:ignore>
        <textarea wire:model="{{ $field_name }}" x-ref="hidden" class="hidden"></textarea>
        @if (in_array('image', $buttons))
            <input type="file" accept="image/*" x-ref="image_input" wire:model="{{ $field_name }}_image" class="hidden">
        @endif
    </div>
</div>
