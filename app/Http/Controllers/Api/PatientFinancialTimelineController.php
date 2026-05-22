<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ServiceRegistrations\Models\PatientServiceRegistration;
use Modules\Users\Models\User;

class PatientFinancialTimelineController extends Controller
{
    /**
     * GET /assistant/patients/{patient}/financial-timeline
     * Full payment history for a patient across all service registrations.
     */
    public function __invoke(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $registrations = PatientServiceRegistration::with(['service', 'payments'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('service_date')
            ->get();

        $totalPaid      = $registrations->sum('amount_paid');
        $totalRemaining = $registrations->sum('remaining_amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'patient' => [
                    'id'           => $patient->id,
                    'patient_code' => 'P-' . str_pad($patient->id, 3, '0', STR_PAD_LEFT),
                    'name'         => $patient->name,
                    'phone'        => $patient->phone,
                ],
                'summary' => [
                    'total_services'   => $registrations->count(),
                    'total_paid'       => round($totalPaid, 2),
                    'remaining_balance' => round($totalRemaining, 2),
                ],
                'services' => $registrations->map(fn($r) => $this->formatRegistration($r)),
            ],
        ]);
    }

    private function formatRegistration(PatientServiceRegistration $r): array
    {
        // Build payment history with running remaining_after per entry
        $running  = $r->total_price;
        $history  = $r->payments->map(function ($p) use (&$running) {
            $running -= $p->amount;
            return [
                'id'            => $p->id,
                'payment_date'  => $p->payment_date?->toDateString(),
                'amount'        => $p->amount,
                'remaining_after' => round(max(0, $running), 2),
            ];
        });

        $entry = [
            'id'             => $r->id,
            'service_name'   => $r->service_name,
            'service_type'   => $r->service?->is_package ? 'package' : 'normal_service',
            'payment_status' => $r->payment_status,
            'service_date'   => $r->service_date?->toDateString(),
            'total_price'    => $r->total_price,
            'amount_paid'    => $r->amount_paid,
            'remaining'      => $r->remaining_amount,
            'payment_history' => $history,
        ];

        // Package-specific fields
        if ($r->service?->is_package) {
            $entry['package_end_date']   = $r->package_end_date?->toDateString();
            $entry['payment_progress']   = $r->payment_progress;
        }

        return $entry;
    }
}
