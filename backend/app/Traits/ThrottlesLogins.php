<?php

namespace App\Traits;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ThrottlesLogins
{
    protected function attemptLogin(string $guard, string $email, string $password, bool $remember = false): void {
        $throttleKey = $this->throttleKey($email);

        $this->ensureIsNotRateLimited($throttleKey);

        if (! Auth::guard($guard)->attempt(['email' => $email, 'password' => $password], $remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
    }

    protected function ensureIsNotRateLimited(string $throttleKey): void {
        if (! RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(string $email): string {
        return Str::lower($email).'|'.request()->ip();
    }
}
