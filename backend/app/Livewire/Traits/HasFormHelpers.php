<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Storage;

trait HasFormHelpers
{
    protected function previewUrl(array|string|null $image): ?string {
        if (is_array($image)) {
            return ! empty($image['original']) ? Storage::disk('public')->url($image['original']) : null;
        }

        return $image ? Storage::disk('public')->url($image) : null;
    }

    protected function resetFormFields(array $properties): void {
        $this->reset($properties);
        $this->resetErrorBag();
        $this->dispatch('admin-form-reset');
    }

    protected function statusField(): array {
        return [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select',
            'options' => [
                ['value' => 1, 'label' => 'Active'],
                ['value' => 0, 'label' => 'Inactive'],
            ],
        ];
    }
}
