<?php

namespace App\Livewire\Components\Admins;

use App\Livewire\Traits\HasAccessControl;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin.app')]
class Form extends Component
{
    use HasAccessControl, WithFileUploads;

    protected string $accessKey = 'admins';

    public ?Admin $admin = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public $role_id = null;

    public $avatar = null;

    public function mount(?Admin $admin = null): void
    {
        if ($admin) {
            $this->guardView();

            $this->admin = $admin;
            $this->name = $admin->name;
            $this->email = $admin->email;
            $this->role_id = $admin->role_id;
        } else {
            abort_unless($this->hasAccess('edit'), 403);

            $this->role_id = $this->roles->first()?->id;
        }
    }

    #[Computed]
    public function roles(): Collection
    {
        return AdminRole::orderBy('label')->get();
    }

    #[Computed]
    public function schema(): array
    {
        return [
            [
                'name' => 'avatar',
                'label' => 'Avatar',
                'type' => 'file',
                'preview' => $this->admin?->avatar ? Storage::disk('public')->url($this->admin->avatar) : null,
                'preview_class' => 'h-16 w-16 rounded-full object-cover',
            ],
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
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'text',
                'input_type' => 'password',
                'placeholder' => $this->admin ? 'Leave blank to keep current password' : null,
            ],
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

    public function save(): void
    {
        if (! $this->guardSave()) {
            return;
        }

        $isCreating = ! $this->admin;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => $isCreating ? ['required', 'email', Rule::unique('admins', 'email')] : [],
            'password' => [$this->admin ? 'nullable' : 'required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:admin_roles,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $admin = $this->admin ?? new Admin();
        $admin->name = $this->name;
        $admin->role_id = (int) $this->role_id;

        if ($isCreating) {
            $admin->email = $this->email;
        }

        if (filled($this->password)) {
            $admin->password = $this->password;
        }

        if ($this->avatar) {
            $admin->avatar = $this->avatar->store('avatars', 'public');
        }

        $admin->save();

        session()->flash('notify', ['type' => 'success', 'message' => $isCreating ? 'Admin created.' : 'Admin updated.']);

        $this->redirectRoute('admin.admins.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.partials.resource-form', [
            'title' => $this->admin ? 'Edit admin' : 'New admin',
            'fields' => $this->schema(),
            'disabled' => ! $this->hasAccess('edit'),
            'cancel_route' => 'admin.admins.index',
        ]);
    }
}
