<div class="max-w-2xl">
    <flux:heading size="xl">{{ $role ? 'Edit role' : 'New role' }}</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-6">
        <div class="space-y-4 rounded-lg border border-base-300 bg-base-200 p-6 shadow-sm">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" :disabled="! $this->hasAccess('edit')" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Label</flux:label>
                <flux:input wire:model="label" :disabled="! $this->hasAccess('edit')" />
                <flux:error name="label" />
            </flux:field>
        </div>

        @include('livewire.admin.roles.partials.permission-matrix', ['accesses' => $this->accesses])

        <div class="flex gap-3 pt-2">
            @if ($this->hasAccess('edit'))
                <flux:button type="submit" variant="primary">Save</flux:button>
            @endif

            <flux:button variant="ghost" :href="route('admin.roles.index')" wire:navigate>Cancel</flux:button>
        </div>
    </form>
</div>
