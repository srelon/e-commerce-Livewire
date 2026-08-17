<?php

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasAccountFields
{
    use HasFormHelpers;

    public string $name = '';

    public string $password = '';

    public $avatar = null;

    protected function fillAccountFields(Model $model): void {
        $this->name = $model->name;
    }

    protected function accountValidationRules(bool $isEditing): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => [$isEditing ? 'nullable' : 'required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function applyPasswordUpdate(Model $model): void {
        if (filled($this->password)) {
            $model->password = $this->password;
            $this->password = '';
        }
    }

    protected function applyAvatarUpload(Model $model): void {
        if ($this->avatar) {
            $model->avatar = $this->avatar->store('avatars', 'public');
            $this->avatar = null;
        }
    }

    protected function accountAvatarField(?string $avatarPath): array {
        return [
            'name' => 'avatar',
            'label' => 'Avatar',
            'type' => 'file',
            'preview' => $this->previewUrl($avatarPath),
            'preview_class' => 'h-16 w-16 rounded-full object-cover',
        ];
    }

    protected function accountPasswordField(bool $isEditing): array {
        return [
            'name' => 'password',
            'label' => 'Password',
            'type' => 'text',
            'input_type' => 'password',
            'placeholder' => $isEditing ? 'Leave blank to keep current password' : null,
        ];
    }
}
