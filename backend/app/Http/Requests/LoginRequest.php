<?php

namespace App\Http\Requests;

use App\Traits\ThrottlesLogins;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use ThrottlesLogins;

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function authenticate(): void {
        $this->attemptLogin('web', $this->input('email'), $this->input('password'));
    }
}
