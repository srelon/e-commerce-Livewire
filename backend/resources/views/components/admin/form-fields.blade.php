@props(['fields', 'disabled' => false])

@php
    $full_fields = collect($fields)->filter(fn ($field) => $field['full_width'] ?? $field['type'] === 'file')->values();
    $grid_fields = collect($fields)->reject(fn ($field) => $field['full_width'] ?? $field['type'] === 'file')->values();
@endphp

@foreach ($full_fields as $field)
    @include('livewire.admin.partials.fields.' . $field['type'], [
        'field' => $field,
        'disabled' => $field['disabled'] ?? $disabled,
    ])
@endforeach

@if ($grid_fields->isNotEmpty())
    <div class="grid grid-cols-1 gap-4 rounded-lg border border-base-300 bg-base-200 p-6 shadow-sm sm:grid-cols-2">
        @foreach ($grid_fields as $field)
            @include('livewire.admin.partials.fields.' . $field['type'], [
                'field' => $field,
                'disabled' => $field['disabled'] ?? $disabled,
            ])
        @endforeach
    </div>
@endif
