<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Users\Models\User;

class AssistantPatientSearchController extends Controller
{
    /**
     * GET /assistant/patients/search?q=...
     * Search patients (role_id=2) by name, phone, or numeric ID.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:1']]);

        $q = trim($request->q);

        $patients = User::where('role_id', 2)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere(function ($sub) use ($q) {
                          if (is_numeric($q)) {
                              $sub->where('id', (int) $q);
                          }
                      });
            })
            ->limit(15)
            ->get(['id', 'name', 'phone']);

        $data = $patients->map(fn($p) => [
            'id'           => $p->id,
            'patient_code' => '#P-' . str_pad($p->id, 3, '0', STR_PAD_LEFT),
            'name'         => $p->name,
            'phone'        => $p->phone,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
