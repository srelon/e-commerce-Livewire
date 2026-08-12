<?php

namespace App\Livewire\Traits;

trait HasAccessControl
{
    public function hasAccess(string $type): bool
    {
        return auth('admins')->user()?->hasAccess($this->accessKey.'.'.$type) ?? false;
    }

    protected function guardView(): void
    {
        abort_unless($this->hasAccess('view'), 403);
    }

    protected function guardSave(): bool
    {
        if ($this->hasAccess('edit')) {
            return true;
        }

        $this->dispatch('notify', type: 'danger', message: 'Access denied');

        return false;
    }
}
