<flux:field>
    <flux:label>
        {{ $field['label'] }}

        @if (! empty($field['manage_button']))
            <x-slot:trailing>
                <button
                    type="button"
                    class="btn btn-sm"
                    title="{{ $field['manage_button']['label'] }}"
                    x-on:click.stop="$wire.dispatch('{{ $field['manage_button']['dispatch'] }}')"
                >
                    <x-heroicon-o-pencil-square class="w-4 text-blue-600 dark:text-blue-400" />
                </button>
            </x-slot:trailing>
        @endif
    </flux:label>
    @if (! empty($field['live']))
        <flux:select wire:model.live="{{ $field['name'] }}" :disabled="$disabled">
            @foreach ($field['options'] as $option)
                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
    @else
        <flux:select wire:model="{{ $field['name'] }}" :disabled="$disabled">
            @foreach ($field['options'] as $option)
                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
    @endif
    <flux:error name="{{ $field['name'] }}" />
</flux:field>
