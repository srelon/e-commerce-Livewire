<flux:field>
    <flux:label>{{ $field['label'] }}</flux:label>
    <flux:input
        type="{{ $field['input_type'] ?? 'text' }}"
        wire:model="{{ $field['name'] }}"
        :disabled="$disabled"
        placeholder="{{ $field['placeholder'] ?? null }}"
    />
    <flux:error name="{{ $field['name'] }}" />
</flux:field>
