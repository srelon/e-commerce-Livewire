<div class="flex items-center justify-between rounded-lg border border-base-300 bg-base-200 px-4 {{ $root ? 'py-2.5' : 'py-2' }} shadow-sm select-none" x-sort:handle>
    <div class="flex items-center gap-2 cursor-move">
        <span class="text-zinc-400">⠿</span>
        <span x-text="{{ $var }}.name" @if ($root) class="font-medium" @endif></span>
    </div>

    <div class="flex gap-1" x-sort:ignore>
        <flux:button size="sm" variant="ghost" x-on:click="$wire.openEdit({{ $var }}.id)">Edit</flux:button>

        @if ($can_edit)
            @if ($root)
                <flux:button size="sm" variant="ghost" x-on:click="$wire.openCreate({{ $var }}.id)">+ Child</flux:button>
            @endif
            <flux:button
                size="sm"
                variant="ghost"
                class="text-red-600"
                x-on:click="$wire.dispatch('confirm-action', {
                    event: 'deleteMenuItem',
                    params: { id: {{ $var }}.id },
                    message: 'Delete this menu item?',
                    heading: 'Confirm deletion',
                    label: 'Delete',
                    variant: 'danger',
                })"
            >Delete</flux:button>
        @endif
    </div>
</div>
