<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\LabResults\Models\PatientLabResult;
use Modules\Prescriptions\Models\PatientPrescription;
use Modules\Sessions\Models\PatientSession;
use Modules\UltrasoundFindings\Models\PatientUltrasoundFinding;
use Modules\Users\Models\User;

class PatientExportController extends Controller
{
    /**
     * GET /v1/doctor/patients/export-pdf
     * Export all patients as a summary PDF.
     * Optional query: mode=pregnancy|period, risk_status=stable|high_risk|monitor, search=name
     */
    public function all(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $query = User::with(['patientTracking', 'lastCompletedAppointment'])
            ->where('role_id', 2)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = ltrim($request->input('search'), '#P-');
            $query->where(function ($q) use ($request, $search) {
                $q->where('name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('id', is_numeric($search) ? (int) $search : -1);
            });
        }

        if ($request->filled('risk_status')) {
            $query->where('risk_status', $request->input('risk_status'));
        }

        if ($request->filled('mode') && $request->input('mode') !== 'all') {
            $type = $request->input('mode') === 'pregnancy' ? 'pregnancy' : 'menstrual';
            $query->whereHas('patientTracking', fn($q) => $q->where('tracking_type', $type));
        }

        $patients = $query->get();

        $stats = [
            'total'      => $patients->count(),
            'pregnancy'  => $patients->filter(fn($p) => $p->patientTracking?->tracking_type === 'pregnancy')->count(),
            'period'     => $patients->filter(fn($p) => $p->patientTracking?->tracking_type === 'menstrual')->count(),
            'high_risk'  => $patients->where('risk_status', 'high_risk')->count(),
            'monitor'    => $patients->where('risk_status', 'monitor')->count(),
            'stable'     => $patients->where('risk_status', 'stable')->count(),
        ];

        $pdf = Pdf::loadView('reports.patients_all_export', [
            'patients'    => $patients,
            'stats'       => $stats,
            'filters'     => $request->only(['mode', 'risk_status', 'search']),
            'doctor'      => config('app.doctor_name'),
            'clinic'      => config('app.clinic_name'),
            'generatedAt' => now()->format('M d, Y h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('patients-export-' . now()->format('Ymd') . '.pdf');
    }

    /**
     * GET /v1/doctor/patients/{patient}/export-pdf
     * Export a single patient's full medical record as PDF.
     */
    public function single(User $patient): \Symfony\Component\HttpFoundation\Response
    {
        abort_if($patient->role_id !== 2, 404);

        $sessions = PatientSession::where('patient_id', $patient->getAttribute('id'))
            ->orderByDesc('session_date')
            ->take(10)
            ->get();

        $prescriptions = PatientPrescription::where('patient_id', $patient->getAttribute('id'))
            ->orderByDesc('start_date')
            ->get();

        $labResults = PatientLabResult::where('patient_id', $patient->getAttribute('id'))
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $ultrasounds = PatientUltrasoundFinding::where('patient_id', $patient->getAttribute('id'))
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $pdf = Pdf::loadView('reports.patient_export', [
            'patient'       => $patient,
            'sessions'      => $sessions,
            'prescriptions' => $prescriptions,
            'labResults'    => $labResults,
            'ultrasounds'   => $ultrasounds,
            'doctor'        => config('app.doctor_name'),
            'clinic'        => config('app.clinic_name'),
            'generatedAt'   => now()->format('M d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        $code = str_pad($patient->getAttribute('id'), 3, '0', STR_PAD_LEFT);

        return $pdf->download('patient-P' . $code . '-' . now()->format('Ymd') . '.pdf');
    }
}
