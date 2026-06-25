<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\DoctorAppointmentController;
use App\Http\Controllers\Api\DoctorDashboardController;
use App\Http\Controllers\Api\DoctorReportsController;
use App\Http\Controllers\Api\PatientDirectoryController;
use App\Http\Controllers\Api\AssistantPatientSearchController;
use App\Http\Controllers\Api\PatientFinancialTimelineController;
use App\Http\Controllers\Api\PatientServicePaymentController;
use App\Http\Controllers\Api\PatientOverviewController;
use App\Http\Controllers\Api\ClinicServiceController;
use App\Http\Controllers\Api\PatientServiceRegistrationController;
use App\Http\Controllers\Api\PatientLabResultController;
use App\Http\Controllers\Api\PatientPelvicExaminationController;
use App\Http\Controllers\Api\PatientUltrasoundFindingController;
use App\Http\Controllers\Api\PatientPrescriptionController;
use App\Http\Controllers\Api\PatientSessionController;
use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PatientMedicalReportsController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\HydrationController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\OtpSendController;
use App\Http\Controllers\Api\PatientTrackingController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\TrackerController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DoctorChatController;
use App\Http\Middleware\ValidateHeadersMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/v1/{patient}/dummy-notifications', \App\Http\Controllers\Api\DummyNotificationsController::class);
Route::post('/v1/{patient}/dummy-chat', \App\Http\Controllers\Api\DummyChatController::class);

