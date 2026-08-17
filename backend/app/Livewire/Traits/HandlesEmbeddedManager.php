<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

trait HandlesEmbeddedManager
{
    use ConfirmsAction, HandlesModalForm;

    public bool $show_list_modal = false;

    public bool $show_form_modal = false;

    public ?int $editing_id = null;

    public string $name = '';

    public $status = 1;

    #[Computed]
    public function list(): array {
        $countKey = Str::snake($this->countRelation).'_count';

        return $this->modelClass::query()
            ->withCount($this->countRelation)
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => array_merge([
                'id' => $model->id,
                'name' => $model->name,
                'status' => (int) $model->status,
                'posts_count' => $model->{$countKey},
            ], $this->extraListFields($model)))
            ->all();
    }

    #[Computed]
    public function schema(): array {
        return [
            ...$this->extraSchemaFieldsBefore(),
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            $this->statusField(),
        ];
    }

    #[On('open-{managerEventKey}-manager')]
    public function openList(): void {
        $this->show_list_modal = true;
    }

    public function openEdit(int $id): void {
        $model = $this->modelClass::findOrFail($id);

        $this->resetForm();
        $this->editing_id = $model->id;
        $this->name = $model->name;
        $this->status = (int) $model->status;
        $this->show_form_modal = true;
    }

    protected function resetForm(): void {
        $this->resetFormFields($this->formFieldNames);
    }

    protected function modalProperty(): string {
        return 'show_form_modal';
    }

    public function save(): void {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate($this->validationRules());

        $isCreating = ! $this->editing_id;
        $model = $this->editing_id ? $this->modelClass::findOrFail($this->editing_id) : new ($this->modelClass);
        $model->name = $this->name;
        $model->status = (int) $this->status;

        $this->beforeSave($model, $isCreating);

        $model->save();

        $this->show_form_modal = false;

        unset($this->list);
        $this->dispatch($this->updatedEvent);
        $this->dispatch('notify', type: 'success', message: $isCreating ? "{$this->itemNoun} created." : "{$this->itemNoun} updated.");
    }

    #[On('delete-{managerEventKey}')]
    public function performDelete(int $id): void {
        if (! $this->guardSave()) {
            return;
        }

        $this->modelClass::whereKey($id)->delete();

        unset($this->list);
        $this->dispatch($this->updatedEvent);
        $this->dispatch('notify', type: 'success', message: "{$this->itemNoun} deleted.");
    }

    public function modalListName(): string {
        return "{$this->managerEventKey}-list";
    }

    public function modalFormName(): string {
        return "{$this->managerEventKey}-form";
    }

    public function confirmModalName(): string {
        return "confirm-action-{$this->managerEventKey}";
    }

    public function deleteMessage(string $name): string {
        return 'Delete '.Str::lower($this->itemNoun)." \"{$name}\"?";
    }

    protected function extraListFields($model): array {
        return [];
    }

    protected function extraSchemaFieldsBefore(): array {
        return [];
    }

    protected function beforeSave($model, bool $isCreating): void {
        //
    }
}
