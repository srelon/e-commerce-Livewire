<?php

namespace App\Livewire\Components\Admins;

use App\Livewire\Traits\HandlesFullPageSave;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasAccountFields;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.components.layouts.admin.app')]
class Form extends Component
{
    use HandlesFullPageSave, HasAccessControl, HasAccountFields, WithFileUploads;

    protected string $accessKey = 'admins';

    protected string $routePrefix = 'admins';

    protected string $routeParamKey = 'admin';

    protected string $saveMessageName = 'Admin';

    public ?Admin $admin = null;

    public string $email = '';

    public $role_id = null;

    public function mount(?Admin $admin = null): void {
        if ($admin) {
            $this->guardView();

            $this->admin = $admin;
            $this->fillAccountFields($admin);
            $this->email = $admin->email;
            $this->role_id = $admin->role_id;
        } else {
            abort_unless($this->hasAccess('edit'), 403);

            $this->role_id = $this->roles->first()?->id;
        }
    }

    #[Computed]
    public function roles(): Collection {
        return AdminRole::orderBy('label')->get();
    }

    #[Computed]
    public function schema(): array {
        return [
            $this->accountAvatarField($this->admin?->avatar),
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
                ...($this->admin ? ['disabled' => true] : []),
            ],
            $this->accountPasswordField((bool) $this->admin),
            [
                'name' => 'role_id',
                'label' => 'Role',
                'type' => 'select',
                'options' => $this->roles->map(fn (AdminRole $role) => [
                    'value' => $role->id,
                    'label' => $role->label,
                ])->all(),
            ],
        ];
    }

    private function persist(): ?Admin {
        if (! $this->guardSave()) {
            return null;
        }

        $isCreating = ! $this->admin;

        $this->validate([
            'email' => $isCreating ? ['required', 'email', Rule::unique('admins', 'email')] : [],
            'role_id' => ['required', 'exists:admin_roles,id'],
            ...$this->accountValidationRules((bool) $this->admin),
        ]);

        $admin = $this->admin ?? new Admin;
        $admin->name = $this->name;
        $admin->role_id = (int) $this->role_id;

        if ($isCreating) {
            $admin->email = $this->email;
        }

        $this->applyPasswordUpdate($admin);
        $this->applyAvatarUpload($admin);

        $admin->save();

        $this->admin = $admin;

        $this->dispatch('admin-access-updated');
        $this->dispatch('profile-updated');

        return $admin;
    }

    public function render() {
        return $this->renderResourceForm(
            pageTitle: $this->admin ? 'Edit admin' : 'New admin',
            disabled: ! $this->hasAccess('edit'),
            cancelRoute: 'admin.admins.index',
        );
    }
}
