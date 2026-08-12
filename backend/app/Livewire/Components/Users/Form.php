<?php

namespace App\Livewire\Components\Users;

use App\Livewire\Traits\HasAccessControl;
use App\Models\User;
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

    #[Computed]
    public function schema(): array
    {
        return [
            [
                'name' => 'avatar',
                'label' => 'Avatar',
                'type' => 'file',
                'preview' => $this->user?->avatar ? Storage::disk('public')->url($this->user->avatar) : null,
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
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'text',
                'input_type' => 'password',
                'placeholder' => $this->user ? 'Leave blank to keep current password' : null,
            ],
        ];
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
        return view('livewire.admin.partials.resource-form', [
            'title' => $this->user ? 'Edit user' : 'New user',
            'fields' => $this->schema(),
            'disabled' => ! $this->hasAccess('edit'),
            'cancel_route' => 'admin.users.index',
        ]);
    }
}
