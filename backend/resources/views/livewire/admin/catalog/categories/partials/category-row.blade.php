<div class="flex items-center justify-between rounded-lg border border-base-300 bg-base-200 px-4 py-2.5 shadow-sm select-none" x-sort:handle>
    <div class="flex items-center gap-3">
        <span class="cursor-move text-zinc-400">⠿</span>

        <template x-if="node.image_url">
            <img :src="node.image_url" alt="" class="h-10 w-10 rounded object-cover">
        </template>

        <div class="flex items-center gap-2 font-medium">
            <span x-text="node.name"></span>
            <flux:badge x-show="! node.status" color="zinc" size="sm">Inactive</flux:badge>
        </div>
    </div>

    <div class="flex items-center gap-3" x-sort:ignore>
        <span class="text-xs text-zinc-500" x-text="node.products_count + ' products'"></span>

        <button type="button" class="btn btn-sm" title="Edit" x-on:click="$wire.openEdit(node.id)">
            <x-heroicon-o-pencil-square class="w-5 text-blue-600 dark:text-blue-400" />
        </button>

        @if ($can_edit)
            <button
                type="button"
                class="btn btn-sm"
                title="Delete"
                x-on:click="$wire.dispatch('confirm-action', {
                    event: 'deleteCategory',
                    params: { categoryId: node.id },
                    message: 'Delete this category?',
                    heading: 'Confirm deletion',
                    label: 'Delete',
                    variant: 'danger',
                })"
            >
                <x-heroicon-o-trash class="w-5 text-red-600 dark:text-red-400" />
            </button>
        @endif
    </div>
</div>
