<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreClinicServiceRequest;
use App\Http\Requests\Api\UpdateClinicServiceRequest;
use Illuminate\Http\JsonResponse;
use Modules\Services\Models\ClinicService;
use Modules\Services\Models\ClinicServiceCategory;
use Modules\Services\Resources\ClinicServiceResource;

class ClinicServiceController extends Controller
{
    /**
     * GET /doctor/services
     * List all clinic services with optional search and category filter.
     */
    public function index(): JsonResponse
    {
        $query = ClinicService::with('category');

        if (request()->filled('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        if (request()->filled('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        $services = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'total'   => $services->count(),
            'data'    => ClinicServiceResource::collection($services),
        ]);
    }

    /**
     * GET /doctor/services/categories
     * List all service categories (for dropdown).
     */
    public function categories(): JsonResponse
    {
        $categories = ClinicServiceCategory::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    /**
     * POST /doctor/services
     * Create a new clinic service.
     */
    public function store(StoreClinicServiceRequest $request): JsonResponse
    {
        $service = ClinicService::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'is_package'  => $request->boolean('is_package'),
            'is_active'   => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Service created successfully.'),
            'data'    => new ClinicServiceResource($service->load('category')),
        ], 201);
    }

    /**
     * POST /doctor/services/{service}
     * Update an existing clinic service.
     */
    public function update(UpdateClinicServiceRequest $request, ClinicService $service): JsonResponse
    {
        $service->update(array_filter([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'is_package'  => $request->has('is_package') ? $request->boolean('is_package') : null,
            'is_active'   => $request->has('is_active') ? $request->boolean('is_active') : null,
        ], fn($v) => $v !== null));

        return response()->json([
            'success' => true,
            'message' => __('Service updated successfully.'),
            'data'    => new ClinicServiceResource($service->fresh()->load('category')),
        ]);
    }

    /**
     * DELETE /doctor/services/{service}
     * Delete a clinic service.
     */
    public function destroy(ClinicService $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => __('Service deleted.'),
        ]);
    }
}
