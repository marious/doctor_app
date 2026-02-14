<?php

namespace Modules\Users\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Users\Dto\LoginDTO;
use Modules\Users\Dto\RegisterDTO;
use Modules\Users\Models\User;

class AuthService
{
    public function register(RegisterDto $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'age' => $dto->age,
            'blood_group' => $dto->bloodGroup,
            'marital_status' => $dto->maritalStatus,
            'date_of_marriage' => $dto->dateOfMarriage,
            'husband_name' => $dto->husbandName,
            'phone' => $dto->phone,
            'emergency_number' => $dto->emergencyNumber,
            'address' => $dto->address,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);
    }

    public function logout(): bool
    {
        $user = Auth::user();

        if ($user) {
            // For API logout - revoke all tokens
            if (request()->expectsJson()) {
//                $user->tokens()->delete();
                $user->currentAccessToken()->delete();
            } else {
                Auth::logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            }

            return true;
        }

        return false;
    }

    public function login(LoginDTO $dto)
    {
        $credentials = [
            'password' => $dto->password,
        ];

        if (str_contains($dto->phoneOrEmail, '@')) {
            $credentials['email'] = $dto->phoneOrEmail;
        } else {
            $credentials['phone'] = $dto->phoneOrEmail;
        }

        if (!Auth::attempt($credentials, $dto->remember)) {
            throw ValidationException::withMessages([
                'phone' => [__('auth.failed')],
            ]);
        }

        $user = Auth::user();

        if (!$user->active) {
            throw ValidationException::withMessages([
                'email' => [__('auth.inactive')],
            ]);
        }

        // Revoke any existing tokens
        $user->tokens()->where('name', 'auth_token')->delete();

        // Create token with expiration
        $expiresAt = Carbon::now()->addDays(120);
        $accessToken = $user->createToken($this->generateDeviceName(), ['*'], $expiresAt);
        $user->auth_token = $accessToken->plainTextToken;
        return $user;
    }

    private function generateDeviceName(): string
    {
        // Get user agent
        $userAgent = request()->header('User-Agent');

        // Try to identify device type
        $deviceType = 'unknown';
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone')) {
            $deviceType = 'mobile';
        } elseif (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            $deviceType = 'tablet';
        } else {
            $deviceType = 'desktop';
        }

        // Add timestamp to make it unique
        return $deviceType . '_' . now()->timestamp;
    }
}