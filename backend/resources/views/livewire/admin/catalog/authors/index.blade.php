<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Authors</flux:heading>

        @if ($this->hasAccess('edit'))
            <flux:button variant="primary" wire:click="openCreate">
                New author
            </flux:button>
        @endif
    </div>

    <div class="mt-6">
        <livewire:components.catalog.authors.table />
    </div>

    <x-admin.modal-form
        name="author-form"
        :title="$editing_id ? 'Edit author' : 'New author'"
        :fields="$this->schema()"
        :disabled="! $this->hasAccess('edit')"
    />

    @include('livewire.admin.partials.confirm-modal')
</div>
