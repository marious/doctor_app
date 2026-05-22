<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePatientServicePaymentRequest;
use Illuminate\Http\JsonResponse;
use Modules\ServiceRegistrations\Models\PatientServicePayment;
use Modules\ServiceRegistrations\Models\PatientServiceRegistration;

class PatientServicePaymentController extends Controller
{
    /**
     * POST /assistant/service-registrations/{registration}/payments
     * Add a payment installment and update the running total.
     */
    public function store(StorePatientServicePaymentRequest $request, PatientServiceRegistration $serviceRegistration): JsonResponse
    {
        $maxAllowed = $serviceRegistration->remaining_amount;

        abort_if($maxAllowed <= 0, 422, __('This service is already fully paid.'));

        $amount = min((float) $request->amount, $maxAllowed);

        PatientServicePayment::create([
            'registration_id' => $serviceRegistration->id,
            'amount'          => $amount,
            'payment_date'    => $request->payment_date,
        ]);

        $serviceRegistration->increment('amount_paid', $amount);

        return response()->json([
            'success'           => true,
            'message'           => __('Payment recorded successfully.'),
            'amount_paid_total' => round($serviceRegistration->fresh()->amount_paid, 2),
            'remaining'         => round($serviceRegistration->fresh()->remaining_amount, 2),
        ]);
    }
}
