<div
    x-data="{ new_preview_url: null }"
    x-on:admin-form-reset.window="new_preview_url = null"
    wire:ignore.self
    class="flex items-center gap-4"
>
    @if (! empty($field['preview']))
        <button type="button" class="shrink-0 cursor-pointer" x-show="! new_preview_url" x-on:click="$dispatch('open-lightbox', { src: @js($field['preview']) })">
            <img src="{{ $field['preview'] }}" alt="" class="{{ $field['preview_class'] ?? 'h-16 w-16 rounded object-cover' }}">
        </button>
    @endif

    <button type="button" class="shrink-0 cursor-pointer" x-show="new_preview_url" x-on:click="$dispatch('open-lightbox', { src: new_preview_url })">
        <img :src="new_preview_url" alt="" class="{{ $field['preview_class'] ?? 'h-16 w-16 rounded object-cover' }}">
    </button>

    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-zinc-800 dark:text-white">{{ $field['label'] }}</label>
        <input
            type="file"
            wire:model="{{ $field['name'] }}"
            x-on:change="new_preview_url = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : new_preview_url"
            @disabled($disabled)
            class="block w-full text-sm text-zinc-600 dark:text-zinc-300 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm dark:file:bg-zinc-700"
        >
        @error($field['name']) <flux:text class="mt-1 text-red-500">{{ $message }}</flux:text> @enderror
    </div>
</div>
