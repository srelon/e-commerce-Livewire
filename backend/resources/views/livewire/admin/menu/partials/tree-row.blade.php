<div class="flex items-center justify-between rounded-lg border border-base-300 bg-base-200 px-4 {{ $root ? 'py-2.5' : 'py-2' }} shadow-sm select-none" x-sort:handle>
    <div class="flex items-center gap-2 cursor-move">
        <span class="text-zinc-400">⠿</span>
        <span x-text="{{ $var }}.name" @if ($root) class="font-medium" @endif></span>
    </div>

    <div class="flex items-center gap-1" x-sort:ignore>
        <button type="button" class="btn btn-sm" title="Edit" x-on:click="$wire.openEdit({{ $var }}.id)">
            <x-heroicon-o-pencil-square class="w-5 text-blue-600 dark:text-blue-400" />
        </button>

        @if ($can_edit)
            @if ($root)
                <button type="button" class="btn btn-sm" title="Add child" x-on:click="$wire.openCreate({{ $var }}.id)">
                    <x-heroicon-o-plus-circle class="w-5 text-emerald-600 dark:text-emerald-400" />
                </button>
            @endif
            <button
                type="button"
                class="btn btn-sm"
                title="Delete"
                x-on:click="$wire.dispatch('confirm-action', {
                    event: 'deleteMenuItem',
                    params: { id: {{ $var }}.id },
                    message: 'Delete this menu item?',
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
