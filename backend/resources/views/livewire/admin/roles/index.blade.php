<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Roles</flux:heading>

        @if ($this->hasAccess('edit'))
            <flux:button variant="primary" :href="route('admin.roles.create')" wire:navigate>
                New role
            </flux:button>
        @endif
    </div>

    <div class="mt-6">
        <livewire:components.roles.table />
    </div>

    @include('livewire.admin.partials.confirm-modal')
</div>
