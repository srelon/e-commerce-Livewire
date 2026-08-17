@props([
    'name',
    'title',
    'fields',
    'disabled' => false,
    'model' => 'show_modal',
    'saveAction' => 'save',
    'cancelAction' => 'closeModal',
    'class' => 'max-w-md',
])

<flux:modal wire:model.self="{{ $model }}" :name="$name" class="{{ $class }}">
    <div
        class="space-y-4"
        @unless ($disabled)
            x-on:keydown.enter="if ($event.target.tagName !== 'TEXTAREA') { $event.preventDefault(); $wire.{{ $saveAction }}() }"
        @endunless
    >
        <flux:heading size="lg">{{ $title }}</flux:heading>

        <x-admin.form-fields :fields="$fields" :disabled="$disabled">
            {{ $slot }}
        </x-admin.form-fields>

        <div class="flex justify-end gap-3 pt-2">
            <flux:button variant="ghost" wire:click="{{ $cancelAction }}">Cancel</flux:button>

            @unless ($disabled)
                <flux:button variant="primary" wire:click="{{ $saveAction }}">Save</flux:button>
            @endunless
        </div>
    </div>
</flux:modal>
