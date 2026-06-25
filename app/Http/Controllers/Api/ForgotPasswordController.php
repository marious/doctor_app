<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Users\Models\User;

class ForgotPasswordController extends Controller
{
    private const EXPIRES_MINUTES = 60;

    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        // Always return success to avoid email enumeration
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => __('If an account with that email exists, a reset code has been sent.'),
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($code), 'created_at' => now()],
        );

        Mail::to($user->email)->send(new ResetPasswordMail($user, $code));

        return response()->json([
            'success' => true,
            'message' => __('A 6-digit reset code has been sent to your email.'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (!$record || !Hash::check($request->input('token'), $record->token)) {
            return response()->json([
                'success' => false,
                'message' => __('The reset code is invalid.'),
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(self::EXPIRES_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

            return response()->json([
                'success' => false,
                'message' => __('The reset code has expired. Please request a new one.'),
            ], 422);
        }

        $user = User::where('email', $request->input('email'))->firstOrFail();
        $user->forceFill(['password' => $request->input('password')])->save();

        DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

        return response()->json([
            'success' => true,
            'message' => __('Password has been reset successfully.'),
        ]);
    }
}
