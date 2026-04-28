<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAdvertisementRequest;
use App\Http\Requests\Api\UpdateAdvertisementRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Resources\AdvertisementResource;

class AdvertisementController extends Controller
{
    /**
     * List all active advertisements for the patient app.
     */
    public function index(): JsonResponse
    {
        $ads = Advertisement::byNewest()->get();

        return response()->json([
            'success' => true,
            'data'    => AdvertisementResource::collection($ads),
        ]);
    }

    /**
     * Show a single advertisement.
     */
    public function show(Advertisement $advertisement): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new AdvertisementResource($advertisement),
        ]);
    }

    /**
     * Create a new advertisement (doctor/admin only).
     */
    public function store(StoreAdvertisementRequest $request): JsonResponse
    {
        $ad = Advertisement::create([
            ...$request->safe()->except('banner_image'),
            'created_by' => Auth::id(),
        ]);

        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $ad
                ->addMedia($file)
                ->usingName(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection('banner');
        }

        return response()->json([
            'success' => true,
            'message' => __('Advertisement created successfully.'),
            'data'    => new AdvertisementResource($ad),
        ], 201);
    }

    /**
     * Update an advertisement (doctor/admin only).
     */
    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): JsonResponse
    {
        $advertisement->update($request->safe()->except('banner_image'));

        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $advertisement
                ->addMedia($file)
                ->usingName(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection('banner');
        }

        return response()->json([
            'success' => true,
            'message' => __('Advertisement updated successfully.'),
            'data'    => new AdvertisementResource($advertisement),
        ]);
    }

    /**
     * Delete an advertisement (doctor/admin only).
     */
    public function destroy(Advertisement $advertisement): JsonResponse
    {
        $advertisement->delete();

        return response()->json([
            'success' => true,
            'message' => __('Advertisement deleted successfully.'),
        ]);
    }
}
