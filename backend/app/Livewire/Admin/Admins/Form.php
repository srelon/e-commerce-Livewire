<?php

namespace App\Livewire\Admin\Admins;

use App\Livewire\Traits\HasAccessControl;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Collection;
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
        return view('livewire.admin.admins.form');
    }
}
