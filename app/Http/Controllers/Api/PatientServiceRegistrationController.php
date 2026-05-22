<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePatientServiceRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ServiceRegistrations\Models\PatientServiceRegistration;
use Modules\ServiceRegistrations\Resources\PatientServiceRegistrationResource;
use Modules\Services\Models\ClinicService;
use Modules\Users\Models\User;

class PatientServiceRegistrationController extends Controller
{
    /**
     * GET /assistant/service-registrations
     * List all registrations, optionally filtered by patient name search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PatientServiceRegistration::with('registeredBy')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('patient_name', 'like', '%' . $request->search . '%');
        }

        $registrations = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => PatientServiceRegistrationResource::collection($registrations),
            'meta'    => [
                'current_page' => $registrations->currentPage(),
                'last_page'    => $registrations->lastPage(),
                'per_page'     => $registrations->perPage(),
                'total'        => $registrations->total(),
            ],
        ]);
    }

    /**
     * GET /assistant/service-registrations/{registration}
     * Single registration with full payment summary.
     */
    public function show(PatientServiceRegistration $serviceRegistration): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new PatientServiceRegistrationResource($serviceRegistration->load('registeredBy')),
        ]);
    }

    /**
     * POST /assistant/service-registrations
     * Record a new patient service registration and payment.
     */
    public function store(StorePatientServiceRegistrationRequest $request): JsonResponse
    {
        $patient = User::findOrFail($request->patient_id);
        $service = ClinicService::findOrFail($request->service_id);

        $registration = PatientServiceRegistration::create([
            'patient_id'       => $patient->id,
            'patient_name'     => $patient->name,
            'patient_phone'    => $patient->phone,
            'service_id'       => $service->id,
            'service_name'     => $service->name,
            'total_price'      => $service->price,
            'service_date'     => $request->service_date,
            'package_end_date' => $request->package_end_date,
            'amount_paid'      => $request->amount_paid,
            'registered_by'    => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Service registration saved successfully.'),
            'data'    => new PatientServiceRegistrationResource($registration->load('registeredBy')),
        ], 201);
    }

    /**
     * DELETE /assistant/service-registrations/{registration}
     * Delete a service registration record.
     */
    public function destroy(PatientServiceRegistration $serviceRegistration): JsonResponse
    {
        $serviceRegistration->delete();

        return response()->json([
            'success' => true,
            'message' => __('Registration deleted.'),
        ]);
    }
}
