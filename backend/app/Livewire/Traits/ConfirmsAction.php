<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\On;

trait ConfirmsAction
{
    public bool $confirming_action = false;

    public string $confirm_action_event = '';

    public array $confirm_action_params = [];

    public string $confirm_action_message = 'Are you sure?';

    public string $confirm_action_heading = 'Confirm action';

    public string $confirm_action_label = 'Confirm';

    public string $confirm_action_variant = 'primary';

    #[On('confirm-action')]
    public function openActionConfirmation(
        string $event,
        array $params = [],
        ?string $message = null,
        ?string $heading = null,
        ?string $label = null,
        string $variant = 'primary',
    ): void {
        $this->confirm_action_event = $event;
        $this->confirm_action_params = $params;
        $this->confirm_action_message = $message ?? 'Are you sure?';
        $this->confirm_action_heading = $heading ?? 'Confirm action';
        $this->confirm_action_label = $label ?? 'Confirm';
        $this->confirm_action_variant = $variant;
        $this->confirming_action = true;
    }

    public function confirmAction(): void {
        $this->dispatch($this->confirm_action_event, ...$this->confirm_action_params);
        $this->confirming_action = false;
    }

    public function cancelAction(): void {
        $this->confirming_action = false;
    }
}
