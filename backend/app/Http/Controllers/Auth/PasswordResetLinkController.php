<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function store(ForgotPasswordRequest $request) {
        $status = $this->authService->sendResetLink($request->validated('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->respondWithError(__($status), 422);
        }

        return $this->respondWithJson(['message' => __($status)]);
    }
}
