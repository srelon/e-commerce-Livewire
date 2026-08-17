<?php

namespace App\Livewire\Components\Users;

use App\Livewire\Traits\HandlesFullPageSave;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasAccountFields;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.components.layouts.admin.app')]
class Form extends Component
{
    use HandlesFullPageSave, HasAccessControl, HasAccountFields, WithFileUploads;

    protected string $accessKey = 'users';

    protected string $routePrefix = 'users';

    protected string $routeParamKey = 'user';

    protected string $saveMessageName = 'User';

    public ?User $user = null;

    public string $email = '';

    public function mount(?User $user = null): void {
        if ($user) {
            $this->guardView();

            $this->user = $user;
            $this->fillAccountFields($user);
            $this->email = $user->email;
        } else {
            abort_unless($this->hasAccess('edit'), 403);
        }
    }

    #[Computed]
    public function schema(): array {
        return [
            $this->accountAvatarField($this->user?->avatar),
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'text',
                'input_type' => 'email',
            ],
            $this->accountPasswordField((bool) $this->user),
        ];
    }

    private function persist(): ?User {
        if (! $this->guardSave()) {
            return null;
        }

        $this->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user?->id)],
            ...$this->accountValidationRules((bool) $this->user),
        ]);

        $user = $this->user ?? new User;
        $user->name = $this->name;
        $user->email = $this->email;

        $this->applyPasswordUpdate($user);
        $this->applyAvatarUpload($user);

        $user->save();

        $this->user = $user;

        return $user;
    }

    public function render() {
        return $this->renderResourceForm(
            pageTitle: $this->user ? 'Edit user' : 'New user',
            disabled: ! $this->hasAccess('edit'),
            cancelRoute: 'admin.users.index',
        );
    }
}
