<flux:field>
    <flux:label>{{ $field['label'] }}</flux:label>
    <flux:select wire:model="{{ $field['name'] }}" :disabled="$disabled">
        @foreach ($field['options'] as $option)
            <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
        @endforeach
    </flux:select>
    <flux:error name="{{ $field['name'] }}" />
</flux:field>