Route::middleware(ValidateHeadersMiddleware::class)->prefix('v1')->group(function () {

    Route::controller(OtpSendController::class)->prefix('otp')->group(function () {
        Route::post('send', 'send');
        Route::post('verify', 'verify');
        Route::post('resend', 'resend');
    });

    // General App Informational Pages
    Route::get('app-info', [\App\Http\Controllers\Api\AppInfoController::class, 'index']);

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
        Route::get('home', [HomeController::class, 'index']);

        Route::post('/pages/{slug}', [\App\Http\Controllers\Api\PageController::class, 'update']);

        //--------------------------------------------------- Notifications -------------------------------------------
        Route::prefix('notifications')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\PatientNotificationController::class, 'index']);
            Route::post('/read-all', [\App\Http\Controllers\Api\PatientNotificationController::class, 'markAllRead']);
            Route::post('/{notification}/read', [\App\Http\Controllers\Api\PatientNotificationController::class, 'markRead']);
        });

        //--------------------------------------------------- Profile & Settings -------------------------------------------
        Route::prefix('profile')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
            Route::post('/upload-avatar', [\App\Http\Controllers\Api\UploadAvatarController::class, 'uploadAvatar']);
            Route::post('/upload-medical-report', [\App\Http\Controllers\Api\UploadMedicalReportController::class, 'uploadReport']);
            Route::post('/', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
            Route::post('/password', [\App\Http\Controllers\Api\ProfileController::class, 'updatePassword']);
            Route::post('/settings', [\App\Http\Controllers\Api\ProfileController::class, 'updateSettings']);
            Route::post('/delete-account', [\App\Http\Controllers\Api\ProfileController::class, 'destroy']);
            
            Route::get('/devices', [\App\Http\Controllers\Api\ProfileController::class, 'devices']);
            Route::post('/devices/{tokenId}', [\App\Http\Controllers\Api\ProfileController::class, 'revokeDevice']);
        });


        //--------------------------------------------------- Patient Tracking ---------------------------------------------------
        Route::post('patient-tracking/store', [PatientTrackingController::class, 'store']);
        // Full tracker screen data (pregnancy or menstrual)
        Route::get('tracker', [TrackerController::class, 'show']);
        // Calendar navigation only
        Route::get('tracker/calendar', [TrackerController::class, 'calendar']);
        // Log weight / BPM
        Route::post('tracker/health-stats', [TrackerController::class, 'logHealthStat']);
        // Available symptoms list (grouped)
        Route::get('tracker/symptoms', [TrackerController::class, 'symptoms']);
        // Log period symptoms
        Route::post('tracker/log-symptoms', [TrackerController::class, 'logSymptom']);

        //--------------------------------------------------- Treatments ---------------------------------------------------
        Route::prefix('treatment')->group(function () {
            // Daily medication schedule (must come before {appointment?} to avoid capture)
            Route::get('/tracker', [TreatmentController::class, 'tracker']);
            Route::post('/tracker/log', [TreatmentController::class, 'logStatus']);
            // Active & past treatment list
            Route::get('/list', [TreatmentController::class, 'index']);
            // Download prescription as PDF (must be before {appointment?} wildcard)
            Route::get('/pdf', [TreatmentController::class, 'downloadPdf']);
            Route::get('/{appointment}/pdf', [TreatmentController::class, 'downloadPdf']);
            Route::get('/session/{session}/pdf', [TreatmentController::class, 'downloadSessionPdf']);
            // Treatment screen: omit ID to get latest appointment, or pass ID for a specific one
            Route::get('/{appointment?}', [TreatmentController::class, 'show']);
        });

        //--------------------------------------------------- Hydration ---------------------------------------------------
        Route::prefix('hydration')->group(function () {
            // Today's hydration status
            Route::get('/', [HydrationController::class, 'show']);
            // Tap a cup — send cups_count (new total)
            Route::post('/log', [HydrationController::class, 'log']);
            // Reset tracker to 0 cups
            Route::post('/reset', [HydrationController::class, 'reset']);
        });

        //--------------------------------------------------- Appointments--------------------------------------------------
        Route::prefix('appointment')->group(function () {
            // Available time slots for a date (must be before /{appointment} wildcard)
            Route::get('/available-slots', [AppointmentController::class, 'availableSlots']);
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
        });


        //--------------------------------------------------- Articles --------------------------------------------------
        Route::prefix('articles')->group(function () {
            Route::get('/', [ArticleController::class, 'index']);
            Route::get('/{article}', [ArticleController::class, 'show']);
        });

        //--------------------------------------------------- Videos --------------------------------------------------
        Route::prefix('videos')->group(function () {
            Route::get('/', [VideoController::class, 'index']);
            Route::get('/{video}', [VideoController::class, 'show']);
        });

        //--------------------------------------------------- Advertisements --------------------------------------------------
        Route::prefix('advs')->group(function () {
            Route::get('/', [AdvertisementController::class, 'index']);
            Route::get('/{advertisement}', [AdvertisementController::class, 'show']);
        });

        //--------------------------------------------------- Chat (patient) --------------------------------------------------
        Route::prefix('chat/conversations')->group(function () {
            Route::get('/', [ChatController::class, 'conversations']);
            Route::get('/{conversation}/messages', [ChatController::class, 'messages']);
            Route::post('/{conversation}/messages', [ChatController::class, 'sendMessage']);
            Route::post('/{conversation}/read', [ChatController::class, 'markRead']);
        });

        //--------------------------------------------------- Assistant --------------------------------------------------
        Route::middleware(['assistant'])->prefix('assistant')->group(function () {

            Route::post('/chat/with/{patient}', [DoctorChatController::class, 'openWith']);

            Route::prefix('appointments')->group(function () {
                Route::get('/', [DoctorAppointmentController::class, 'index']);
                Route::get('/{appointment}', [DoctorAppointmentController::class, 'show']);
                Route::post('/{appointment}/approve', [DoctorAppointmentController::class, 'approve']);
                Route::post('/{appointment}/reject', [DoctorAppointmentController::class, 'reject']);
                Route::post('/{appointment}/reschedule', [DoctorAppointmentController::class, 'reschedule']);
            });

            // Patient search for registration form
            Route::get('/patients/search', AssistantPatientSearchController::class);

            // Active services for registration form dropdown
            Route::get('/services', [ClinicServiceController::class, 'activeServices']);

            // Patient financial timeline
            Route::get('/patients/{patient}/financial-timeline', PatientFinancialTimelineController::class);

            // Service registrations & payments
            Route::prefix('service-registrations')->group(function () {
                Route::get('/', [PatientServiceRegistrationController::class, 'index']);
                Route::post('/', [PatientServiceRegistrationController::class, 'store']);
                Route::get('/{serviceRegistration}', [PatientServiceRegistrationController::class, 'show']);
                Route::delete('/{serviceRegistration}', [PatientServiceRegistrationController::class, 'destroy']);
                Route::post('/{serviceRegistration}/payments', [PatientServicePaymentController::class, 'store']);
            });

            // Chat
            Route::prefix('chat/conversations')->group(function () {
                Route::get('/', [DoctorChatController::class, 'conversations']);
                Route::get('/{conversation}/messages', [DoctorChatController::class, 'messages']);
                Route::post('/{conversation}/messages', [DoctorChatController::class, 'sendMessage']);
                Route::post('/{conversation}/read', [DoctorChatController::class, 'markRead']);
            });

            // Notification settings
            Route::get('/notification-settings', [\App\Http\Controllers\Api\DoctorNotificationSettingsController::class, 'show']);
            Route::post('/notification-settings', [\App\Http\Controllers\Api\DoctorNotificationSettingsController::class, 'update']);

        });

        //--------------------------------------------------- Doctor/Admin --------------------------------------------------
        Route::middleware(['admin'])->prefix('doctor')->group(function() {

            Route::get('/dashboard', DoctorDashboardController::class);

            Route::post('/chat/with/{patient}', [DoctorChatController::class, 'openWith']);

            // ── Staff management (create doctor / assistant accounts) ──────
            Route::get('/staff',           [StaffController::class, 'index']);
            Route::post('/staff',          [StaffController::class, 'store']);
            Route::post('/staff/{user}',  [StaffController::class, 'update']);
            Route::delete('/staff/{user}', [StaffController::class, 'destroy']);

            Route::prefix('articles')->group(function () {
                Route::post('/', [ArticleController::class, 'store']);
                Route::post('/{article}', [ArticleController::class, 'update']);
                Route::delete('/{article}', [ArticleController::class, 'destroy']);
            });

            Route::prefix('videos')->group(function () {
                Route::post('/', [VideoController::class, 'store']);
                Route::post('/{video}', [VideoController::class, 'update']);
                Route::delete('/{video}', [VideoController::class, 'destroy']);
            });

            Route::prefix('advs')->group(function () {
                Route::post('/', [AdvertisementController::class, 'store']);
                Route::post('/{advertisement}', [AdvertisementController::class, 'update']);
                Route::delete('/{advertisement}', [AdvertisementController::class, 'destroy']);
            });

            //--------------------------------------------------- Patient Directory --------------------------------------------------
            Route::prefix('patients')->group(function () {
                Route::get('/', [PatientDirectoryController::class, 'index']);
                Route::get('/export-pdf', [\App\Http\Controllers\Api\PatientExportController::class, 'all']);
                Route::get('/{patient}', [PatientDirectoryController::class, 'show']);
                Route::get('/{patient}/overview', PatientOverviewController::class);
                Route::get('/{patient}/export-pdf', [\App\Http\Controllers\Api\PatientExportController::class, 'single']);
                Route::get('/{patient}/medical-reports', PatientMedicalReportsController::class);
                Route::patch('/{patient}/risk-status', [PatientDirectoryController::class, 'updateRiskStatus']);

                // Clinical sessions
                Route::get('/{patient}/sessions/dropdown', [PatientSessionController::class, 'dropdown']);
                Route::get('/{patient}/sessions', [PatientSessionController::class, 'index']);
                Route::post('/{patient}/sessions', [PatientSessionController::class, 'store']);
                Route::get('/{patient}/sessions/{session}', [PatientSessionController::class, 'show']);
                Route::post('/{patient}/sessions/{session}', [PatientSessionController::class, 'update']);

                // Ultrasound findings (dedicated uploads + merged from sessions)
                Route::get('/{patient}/ultrasound-findings', [PatientUltrasoundFindingController::class, 'index']);
                Route::post('/{patient}/ultrasound-findings', [PatientUltrasoundFindingController::class, 'store']);
                Route::delete('/{patient}/ultrasound-findings/{ultrasoundFinding}', [PatientUltrasoundFindingController::class, 'destroy']);

                // Lab results (dedicated uploads + merged from sessions)
                Route::get('/{patient}/lab-results', [PatientLabResultController::class, 'index']);
                Route::post('/{patient}/lab-results', [PatientLabResultController::class, 'store']);
                Route::delete('/{patient}/lab-results/{labResult}', [PatientLabResultController::class, 'destroy']);

                // Pelvic examinations (dedicated uploads + merged from sessions)
                Route::get('/{patient}/pelvic-examinations', [PatientPelvicExaminationController::class, 'index']);
                Route::post('/{patient}/pelvic-examinations', [PatientPelvicExaminationController::class, 'store']);
                Route::delete('/{patient}/pelvic-examinations/{pelvicExamination}', [PatientPelvicExaminationController::class, 'destroy']);

                // Prescriptions
                Route::get('/{patient}/prescriptions', [PatientPrescriptionController::class, 'index']);
                Route::post('/{patient}/prescriptions', [PatientPrescriptionController::class, 'store']);
                Route::post('/{patient}/prescriptions/{prescription}', [PatientPrescriptionController::class, 'update']);
                Route::post('/{patient}/prescriptions/{prescription}/stop', [PatientPrescriptionController::class, 'stop']);
                Route::delete('/{patient}/prescriptions/{prescription}', [PatientPrescriptionController::class, 'destroy']);
            });

            //--------------------------------------------------- Doctor Appointments --------------------------------------------------
            Route::prefix('appointments')->group(function () {
                Route::get('/', [DoctorAppointmentController::class, 'index']);
                Route::get('/{appointment}', [DoctorAppointmentController::class, 'show']);
                Route::post('/{appointment}/approve', [DoctorAppointmentController::class, 'approve']);
                Route::post('/{appointment}/reject', [DoctorAppointmentController::class, 'reject']);
                Route::post('/{appointment}/reschedule', [DoctorAppointmentController::class, 'reschedule']);
            });

            //--------------------------------------------------- Services & Pricing --------------------------------------------------
            Route::prefix('services')->group(function () {
                Route::get('/categories', [ClinicServiceController::class, 'categories']);
                Route::get('/', [ClinicServiceController::class, 'index']);
                Route::post('/', [ClinicServiceController::class, 'store']);
                Route::post('/{service}', [ClinicServiceController::class, 'update']);
                Route::delete('/{service}', [ClinicServiceController::class, 'destroy']);
            });

            //--------------------------------------------------- Reports & Analytics --------------------------------------------------
            Route::prefix('reports')->group(function () {
                Route::get('/', [DoctorReportsController::class, 'index']);
                Route::get('/export', [DoctorReportsController::class, 'export']);
            });

            //--------------------------------------------------- Availability --------------------------------------------------
            Route::prefix('availability')->group(function () {
                Route::get('/calendar', [AvailabilityController::class, 'calendar']);
                Route::post('/bulk', [AvailabilityController::class, 'bulkSave']);
                Route::get('/{date}', [AvailabilityController::class, 'show']);
                Route::post('/{date}', [AvailabilityController::class, 'save']);
            });

            // Chat
            Route::prefix('chat/conversations')->group(function () {
                Route::get('/', [DoctorChatController::class, 'conversations']);
                Route::get('/{conversation}/messages', [DoctorChatController::class, 'messages']);
                Route::post('/{conversation}/messages', [DoctorChatController::class, 'sendMessage']);
                Route::post('/{conversation}/read', [DoctorChatController::class, 'markRead']);
            });

            // Notification settings
            Route::get('/notification-settings', [\App\Http\Controllers\Api\DoctorNotificationSettingsController::class, 'show']);
            Route::post('/notification-settings', [\App\Http\Controllers\Api\DoctorNotificationSettingsController::class, 'update']);

        });


    });
});