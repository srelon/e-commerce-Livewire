@props(['fields', 'disabled' => false])

<div class="grid grid-cols-1 gap-4 rounded-lg border border-base-300 bg-base-200 p-6 shadow-sm sm:grid-cols-2">
    @foreach ($fields as $field)
        @php
            $full_width = $field['full_width'] ?? $field['type'] === 'file';
        @endphp

        <div @class(['sm:col-span-2' => $full_width])>
            @include('livewire.admin.partials.fields.' . $field['type'], [
                'field' => $field,
                'disabled' => $field['disabled'] ?? $disabled,
            ])
        </div>
    @endforeach

    @if ($slot->isNotEmpty())
        <div class="sm:col-span-2">
            {{ $slot }}
        </div>
    @endif
</div>
