<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Password;

class NewPasswordController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function store(ResetPasswordRequest $request) {
        $status = $this->authService->resetPassword($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            return $this->respondWithError(__($status), 422);
        }

        return $this->respondWithJson(['message' => __($status)]);
    }
}
