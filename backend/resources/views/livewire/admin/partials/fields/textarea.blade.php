<flux:field>
    <flux:label>{{ $field['label'] }}</flux:label>
    <flux:textarea
        wire:model="{{ $field['name'] }}"
        :disabled="$disabled"
        :placeholder="$field['placeholder'] ?? null"
        rows="{{ $field['rows'] ?? 4 }}"
    />
    <flux:error name="{{ $field['name'] }}" />
</flux:field>
