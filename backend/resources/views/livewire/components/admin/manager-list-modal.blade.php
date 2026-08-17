@props([
    'heading',
    'createLabel' => null,
    'formTitle',
])

<div>
    <flux:modal wire:model.self="show_list_modal" :name="$this->modalListName()" class="max-w-2xl">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ $heading }}</flux:heading>

                @if ($createLabel && $this->hasAccess('edit'))
                    <flux:button variant="primary" wire:click="openCreate">{{ $createLabel }}</flux:button>
                @endif
            </div>

            <div class="overflow-x-auto">
                {{ $slot }}
            </div>

            <div class="flex justify-end pt-2">
                <flux:button variant="ghost" wire:click="closeListModal">Close</flux:button>
            </div>
        </div>
    </flux:modal>

    <x-admin.modal-form
        :name="$this->modalFormName()"
        :title="$formTitle"
        :fields="$this->schema()"
        :disabled="! $this->hasAccess('edit')"
        model="show_form_modal"
    />

    @include('livewire.admin.partials.confirm-modal', ['name' => $this->confirmModalName()])
</div>
