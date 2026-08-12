<flux:modal wire:model.self="confirming_action" name="confirm-action" class="max-w-sm">
    <div class="space-y-4">
        <flux:heading size="lg">{{ $confirm_action_heading }}</flux:heading>

        <flux:text>{{ $confirm_action_message }}</flux:text>

        <div class="flex justify-end gap-3 pt-2">
            <flux:button variant="ghost" wire:click="cancelAction">Cancel</flux:button>
            <flux:button variant="{{ $confirm_action_variant }}" wire:click="confirmAction">{{ $confirm_action_label }}</flux:button>
        </div>
    </div>
</flux:modal>
