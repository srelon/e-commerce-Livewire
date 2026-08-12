<?php

namespace App\Livewire\Components\Auth;

use App\Traits\ThrottlesLogins;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin.guest')]
class Login extends Component
{
    use ThrottlesLogins;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $this->attemptLogin('admins', $this->email, $this->password, $this->remember);

        request()->session()->regenerate();

        $this->redirectRoute('admin.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
