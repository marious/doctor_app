<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\OtpSendController;
use App\Http\Controllers\Api\PatientTrackingController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Middleware\ValidateHeadersMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(ValidateHeadersMiddleware::class)->prefix('v1')->group(function () {

    Route::controller(OtpSendController::class)->prefix('otp')->group(function () {
        Route::post('send', 'send');
        Route::post('verify', 'verify');
        Route::post('resend', 'resend');
    });

    Route::prefix('auth')->group(function () {
        Route::controller(RegisterController::class)->group(function () {
            Route::post('register', 'register');
        });

        Route::controller(LoginController::class)->group(function () {
            Route::post('login', 'login');
            Route::post('logout', 'logout')->middleware('auth:sanctum');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('patient-tracking/store', [PatientTrackingController::class, 'store']);

        //--------------------------------------------------- Profile & Settings -------------------------------------------
        Route::prefix('profile')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
            Route::post('/upload-avatar', [\App\Http\Controllers\Api\UploadAvatarController::class, 'uploadAvatar']);
            Route::post('/', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
            Route::post('/password', [\App\Http\Controllers\Api\ProfileController::class, 'updatePassword']);
            Route::put('/settings', [\App\Http\Controllers\Api\ProfileController::class, 'updateSettings']);
            Route::post('/delete-account', [\App\Http\Controllers\Api\ProfileController::class, 'destroy']);
        });

        //--------------------------------------------------- Treatments ---------------------------------------------------
        Route::prefix('treatment')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\TreatmentController::class, 'index']);
            Route::get('/tracker', [\App\Http\Controllers\Api\TreatmentController::class, 'tracker']);
            Route::post('/tracker/log', [\App\Http\Controllers\Api\TreatmentController::class, 'logStatus']);
        });

        //--------------------------------------------------- Appointments--------------------------------------------------
        Route::prefix('appointment')->group(function () {
            // List appointments (past + next upcoming)
            Route::get('/', [AppointmentController::class, 'index']);
            // Book a new appointment
            Route::post('/', [AppointmentController::class, 'store']);
            // Appointment details (with prescriptions & tests)
            Route::get('/{appointment}', [AppointmentController::class, 'show']);
            // Appointment status + timeline
            Route::get('/{appointment}/status', [AppointmentController::class, 'status']);
            // Cancel appointment
            Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel']);
            // Reschedule (Change appointment)
            Route::post('/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
            // Available time slots for a doctor on a date
            Route::get('/available-slots', [AppointmentController::class, 'availableSlots']);
        });
    });
});