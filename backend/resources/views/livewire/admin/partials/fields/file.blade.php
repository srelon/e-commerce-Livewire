<div class="flex items-center gap-4 rounded-lg border border-base-300 bg-base-200 p-6 shadow-sm">
    @if (! empty($field['preview']))
        <x-lightbox-trigger :src="$field['preview']">
            <img src="{{ $field['preview'] }}" alt="" class="{{ $field['preview_class'] ?? 'h-16 w-16 rounded object-cover' }} shrink-0">
        </x-lightbox-trigger>
    @endif

    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-zinc-800 dark:text-white">{{ $field['label'] }}</label>
        <input
            type="file"
            wire:model="{{ $field['name'] }}"
            @disabled($disabled)
            class="block w-full text-sm text-zinc-600 dark:text-zinc-300 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm dark:file:bg-zinc-700"
        >
        @error($field['name']) <flux:text class="mt-1 text-red-500">{{ $message }}</flux:text> @enderror
    </div>
</div>
