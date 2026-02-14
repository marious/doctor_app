<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use Illuminate\Http\Request;
use Modules\Users\Dto\RegisterDTO;
use Modules\Users\Events\UserRegistered;
use Modules\Users\Services\AuthService;

class RegisterController extends Controller
{
    public function __construct(
        protected readonly AuthService $authService
    )
    {
    }

    public function register(RegisterRequest $request)
    {
        $dto = RegisterDto::fromRequest($request);
        if ($user = $this->authService->register($dto)) {
            event(new UserRegistered($user));
            return response()->json([
                'message' => __('Registration has been done successfully!'),
                'status' => 200,
            ], 200);
        }
    }
}
