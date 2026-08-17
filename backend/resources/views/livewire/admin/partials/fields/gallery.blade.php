@php
    $field_name = $field['name'];
@endphp

<div
    x-data="sortableTree(@js($field['images']))"
    x-on:gallery-updated.window="if ($event.detail.field === '{{ $field_name }}') { tree = $event.detail.images; original = JSON.parse(JSON.stringify($event.detail.images)); is_dirty = false }"
    wire:ignore.self
>
    <label class="mb-1.5 block text-sm font-medium text-zinc-800 dark:text-white">{{ $field['label'] }}</label>

    <ul
        x-sort.ghost="onDrop($item, $position)"
        x-sort:config="{ forceFallback: true, onChoose: pauseObserving, onUnchoose: resumeObserving }"
        class="flex flex-wrap gap-3"
    >
        <template x-for="node in tree" :key="node.id">
            <li x-sort:item="node.id" class="group relative h-24 w-24 shrink-0 cursor-move overflow-hidden rounded-lg border border-base-300" x-sort:handle>
                <img :src="node.url" alt="" class="h-full w-full object-cover">

                @unless ($disabled)
                    <button
                        type="button"
                        class="absolute right-1 top-1 rounded-full bg-red-600 p-1 text-white opacity-0 shadow group-hover:opacity-100"
                        title="Delete image"
                        x-sort:ignore
                        x-on:click.stop="$wire.dispatchSelf('confirm-action', {
                            event: 'deleteGalleryImage',
                            params: { imageId: node.id },
                            message: 'Delete this image?',
                            heading: 'Confirm deletion',
                            label: 'Delete',
                            variant: 'danger',
                        })"
                    >
                        <x-heroicon-o-x-mark class="h-3 w-3" />
                    </button>
                @endunless
            </li>
        </template>
    </ul>

    <template x-if="tree.length === 0">
        <flux:text class="mt-2 text-zinc-500">No images yet.</flux:text>
    </template>

    @unless ($disabled)
        <div class="mt-3 flex gap-3" x-show="is_dirty">
            <flux:button size="sm" variant="primary" x-on:click="commitOrder">Save order</flux:button>
            <flux:button size="sm" variant="ghost" x-on:click="cancelOrder">Cancel</flux:button>
        </div>

        <div class="mt-3">
            <input
                type="file"
                multiple
                wire:model="{{ $field_name }}"
                class="block w-full text-sm text-zinc-600 dark:text-zinc-300 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm dark:file:bg-zinc-700"
            >
            @error($field_name . '.*') <flux:text class="mt-1 text-red-500">{{ $message }}</flux:text> @enderror
        </div>
    @endunless
</div>
