<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Modules\Users\Models\User;

class StaffController extends Controller
{
    private const ROLE_MAP = [
        'doctor'    => 1,
        'assistant' => 3,
    ];

    /**
     * GET /doctor/staff
     * List all doctors and assistants.
     */
    public function index(): JsonResponse
    {
        $staff = User::whereIn('role_id', [1, 3])
            ->orderBy('role_id')
            ->orderBy('name')
            ->get()
            ->map(fn(User $u) => $this->format($u));

        return response()->json(['success' => true, 'data' => $staff]);
    }

    /**
     * POST /doctor/staff
     * Create a doctor or assistant account.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', Password::min(8)],
            'type'     => ['required', 'in:doctor,assistant'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => bcrypt($data['password']),
            'role_id'  => self::ROLE_MAP[$data['type']],
            'active'   => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($data['type']) . ' account created successfully.',
            'data'    => $this->format($user),
        ], 201);
    }

    /**
     * PATCH /doctor/staff/{user}
     * Update a staff account's details.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        abort_if(!in_array($user->role_id, [1, 3]), 404);

        $data = $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'max:255'],
            'email'    => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'    => ['sometimes', 'required', 'string', 'max:20',  'unique:users,phone,' . $user->id],
            'password' => ['nullable', Password::min(8)],
            'type'     => ['sometimes', 'required', 'in:doctor,assistant'],
        ]);

        $updates = array_filter([
            'name'    => $data['name']  ?? null,
            'email'   => $data['email'] ?? null,
            'phone'   => $data['phone'] ?? null,
            'role_id' => isset($data['type']) ? self::ROLE_MAP[$data['type']] : null,
            'password'=> !empty($data['password']) ? bcrypt($data['password']) : null,
        ], fn($v) => $v !== null);

        $user->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully.',
            'data'    => $this->format($user->fresh()),
        ]);
    }

    /**
     * DELETE /doctor/staff/{user}
     * Remove a staff account (cannot delete self).
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_if(!in_array($user->role_id, [1, 3]), 404);
        abort_if($user->id === $request->user()->id, 403, 'Cannot delete your own account.');

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Account removed.']);
    }

    private function format(User $u): array
    {
        return [
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'type'  => $u->role_id === 1 ? 'doctor' : 'assistant',
            'active' => (bool) $u->active,
        ];
    }
}
