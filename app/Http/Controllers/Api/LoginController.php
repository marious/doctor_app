<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\Request;
use Modules\Users\Dto\LoginDTO;
use Modules\Users\Resources\UserResource;
use Modules\Users\Services\AuthService;

class LoginController extends Controller
{
    public function __construct(
        protected readonly AuthService $authService
    )
    {
    }

    public function login(LoginRequest $request)
    {
        return UserResource::make($this->authService->login(LoginDTO::fromRequest($request)));
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        return response()->json([
            'message' => __('Logout successfully'),
            'status' => 200,
        ]);
    }
}
