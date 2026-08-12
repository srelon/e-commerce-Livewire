@php
    $can_edit = $this->hasAccess('edit');
@endphp

<div
    wire:key="access-list-{{ $accesses->count() }}"
    x-data="{
        selected: $wire.entangle('selected_accesses').live,

        isChecked(access_id, type) {
            return (this.selected ?? []).includes(access_id + ':' + type)
        },

        toggle(access_id, type) {
            const view_key = access_id + ':1'
            const edit_key = access_id + ':2'
            const key = access_id + ':' + type

            if (this.isChecked(access_id, type)) {
                this.selected = this.selected.filter((s) => s !== key)
                if (type == 1) {
                    this.selected = this.selected.filter((s) => s !== edit_key)
                }
            } else {
                this.selected = [...(this.selected ?? []), key]
                if (type == 2 && ! this.isChecked(access_id, 1)) {
                    this.selected = [...this.selected, view_key]
                }
            }
        },
    }"
    class="overflow-hidden rounded-lg border border-base-300 bg-base-200 shadow-sm"
>
    <div class="flex items-center justify-between border-b border-base-300 bg-base-100 px-4 py-3">
        <flux:heading size="sm">Accesses</flux:heading>

        @if ($can_edit)
            <flux:button size="sm" variant="primary" wire:click="$set('show_create_access', true)">Add Access</flux:button>
        @endif
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-base-300 text-left">
                <th class="w-20 px-4 py-2 text-center font-medium">View</th>
                <th class="w-20 px-4 py-2 text-center font-medium">Edit</th>
                <th class="px-4 py-2 font-medium">Resource</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($accesses as $access)
                <tr class="border-b border-base-300/50 last:border-0">
                    <td class="px-4 py-2 text-center">
                        <input
                            type="checkbox"
                            :checked="isChecked({{ $access->id }}, 1)"
                            @change="toggle({{ $access->id }}, 1)"
                            @disabled(! $can_edit)
                            class="size-4 rounded border-zinc-300"
                        />
                    </td>
                    <td class="px-4 py-2 text-center">
                        <input
                            type="checkbox"
                            :checked="isChecked({{ $access->id }}, 2)"
                            @change="toggle({{ $access->id }}, 2)"
                            @disabled(! $can_edit)
                            class="size-4 rounded border-zinc-300"
                        />
                    </td>
                    <td class="px-4 py-2">{{ $access->title }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-3 text-center text-zinc-500">No accesses defined yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<flux:modal wire:model.self="show_create_access" name="create-access" class="max-w-sm">
    <div class="space-y-4">
        <flux:heading size="lg">Add Access</flux:heading>

        <flux:field>
            <flux:label>Key</flux:label>
            <flux:input wire:model="new_access_key" placeholder="e.g. orders" />
            <flux:error name="new_access_key" />
        </flux:field>

        <flux:field>
            <flux:label>Title</flux:label>
            <flux:input wire:model="new_access_title" placeholder="e.g. Orders" />
            <flux:error name="new_access_title" />
        </flux:field>

        <div class="flex justify-end gap-3">
            <flux:button variant="ghost" wire:click="$set('show_create_access', false)">Cancel</flux:button>
            <flux:button variant="primary" wire:click="createAccess">Add</flux:button>
        </div>
    </div>
</flux:modal>
