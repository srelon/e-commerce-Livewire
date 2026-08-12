<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Traits\HasAccessControl;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin.app')]
class Form extends Component
{
    use HasAccessControl, WithFileUploads;

    protected string $accessKey = 'users';

    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public $avatar = null;

    public function mount(?User $user = null): void
    {
        if ($user) {
            $this->guardView();

            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
        } else {
            abort_unless($this->hasAccess('edit'), 403);
        }
    }

    public function save(): void
    {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'password' => [$this->user ? 'nullable' : 'required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $isCreating = ! $this->user;
        $user = $this->user ?? new User();
        $user->name = $this->name;
        $user->email = $this->email;

        if (filled($this->password)) {
            $user->password = $this->password;
        }

        if ($this->avatar) {
            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        $user->save();

        session()->flash('notify', ['type' => 'success', 'message' => $isCreating ? 'User created.' : 'User updated.']);

        $this->redirectRoute('admin.users.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.users.form');
    }
}
