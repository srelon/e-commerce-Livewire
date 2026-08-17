<?php

namespace App\Livewire\Traits;

trait HandlesModalForm
{
    use HasFormHelpers;

    public function mount(): void {
        $this->guardView();
    }

    public function openCreate(): void {
        if (! $this->hasAccess('edit')) {
            return;
        }

        $this->resetForm();
        $this->{$this->modalProperty()} = true;
    }

    public function closeModal(): void {
        $this->{$this->modalProperty()} = false;
    }

    public function closeListModal(): void {
        $this->show_list_modal = false;
    }

    protected function modalProperty(): string {
        return 'show_modal';
    }
}
