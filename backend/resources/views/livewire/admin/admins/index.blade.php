<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Admins</flux:heading>

        @if ($this->hasAccess('edit'))
            <flux:button variant="primary" :href="route('admin.admins.create')" wire:navigate>
                New admin
            </flux:button>
        @endif
    </div>

    <div class="mt-6">
        <livewire:components.admins.table />
    </div>

    @include('livewire.admin.partials.confirm-modal')
</div>
